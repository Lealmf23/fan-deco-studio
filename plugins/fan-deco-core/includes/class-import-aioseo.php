<?php
/**
 * Importador de datos de AIOSEO (títulos, descripciones, canonical, robots, imagen social).
 *
 * IMPORTANTE: el esquema de tablas de AIOSEO no está documentado oficialmente y no pudo probarse contra
 * una instalación real. El importador comprueba qué tablas/columnas existen, funciona en modo simulación
 * por defecto y NO sobrescribe campos ya completados en Fan Deco (salvo que se pida expresamente).
 *
 * @package FanDecoCore
 */

defined( 'ABSPATH' ) || exit;

final class FanDeco_Import_AIOSEO {

	public static function init(): void {
		add_action( 'admin_post_fdc_import_aioseo', array( __CLASS__, 'handle' ) );
	}

	private static function table_columns( string $table ): array {
		global $wpdb;
		$found = $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $wpdb->esc_like( $table ) ) ); // phpcs:ignore WordPress.DB
		if ( $found !== $table ) {
			return array();
		}
		return array_map( 'strval', (array) $wpdb->get_col( "DESC `{$table}`", 0 ) ); // phpcs:ignore WordPress.DB
	}

	/** Convierte un valor de AIOSEO; devuelve '' si equivale a la plantilla por defecto. */
	private static function convert( string $value, array &$unknown ): string {
		if ( '' === trim( $value ) ) {
			return '';
		}
		[ $out, $unk ] = FanDeco_Text::from_aioseo( $value );
		$unknown       = array_merge( $unknown, $unk );
		return $out;
	}

	/**
	 * @return array{posts:int,terms:int,skipped:int,samples:array<int,array<string,string>>,unknown:string[],notes:string[]}
	 */
	public static function run( bool $dry, bool $overwrite ): array {
		global $wpdb;
		$res     = array( 'posts' => 0, 'terms' => 0, 'skipped' => 0, 'samples' => array(), 'unknown' => array(), 'notes' => array() );
		$unknown = array();
		$default = FanDeco_Settings::get( 'title_post' );
		if ( function_exists( 'set_time_limit' ) ) {
			@set_time_limit( 120 ); // phpcs:ignore WordPress.PHP.NoSilencedErrors
		}

		$write = static function ( string $kind, int $id, array $data ) use ( $dry, $overwrite, &$res, $default ): void {
			$changed = array();
			foreach ( $data as $field => $value ) {
				if ( '' === $value || null === $value ) {
					continue;
				}
				if ( 'title' === $field && $value === $default ) {
					continue; // equivale a la plantilla global: no hace falta guardarlo.
				}
				$key = FanDeco_Meta::key( $field );
				$cur = 'term' === $kind ? get_term_meta( $id, $key, true ) : get_post_meta( $id, $key, true );
				if ( '' !== (string) $cur && ! $overwrite ) {
					++$res['skipped'];
					continue;
				}
				$changed[ $field ] = $value;
				if ( ! $dry ) {
					'term' === $kind ? update_term_meta( $id, $key, $value ) : update_post_meta( $id, $key, $value );
				}
			}
			if ( $changed ) {
				++$res['term' === $kind ? 'terms' : 'posts'];
				if ( count( $res['samples'] ) < 10 ) {
					$res['samples'][] = array_merge( array( 'tipo' => $kind, 'id' => (string) $id ), $changed );
				}
			}
		};

		// 1) AIOSEO 4: tabla aioseo_posts.
		$posts_table = $wpdb->prefix . 'aioseo_posts';
		$cols        = self::table_columns( $posts_table );
		if ( $cols && in_array( 'post_id', $cols, true ) ) {
			$want   = array_values( array_intersect( array( 'post_id', 'title', 'description', 'canonical_url', 'robots_default', 'robots_noindex', 'robots_nofollow', 'og_image_custom_url' ), $cols ) );
			$sql    = 'SELECT `' . implode( '`,`', $want ) . "` FROM `{$posts_table}`";
			foreach ( (array) $wpdb->get_results( $sql, ARRAY_A ) as $row ) { // phpcs:ignore WordPress.DB
				$use_global = isset( $row['robots_default'] ) ? (int) $row['robots_default'] === 1 : true;
				$write(
					'post',
					(int) $row['post_id'],
					array(
						'title'       => self::convert( (string) ( $row['title'] ?? '' ), $unknown ),
						'description' => self::convert( (string) ( $row['description'] ?? '' ), $unknown ),
						'canonical'   => esc_url_raw( (string) ( $row['canonical_url'] ?? '' ) ),
						'og_image'    => esc_url_raw( (string) ( $row['og_image_custom_url'] ?? '' ) ),
						'noindex'     => ( ! $use_global && ! empty( $row['robots_noindex'] ) ) ? '1' : '',
						'nofollow'    => ( ! $use_global && ! empty( $row['robots_nofollow'] ) ) ? '1' : '',
					)
				);
			}
		} else {
			$res['notes'][] = 'No se encontró la tabla aioseo_posts (AIOSEO 4). Se intenta con metadatos antiguos.';
		}

		// 2) Metadatos antiguos (AIOSEO 3 / "All in One SEO Pack").
		foreach ( array( '_aioseop_title' => 'title', '_aioseop_description' => 'description' ) as $meta_key => $field ) {
			$rows = $wpdb->get_results( $wpdb->prepare( "SELECT post_id, meta_value FROM {$wpdb->postmeta} WHERE meta_key = %s AND meta_value <> ''", $meta_key ), ARRAY_A ); // phpcs:ignore WordPress.DB
			foreach ( (array) $rows as $row ) {
				$write( 'post', (int) $row['post_id'], array( $field => self::convert( (string) $row['meta_value'], $unknown ) ) );
			}
		}

		// 3) Términos (categorías): tabla aioseo_terms.
		$terms_table = $wpdb->prefix . 'aioseo_terms';
		$tcols       = self::table_columns( $terms_table );
		if ( $tcols && in_array( 'term_id', $tcols, true ) ) {
			$want = array_values( array_intersect( array( 'term_id', 'title', 'description', 'robots_default', 'robots_noindex' ), $tcols ) );
			foreach ( (array) $wpdb->get_results( 'SELECT `' . implode( '`,`', $want ) . "` FROM `{$terms_table}`", ARRAY_A ) as $row ) { // phpcs:ignore WordPress.DB
				$use_global = isset( $row['robots_default'] ) ? (int) $row['robots_default'] === 1 : true;
				$write(
					'term',
					(int) $row['term_id'],
					array(
						'title'       => self::convert( (string) ( $row['title'] ?? '' ), $unknown ),
						'description' => self::convert( (string) ( $row['description'] ?? '' ), $unknown ),
						'noindex'     => ( ! $use_global && ! empty( $row['robots_noindex'] ) ) ? '1' : '',
					)
				);
			}
		} else {
			$res['notes'][] = 'No se encontró la tabla aioseo_terms.';
		}

		$res['unknown'] = array_values( array_unique( $unknown ) );
		return $res;
	}

	public static function handle(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Sin permisos.', 'fan-deco-core' ), 403 );
		}
		check_admin_referer( 'fdc_import_aioseo' );
		$dry       = ! isset( $_POST['fdc_apply'] );
		$overwrite = ! empty( $_POST['fdc_overwrite'] );
		set_transient( 'fdc_import_result_' . get_current_user_id(), array( 'dry' => $dry ) + self::run( $dry, $overwrite ), 300 );
		wp_safe_redirect( admin_url( 'options-general.php?page=fandeco-seo#fdc-import' ) );
		exit;
	}

	public static function render_section(): void {
		$key    = 'fdc_import_result_' . get_current_user_id();
		$result = get_transient( $key );
		delete_transient( $key );
		?>
		<h2 id="fdc-import">Importar desde AIOSEO</h2>
		<p class="description"><strong>Antes de desactivar AIOSEO:</strong> 1) hacé una copia de la base de datos; 2) ejecutá primero la <em>simulación</em>; 3) comparás títulos y descripciones antes/después (ver README: <code>npm run seo:snapshot</code>). Las plantillas globales de AIOSEO (formato de títulos por tipo de contenido) <u>no</u> se importan: copiálas manualmente arriba en “Títulos y descripciones”. Los campos ya completados aquí no se pisan salvo que lo indiques.</p>
		<?php if ( is_array( $result ) ) : ?>
			<div class="notice notice-info inline">
				<p><strong><?php echo ! empty( $result['dry'] ) ? 'Simulación (no se guardó nada)' : 'Importación aplicada'; ?>:</strong>
					<?php echo (int) $result['posts']; ?> contenidos, <?php echo (int) $result['terms']; ?> términos, <?php echo (int) $result['skipped']; ?> campos omitidos por existir ya.</p>
				<?php foreach ( $result['notes'] as $n ) { echo '<p>' . esc_html( $n ) . '</p>'; } ?>
				<?php if ( $result['unknown'] ) : ?><p>Etiquetas de AIOSEO sin equivalente (descartadas): <code><?php echo esc_html( implode( ' ', $result['unknown'] ) ); ?></code></p><?php endif; ?>
				<?php if ( $result['samples'] ) : ?><pre style="white-space:pre-wrap"><?php echo esc_html( wp_json_encode( $result['samples'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE ) ); ?></pre><?php endif; ?>
			</div>
		<?php endif; ?>
		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
			<input type="hidden" name="action" value="fdc_import_aioseo">
			<?php wp_nonce_field( 'fdc_import_aioseo' ); ?>
			<p><label><input type="checkbox" name="fdc_overwrite" value="1"> Sobrescribir campos que ya tengan valor en Fan Deco</label></p>
			<p>
				<button class="button" type="submit">Simular importación</button>
				<button class="button button-primary" type="submit" name="fdc_apply" value="1" onclick="return confirm('¿Aplicar la importación? Hacé una copia de la base de datos antes.');">Importar ahora</button>
			</p>
		</form>
		<?php
	}
}
