<?php
/**
 * Interruptor de luz para luminarias: alterna en la galería entre la foto principal (apagada) y una
 * "foto encendida" real cargada por producto (y opcionalmente por variación). No simula nada: sin foto
 * encendida no hay interruptor.
 *
 * @package FanDecoCore
 */

defined( 'ABSPATH' ) || exit;

final class FanDeco_Lights {
	public const META = '_fandeco_lit_image';

	public static function init(): void {
		add_action( 'woocommerce_product_options_general_product_data', array( __CLASS__, 'product_field' ) );
		add_action( 'woocommerce_process_product_meta', array( __CLASS__, 'save_product' ) );
		add_action( 'woocommerce_product_after_variable_attributes', array( __CLASS__, 'variation_field' ), 10, 3 );
		add_action( 'woocommerce_save_product_variation', array( __CLASS__, 'save_variation' ), 10, 2 );
		add_filter( 'woocommerce_available_variation', array( __CLASS__, 'variation_data' ), 10, 3 );
		add_filter( 'woocommerce_single_product_image_thumbnail_html', array( __CLASS__, 'main_image' ), 20, 2 );
		add_action( 'woocommerce_product_thumbnails', array( __CLASS__, 'switch_button' ), 25 );
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'assets' ) );
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'admin_assets' ) );
	}

	/* ---------------- Datos ---------------- */

	private static function valid_image( int $id ): int {
		return ( $id > 0 && wp_attachment_is_image( $id ) ) ? $id : 0;
	}

	public static function product_lit_id( $product ): int {
		return self::valid_image( (int) get_post_meta( $product->get_id(), self::META, true ) );
	}

	/** ¿Alguna variación tiene foto encendida? */
	private static function any_variation_lit( $product ): bool {
		if ( ! $product instanceof WC_Product_Variable ) {
			return false;
		}
		foreach ( (array) $product->get_children() as $child ) {
			if ( self::valid_image( (int) get_post_meta( (int) $child, self::META, true ) ) ) {
				return true;
			}
		}
		return false;
	}

	public static function has_lights( $product ): bool {
		return $product instanceof WC_Product && ( self::product_lit_id( $product ) || self::any_variation_lit( $product ) );
	}

	/** @return array{src:string,srcset:string}|null */
	private static function image_data( int $id ): ?array {
		$id = self::valid_image( $id );
		if ( ! $id ) {
			return null;
		}
		$src = wp_get_attachment_image_url( $id, 'woocommerce_single' );
		if ( ! $src ) {
			return null;
		}
		return array( 'src' => (string) $src, 'srcset' => (string) wp_get_attachment_image_srcset( $id, 'woocommerce_single' ) );
	}

	/* ---------------- Administración ---------------- */

	public static function admin_assets( string $hook ): void {
		if ( ! in_array( $hook, array( 'post.php', 'post-new.php' ), true ) || 'product' !== get_post_type() ) {
			return;
		}
		wp_enqueue_media();
		wp_enqueue_script( 'fandeco-lights-admin', FANDECO_CORE_URL . 'assets/lights-admin.js', array( 'jquery' ), FANDECO_CORE_VERSION, true );
	}

	private static function picker( string $name, int $id ): void {
		$url = $id ? wp_get_attachment_image_url( $id, 'thumbnail' ) : '';
		?>
		<span class="fdl-picker" data-fdl-picker>
			<input type="hidden" name="<?php echo esc_attr( $name ); ?>" value="<?php echo esc_attr( (string) $id ); ?>" data-fdl-input>
			<img src="<?php echo esc_url( (string) $url ); ?>" alt="" style="max-width:80px;height:auto;vertical-align:middle;<?php echo $url ? '' : 'display:none;'; ?>" data-fdl-preview>
			<button type="button" class="button" data-fdl-select><?php esc_html_e( 'Elegir foto', 'fan-deco-core' ); ?></button>
			<button type="button" class="button-link" data-fdl-clear <?php echo $id ? '' : 'style="display:none"'; ?>><?php esc_html_e( 'Quitar', 'fan-deco-core' ); ?></button>
		</span>
		<?php
	}

	public static function product_field(): void {
		global $post;
		$id = $post ? self::valid_image( (int) get_post_meta( $post->ID, self::META, true ) ) : 0;
		echo '<div class="options_group"><p class="form-field"><label>' . esc_html__( 'Foto encendida', 'fan-deco-core' ) . '</label>';
		self::picker( 'fandeco_lit_image', $id );
		echo '<span class="description" style="display:block;clear:both;margin-left:0">' . esc_html__( 'Solo para luminarias: la foto principal es la lámpara apagada y esta, la misma toma encendida. Si la cargás, la ficha muestra un interruptor de luz. Mismo encuadre y tamaño que la foto principal.', 'fan-deco-core' ) . '</span></p></div>';
		wp_nonce_field( 'fandeco_lit', 'fandeco_lit_nonce' );
	}

	public static function save_product( int $post_id ): void {
		if ( ! isset( $_POST['fandeco_lit_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['fandeco_lit_nonce'] ) ), 'fandeco_lit' ) || ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}
		$id = self::valid_image( absint( wp_unslash( $_POST['fandeco_lit_image'] ?? 0 ) ) );
		$id ? update_post_meta( $post_id, self::META, $id ) : delete_post_meta( $post_id, self::META );
	}

	public static function variation_field( int $loop, array $variation_data, $variation ): void {
		$id = self::valid_image( (int) get_post_meta( $variation->ID, self::META, true ) );
		echo '<p class="form-row form-row-full"><label>' . esc_html__( 'Foto encendida de esta variación (opcional)', 'fan-deco-core' ) . '</label>';
		self::picker( 'fandeco_lit_image_var[' . (int) $loop . ']', $id );
		echo '</p>';
	}

	public static function save_variation( int $variation_id, int $i ): void {
		// WooCommerce ya valida el nonce y los permisos del guardado AJAX de variaciones.
		if ( ! current_user_can( 'edit_post', $variation_id ) ) {
			return;
		}
		$raw = isset( $_POST['fandeco_lit_image_var'] ) && is_array( $_POST['fandeco_lit_image_var'] ) ? wp_unslash( $_POST['fandeco_lit_image_var'] ) : array(); // phpcs:ignore WordPress.Security
		$id  = self::valid_image( absint( $raw[ $i ] ?? 0 ) );
		$id ? update_post_meta( $variation_id, self::META, $id ) : delete_post_meta( $variation_id, self::META );
	}

	/* ---------------- Frontend ---------------- */

	/** Datos que WooCommerce envía al navegador al elegir una variación. */
	public static function variation_data( array $data, $product, $variation ): array {
		$data['fandeco_lit'] = self::image_data( (int) get_post_meta( $variation->get_id(), self::META, true ) );
		return $data;
	}

	/** Agrega la foto encendida (superpuesta, oculta) dentro del contenedor de la imagen principal. */
	public static function main_image( $html, $attachment_id ) {
		global $product;
		static $done = array();
		if ( ! $product instanceof WC_Product || (int) $attachment_id !== (int) $product->get_image_id() || isset( $done[ $product->get_id() ] ) || ! self::has_lights( $product ) ) {
			return $html;
		}
		$done[ $product->get_id() ] = true;
		$data = self::image_data( self::product_lit_id( $product ) );
		if ( $data ) {
			$img = sprintf(
				'<img class="fd-lit-img" alt="" aria-hidden="true" decoding="async" fetchpriority="low" data-fd-lit src="%s"%s>',
				esc_url( $data['src'] ),
				$data['srcset'] ? ' srcset="' . esc_attr( $data['srcset'] ) . '" sizes="(max-width: 767px) 100vw, 50vw"' : ''
			);
		} else { // solo hay fotos por variación: el elemento se completa al elegir una
			$img = '<img class="fd-lit-img" alt="" aria-hidden="true" decoding="async" data-fd-lit hidden src="data:image/gif;base64,R0lGODlhAQABAAAAACH5BAEKAAEALAAAAAABAAEAAAICTAEAOw==">';
		}
		$pos = strrpos( (string) $html, '</div>' );
		return false === $pos ? $html . $img : substr_replace( (string) $html, $img, $pos, 0 );
	}

	/** Interruptor accesible (role="switch"). Se ubica sobre la galería. */
	public static function switch_button(): void {
		global $product;
		if ( ! $product instanceof WC_Product || ! self::has_lights( $product ) ) {
			return;
		}
		$has_default = (bool) self::product_lit_id( $product );
		printf(
			'<button type="button" class="fd-light-switch" role="switch" aria-checked="false" data-fd-light-switch data-main-id="%1$d" data-off="%2$s" data-on="%3$s"%4$s><span class="fd-light-switch__track" aria-hidden="true"><span class="fd-light-switch__thumb"></span></span><span class="fd-light-switch__label" data-fd-light-label>%2$s</span></button>',
			(int) $product->get_image_id(),
			esc_attr__( 'Luz apagada', 'fan-deco-core' ),
			esc_attr__( 'Luz encendida', 'fan-deco-core' ),
			$has_default ? '' : ' hidden'
		);
	}

	public static function assets(): void {
		global $product;
		if ( ! function_exists( 'is_product' ) || ! is_product() ) {
			return;
		}
		$p = $product instanceof WC_Product ? $product : wc_get_product( get_the_ID() );
		if ( ! self::has_lights( $p ) ) {
			return;
		}
		wp_enqueue_style( 'fandeco-lights', FANDECO_CORE_URL . 'assets/lights.css', array(), FANDECO_CORE_VERSION );
		wp_enqueue_script( 'fandeco-lights', FANDECO_CORE_URL . 'assets/lights.js', array( 'jquery' ), FANDECO_CORE_VERSION, true );
	}
}
