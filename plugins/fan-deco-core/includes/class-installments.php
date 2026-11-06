<?php
/**
 * "Precio y cuotas": muestra en la ficha de producto (y opcionalmente en listados) el valor de la cuota sin
 * interés y el precio con transferencia. ES SOLO VISUAL: no cambia precios ni cobra nada. Las cuotas reales las
 * define Mercado Pago; el descuento por transferencia lo define el módulo "Descuento por transferencia".
 *
 * Se actualiza solo al cambiar de variación. Shortcode para plantillas (p. ej. Elementor): [fandeco_cuotas]
 *
 * @package FanDecoCore
 */

defined( 'ABSPATH' ) || exit;

final class FanDeco_Installments {
	public const OPTION = 'fandeco_core_installments';

	public static function init(): void {
		add_action( 'admin_menu', array( __CLASS__, 'menu' ) );
		add_action( 'admin_init', array( __CLASS__, 'register' ) );
		add_action( 'woocommerce_single_product_summary', array( __CLASS__, 'render_single' ), 11 );
		if ( 'blocksy' === get_template() ) {
			add_filter( 'blocksy:woocommerce:product-card:price', array( __CLASS__, 'blocksy_price' ) ); // tarjetas de Blocksy (no usan el gancho de WooCommerce; el filtro vale para sus tipos 1 y 2)
		} else {
			add_action( 'woocommerce_after_shop_loop_item_title', array( __CLASS__, 'render_loop' ), 11 );
		}
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'assets' ) );
		add_shortcode( 'fandeco_cuotas', array( __CLASS__, 'shortcode' ) );
	}

	public static function defaults(): array {
		return array(
			'enabled'       => 0,
			'installments'  => 3,
			'show_single'   => 1,
			'show_loop'     => 0,
			'show_transfer' => 1,
			'min_total'     => 0.0,
		);
	}

	public static function options(): array {
		static $cache = null;
		if ( null === $cache ) {
			$saved = get_option( self::OPTION, array() );
			$cache = array_merge( self::defaults(), is_array( $saved ) ? $saved : array() );
		}
		return $cache;
	}

	/** Formato de moneda de WooCommerce (símbolo, posición, separadores, decimales). */
	public static function money_format(): array {
		return array(
			'sym'    => html_entity_decode( get_woocommerce_currency_symbol(), ENT_QUOTES, 'UTF-8' ),
			'format' => html_entity_decode( get_woocommerce_price_format(), ENT_QUOTES, 'UTF-8' ),
			'dec'    => (int) wc_get_price_decimals(),
			'ds'     => (string) wc_get_price_decimal_separator(),
			'ts'     => (string) wc_get_price_thousand_separator(),
		);
	}

	/**
	 * Elegibilidad al precio con transferencia: 'all' | 'none' | lista JSON de IDs de variación elegibles.
	 *
	 * @param WC_Product $product Producto.
	 */
	private static function eligibility( $product ): string {
		if ( ! class_exists( 'FanDeco_Transfer_Discount' ) || ! FanDeco_Transfer_Discount::is_active() || ! FanDeco_Transfer_Discount::display_consistent() ) {
			return 'none';
		}
		if ( FanDeco_Transfer_Discount::product_eligible( $product ) ) {
			return 'all';
		}
		if ( $product instanceof WC_Product_Variable ) { // alcance por productos con variaciones puntuales
			$ids = array();
			foreach ( (array) $product->get_children() as $child_id ) {
				$child = wc_get_product( $child_id );
				if ( $child && FanDeco_Transfer_Discount::product_eligible( $child ) ) {
					$ids[] = (int) $child_id;
				}
			}
			return $ids ? (string) wp_json_encode( $ids ) : 'none';
		}
		return 'none';
	}

	/**
	 * @param WC_Product $product Producto.
	 * @param string     $context 'single' | 'loop'.
	 * @param string     $parts   'all' (cuotas + transferencia) o 'transfer' (solo el precio con transferencia, sin cuotas).
	 */
	public static function html( $product, string $context = 'single', string $parts = 'all' ): string {
		$o            = self::options();
		$only_transfer = 'transfer' === $parts;
		if ( $only_transfer ) {
			$o['enabled']       = 1; // el precio con transferencia no depende de que estén activas las cuotas
			$o['show_transfer'] = 1;
		}
		if ( ! $o['enabled'] || ! $product instanceof WC_Product || ! $product->is_purchasable() ) {
			return '';
		}
		$variable = $product instanceof WC_Product_Variable;
		$price    = $variable ? (float) $product->get_variation_price( 'min', true ) : (float) wc_get_price_to_display( $product );
		if ( $price <= 0 ) {
			return '';
		}
		$fmt      = self::money_format();
		$n        = max( 1, min( 12, (int) $o['installments'] ) );
		$percent  = ( $o['show_transfer'] && class_exists( 'FanDeco_Transfer_Discount' ) ) ? FanDeco_Transfer_Discount::percent() : 0.0;
		$elig     = $percent > 0 ? self::eligibility( $product ) : 'none';
		$show_cuo = ! $only_transfer && $price >= (float) $o['min_total'];
		$show_tr  = 'all' === $elig; // con variaciones puntuales elegibles, se muestra recién al elegir una de ellas
		if ( ! $show_cuo && ! $show_tr && ! $variable ) {
			return '';
		}
		$from   = $variable && (float) $product->get_variation_price( 'min', true ) !== (float) $product->get_variation_price( 'max', true );
		$prefix = $from ? esc_html__( 'Desde ', 'fan-deco-core' ) : '';
		$pct    = rtrim( rtrim( number_format( $percent, 2, '.', '' ), '0' ), '.' );

		$out  = sprintf(
			'<div class="fd-installments fd-installments--%1$s" data-fd-installments data-n="%2$d" data-pct="%3$s" data-eligible="%4$s" data-min="%5$s" data-from="%6$d" data-fmt="%7$s">',
			esc_attr( $context ),
			$n,
			esc_attr( (string) $percent ),
			esc_attr( $elig ),
			esc_attr( (string) (float) $o['min_total'] ),
			$from ? 1 : 0,
			esc_attr( (string) wp_json_encode( $fmt ) )
		);
		$out .= sprintf(
			'<p class="fd-installments__line" data-fd-inst-line%s>%s%d %s <strong data-fd-inst-amount>%s</strong></p>',
			$show_cuo ? '' : ' hidden',
			$prefix ? '<span data-fd-inst-prefix>' . $prefix . '</span>' : '',
			$n,
			esc_html__( 'cuotas sin interés de', 'fan-deco-core' ),
			esc_html( FanDeco_Installments_Calc::money( FanDeco_Installments_Calc::per_installment( $price, $n, $fmt['dec'] ), $fmt ) )
		);
		$out .= sprintf(
			'<p class="fd-installments__transfer" data-fd-transfer-line%s>%s<strong data-fd-transfer-amount>%s</strong> <span class="fd-pay-badge">%s%% OFF</span> <span class="fd-installments__how">%s</span></p>',
			$show_tr ? '' : ' hidden',
			$prefix ? '<span data-fd-inst-prefix>' . $prefix . '</span>' : '',
			esc_html( FanDeco_Installments_Calc::money( FanDeco_Installments_Calc::transfer_price( $price, $percent, $fmt['dec'] ), $fmt ) ),
			esc_html( $pct ),
			esc_html__( 'pagando con transferencia', 'fan-deco-core' )
		);
		return $out . '</div>';
	}

	public static function render_single(): void {
		global $product;
		if ( self::options()['show_single'] ) {
			echo self::html( $product, 'single' ); // phpcs:ignore WordPress.Security.EscapeOutput -- escapado dentro de html().
		}
	}

	/** Blocksy: agrega las cuotas / el precio con transferencia a continuación del precio de la tarjeta. */
	public static function blocksy_price( $html ) {
		ob_start();
		self::render_loop();
		return $html . ob_get_clean();
	}

	public static function render_loop(): void {
		global $product;
		if ( self::options()['show_loop'] ) {
			echo self::html( $product, 'loop' ); // phpcs:ignore WordPress.Security.EscapeOutput
		}
	}

	public static function shortcode(): string {
		global $product;
		return self::html( $product instanceof WC_Product ? $product : ( function_exists( 'wc_get_product' ) ? wc_get_product( get_the_ID() ) : null ), 'single' );
	}

	public static function assets(): void {
		$o = self::options();
		if ( ! $o['enabled'] || ! function_exists( 'is_product' ) || ! is_product() ) {
			return;
		}
		wp_enqueue_script( 'fandeco-installments', FANDECO_CORE_URL . 'assets/installments.js', array( 'jquery' ), FANDECO_CORE_VERSION, true );
	}

	/* ---- Administración ---- */

	public static function menu(): void {
		add_submenu_page( 'woocommerce', 'Precio y cuotas', 'Precio y cuotas', 'manage_woocommerce', 'fandeco-installments', array( __CLASS__, 'render' ) );
	}

	public static function register(): void {
		register_setting( 'fandeco_installments', self::OPTION, array( 'type' => 'array', 'sanitize_callback' => array( __CLASS__, 'sanitize' ), 'default' => self::defaults() ) );
	}

	public static function sanitize( $input ): array {
		$in = is_array( $input ) ? $input : array();
		return array(
			'enabled'       => empty( $in['enabled'] ) ? 0 : 1,
			'installments'  => max( 1, min( 12, absint( $in['installments'] ?? 3 ) ) ),
			'show_single'   => empty( $in['show_single'] ) ? 0 : 1,
			'show_loop'     => empty( $in['show_loop'] ) ? 0 : 1,
			'show_transfer' => empty( $in['show_transfer'] ) ? 0 : 1,
			'min_total'     => max( 0.0, round( (float) str_replace( ',', '.', (string) ( $in['min_total'] ?? 0 ) ), 2 ) ),
		);
	}

	public static function render(): void {
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			return;
		}
		$o = self::options();
		$n = self::OPTION;
		?>
		<div class="wrap">
			<h1>Precio y cuotas</h1>
			<p><strong>Solo informativo:</strong> muestra en la ficha del producto “N cuotas sin interés de $X” y el precio con transferencia. No cambia precios ni cobra nada; las cuotas reales las define Mercado Pago y el descuento por transferencia se configura en <em>WooCommerce → Descuento por transferencia</em> (misma fuente del porcentaje). Se actualiza al elegir una variación.</p>
			<div class="notice notice-warning inline"><p>Si el snippet <code>precio_con_cuotas</code> sigue activo, <strong>desactivalo al activar esto</strong> para no mostrar el cálculo dos veces.</p></div>
			<form method="post" action="options.php">
				<?php settings_fields( 'fandeco_installments' ); ?>
				<table class="form-table" role="presentation">
					<tr><th scope="row">Activar</th><td><label><input type="checkbox" name="<?php echo esc_attr( $n ); ?>[enabled]" value="1" <?php checked( $o['enabled'], 1 ); ?>> Mostrar precio con cuotas</label></td></tr>
					<tr><th scope="row"><label for="fdi_n">Cantidad de cuotas</label></th><td><input id="fdi_n" type="number" min="1" max="12" class="small-text" name="<?php echo esc_attr( $n ); ?>[installments]" value="<?php echo esc_attr( (string) $o['installments'] ); ?>"><p class="description">Debe coincidir con las cuotas sin interés configuradas en Mercado Pago.</p></td></tr>
					<tr><th scope="row"><label for="fdi_min">Importe mínimo</label></th><td><input id="fdi_min" type="number" min="0" step="0.01" class="small-text" name="<?php echo esc_attr( $n ); ?>[min_total]" value="<?php echo esc_attr( (string) $o['min_total'] ); ?>"><p class="description">No mostrar cuotas si el precio es menor (0 = siempre). Útil si Mercado Pago exige un mínimo.</p></td></tr>
					<tr><th scope="row">Dónde</th><td>
						<label style="display:block"><input type="checkbox" name="<?php echo esc_attr( $n ); ?>[show_single]" value="1" <?php checked( $o['show_single'], 1 ); ?>> Ficha de producto (debajo del precio)</label>
						<label style="display:block"><input type="checkbox" name="<?php echo esc_attr( $n ); ?>[show_loop]" value="1" <?php checked( $o['show_loop'], 1 ); ?>> Listados / tarjetas de producto</label>
						<p class="description">Para plantillas hechas con Elementor usá el shortcode <code>[fandeco_cuotas]</code>.</p></td></tr>
					<tr><th scope="row">Precio con transferencia</th><td><label><input type="checkbox" name="<?php echo esc_attr( $n ); ?>[show_transfer]" value="1" <?php checked( $o['show_transfer'], 1 ); ?>> Mostrarlo cuando el descuento por transferencia esté activo y el producto califique</label></td></tr>
				</table>
				<?php submit_button(); ?>
			</form>
		</div>
		<?php
	}
}

/** Para temas/plantillas: HTML de cuotas de un producto ('' si está desactivado). */
function fandeco_installments_html( $product, string $context = 'loop' ): string {
	return FanDeco_Installments::html( $product, $context );
}

/** Para tarjetas: solo el precio con transferencia (sin cuotas); '' si el descuento no está activo o el producto no califica. */
function fandeco_transfer_price_html( $product, string $context = 'loop' ): string {
	return FanDeco_Installments::html( $product, $context, 'transfer' );
}
