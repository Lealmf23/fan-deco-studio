<?php
/**
 * Metadatos SEO por contenido (panel en entradas/páginas/productos) y por término (categorías).
 *
 * @package FanDecoCore
 */

defined( 'ABSPATH' ) || exit;

final class FanDeco_Meta {
	public const FIELDS = array( 'title', 'description', 'canonical', 'og_image', 'noindex', 'nofollow' );

	public static function init(): void {
		add_action( 'add_meta_boxes', array( __CLASS__, 'add_box' ) );
		add_action( 'save_post', array( __CLASS__, 'save_post' ), 10, 2 );
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'assets' ) );
		add_action( 'init', array( __CLASS__, 'register_term_hooks' ), 20 );
	}

	public static function key( string $field ): string {
		return '_fdc_' . $field;
	}

	public static function assets( string $hook ): void {
		if ( ! in_array( $hook, array( 'post.php', 'post-new.php', 'term.php', 'edit-tags.php' ), true ) ) {
			return;
		}
		wp_enqueue_script( 'fandeco-core-admin', FANDECO_CORE_URL . 'assets/admin.js', array(), FANDECO_CORE_VERSION, true );
	}

	public static function add_box(): void {
		foreach ( get_post_types( array( 'public' => true ), 'names' ) as $type ) {
			if ( 'attachment' === $type ) {
				continue;
			}
			add_meta_box( 'fandeco-seo', 'Fan Deco SEO', array( __CLASS__, 'render_post_box' ), $type, 'normal', 'low' );
		}
	}

	private static function fields_html( callable $get, string $name_prefix ): void {
		$title = (string) $get( 'title' );
		$desc  = (string) $get( 'description' );
		?>
		<div class="fdc-seo" data-fdc-seo>
			<p><label><strong>Título SEO</strong><br>
				<input type="text" class="widefat" name="<?php echo esc_attr( $name_prefix ); ?>[title]" value="<?php echo esc_attr( $title ); ?>" data-fdc-title placeholder="Vacío = plantilla global">
			</label> <span class="description"><span data-fdc-title-count>0</span>/60 recomendado</span></p>
			<p><label><strong>Meta descripción</strong><br>
				<textarea class="widefat" rows="3" name="<?php echo esc_attr( $name_prefix ); ?>[description]" data-fdc-desc placeholder="Vacío = se genera desde el contenido"><?php echo esc_textarea( $desc ); ?></textarea>
			</label> <span class="description"><span data-fdc-desc-count>0</span>/160 recomendado</span></p>
			<p><label><strong>URL canónica</strong> (solo si difiere de la propia)<br>
				<input type="url" class="widefat" name="<?php echo esc_attr( $name_prefix ); ?>[canonical]" value="<?php echo esc_attr( (string) $get( 'canonical' ) ); ?>">
			</label></p>
			<p><label><strong>Imagen social (URL)</strong><br>
				<input type="url" class="widefat" name="<?php echo esc_attr( $name_prefix ); ?>[og_image]" value="<?php echo esc_attr( (string) $get( 'og_image' ) ); ?>">
			</label></p>
			<p>
				<label><input type="checkbox" name="<?php echo esc_attr( $name_prefix ); ?>[noindex]" value="1" <?php checked( (string) $get( 'noindex' ), '1' ); ?>> No indexar (noindex)</label>
				&nbsp; <label><input type="checkbox" name="<?php echo esc_attr( $name_prefix ); ?>[nofollow]" value="1" <?php checked( (string) $get( 'nofollow' ), '1' ); ?>> No seguir enlaces (nofollow)</label>
			</p>
		</div>
		<?php
	}

	public static function render_post_box( WP_Post $post ): void {
		wp_nonce_field( 'fdc_save_post_' . $post->ID, 'fdc_nonce' );
		self::fields_html( static fn( $f ) => get_post_meta( $post->ID, self::key( $f ), true ), 'fdc' );
	}

	/** Sanea un conjunto de campos recibidos. */
	public static function sanitize_fields( array $in ): array {
		return array(
			'title'       => sanitize_text_field( $in['title'] ?? '' ),
			'description' => sanitize_textarea_field( $in['description'] ?? '' ),
			'canonical'   => esc_url_raw( $in['canonical'] ?? '' ),
			'og_image'    => esc_url_raw( $in['og_image'] ?? '' ),
			'noindex'     => empty( $in['noindex'] ) ? '' : '1',
			'nofollow'    => empty( $in['nofollow'] ) ? '' : '1',
		);
	}

	public static function save_post( int $post_id, WP_Post $post ): void {
		if ( ! isset( $_POST['fdc_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['fdc_nonce'] ) ), 'fdc_save_post_' . $post_id ) ) {
			return;
		}
		if ( ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) || wp_is_post_revision( $post_id ) || ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}
		$clean = self::sanitize_fields( isset( $_POST['fdc'] ) && is_array( $_POST['fdc'] ) ? wp_unslash( $_POST['fdc'] ) : array() ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
		foreach ( $clean as $field => $value ) {
			if ( '' === $value ) {
				delete_post_meta( $post_id, self::key( $field ) );
			} else {
				update_post_meta( $post_id, self::key( $field ), $value );
			}
		}
	}

	public static function register_term_hooks(): void {
		$taxes = get_taxonomies( array( 'public' => true ), 'names' );
		foreach ( $taxes as $tax ) {
			add_action( "{$tax}_edit_form_fields", array( __CLASS__, 'render_term_fields' ), 10, 1 );
			add_action( "edited_{$tax}", array( __CLASS__, 'save_term' ), 10, 1 );
		}
	}

	public static function render_term_fields( WP_Term $term ): void {
		wp_nonce_field( 'fdc_save_term_' . $term->term_id, 'fdc_nonce' );
		echo '<tr class="form-field"><th scope="row">Fan Deco SEO</th><td>';
		self::fields_html( static fn( $f ) => get_term_meta( $term->term_id, self::key( $f ), true ), 'fdc' );
		echo '</td></tr>';
	}

	public static function save_term( int $term_id ): void {
		if ( ! isset( $_POST['fdc_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['fdc_nonce'] ) ), 'fdc_save_term_' . $term_id ) ) {
			return;
		}
		if ( ! current_user_can( 'manage_categories' ) ) {
			return;
		}
		$clean = self::sanitize_fields( isset( $_POST['fdc'] ) && is_array( $_POST['fdc'] ) ? wp_unslash( $_POST['fdc'] ) : array() ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
		foreach ( $clean as $field => $value ) {
			if ( '' === $value ) {
				delete_term_meta( $term_id, self::key( $field ) );
			} else {
				update_term_meta( $term_id, self::key( $field ), $value );
			}
		}
	}
}
