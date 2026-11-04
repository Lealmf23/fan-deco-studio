<?php
/**
 * Descuento por transferencia (checkout clásico de WooCommerce).
 *
 * - Se aplica como cargo NEGATIVO estándar de WooCommerce ("fee"), visible en carrito/checkout, pedido y emails.
 * - Solo cuando el método de pago elegido está en la lista (por defecto: transferencia bancaria "bacs").
 * - Base: total de los productos elegibles DESPUÉS de cupones, SIN envío ni otros cargos.
 * - Alcance configurable: todos los productos, categorías o productos puntuales.
 * - Desactivado por defecto. Se frena si detecta el plugin "Payment Gateway Based Fees and Discounts" activo.
 * - No aplica a Carrito/Checkout por BLOQUES (el sitio usa el clásico; ver README).
 *
 * @package FanDecoCore
 */

defined( 'ABSPATH' ) || exit;

final class FanDeco_Transfer_Discount {
	public const OPTION = 'fandeco_core_transfer';

	public static function init(): void {
		add_action( 'admin_menu', array( __CLASS__, 'menu' ) );
		add_action( 'admin_init', array( __CLASS__, 'register' ) );
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'admin_assets' ), 20 );
		add_action( 'woocommerce_cart_calculate_fees', array( __CLASS__, 'cart_fee' ), 20 );
		add_filter( 'woocommerce_gateway_icon', array( __CLASS__, 'gateway_badge' ), 20, 2 );
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'checkout_script' ), 30 );
		add_action( 'admin_notices', array( __CLASS__, 'conflict_notice' ) );
	}

	public static function defaults(): array {
		return array(
			'enabled'            => 0,
			'percent'            => 10.0,
			'label'              => 'Descuento por transferencia',
			'gateways'           => array( 'bacs' ),
			'scope'              => 'all',
			'categories'         => array(),
			'products'           => array(),
			'taxable'            => 1,
			'badge'              => 1,
			'confirm_no_overlap' => 0,
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

	/** Porcentaje vigente (0 si está desactivado). Útil para otros componentes (p. ej. mostrar el % en el producto). */
	public static function percent(): float {
		$o = self::options();
		return $o['enabled'] ? FanDeco_Transfer_Calc::clamp_percent( $o['percent'] ) : 0.0;
	}

	/** ¿Hay otro plugin activo que ya aplica descuentos por método de pago? (comprobación barata) */
	public static function conflicting_plugin_active(): bool {
		foreach ( (array) get_option( 'active_plugins', array() ) as $file ) {
			if ( false !== stripos( (string) $file, 'gateway-based-fees' ) ) {
				return true;
			}
		}
		return false;
	}

	private static function should_apply( array $o ): bool {
		if ( ! $o['enabled'] || FanDeco_Transfer_Calc::clamp_percent( $o['percent'] ) <= 0 ) {
			return false;
		}
		if ( self::conflicting_plugin_active() && ! $o['confirm_no_overlap'] ) {
			return false; // evita descontar dos veces
		}
		return true;
	}

	/* ---------------- Frontend ---------------- */

	public static function cart_fee( $cart ): void {
		if ( ( is_admin() && ! defined( 'DOING_AJAX' ) ) || ! $cart instanceof WC_Cart ) {
			return;
		}
		$o = self::options();
		if ( ! self::should_apply( $o ) ) {
			return;
		}
		// Solo en el checkout (incluye su AJAX y el envío del pedido); en el carrito aún no se eligió método.
		if ( ! is_checkout() ) {
			return;
		}
		$chosen = ( function_exists( 'WC' ) && WC()->session ) ? (string) WC()->session->get( 'chosen_payment_method' ) : '';
		if ( '' === $chosen || ! in_array( $chosen, (array) $o['gateways'], true ) ) {
			return;
		}

		$lines = array();
		foreach ( $cart->get_cart() as $item ) {
			$product = $item['data'] ?? null;
			if ( ! $product instanceof WC_Product ) {
				continue;
			}
			$parent = $product->get_parent_id() ? (int) $product->get_parent_id() : (int) $product->get_id();
			$cats   = array();
			foreach ( (array) wc_get_product_term_ids( $parent, 'product_cat' ) as $term_id ) {
				$cats[] = (int) $term_id;
				foreach ( (array) get_ancestors( (int) $term_id, 'product_cat', 'taxonomy' ) as $anc ) {
					$cats[] = (int) $anc; // una subcategoría también cuenta para su categoría padre
				}
			}
			$lines[] = array(
				'product_id' => (int) $product->get_id(),
				'parent_id'  => $parent,
				'categories' => array_values( array_unique( $cats ) ),
				'total'      => (float) ( $item['line_total'] ?? 0 ), // con cupones, sin impuestos, sin envío
			);
		}
		$amount = FanDeco_Transfer_Calc::amount(
			$lines,
			(float) $o['percent'],
			(string) $o['scope'],
			(array) $o['categories'],
			(array) $o['products'],
			function_exists( 'wc_get_price_decimals' ) ? (int) wc_get_price_decimals() : 2
		);
		if ( $amount <= 0 ) {
			return;
		}
		$pct  = rtrim( rtrim( number_format( FanDeco_Transfer_Calc::clamp_percent( $o['percent'] ), 2, '.', '' ), '0' ), '.' );
		$name = sprintf( '%s (%s%%)', (string) $o['label'], $pct );
		// Cargo gravado cuando hay impuestos activos: reduce también la parte de impuesto, quedando coherente con precios con IVA incluido.
		$cart->add_fee( $name, -$amount, (bool) ( $o['taxable'] && function_exists( 'wc_tax_enabled' ) && wc_tax_enabled() ), '' );
	}

	/** Etiqueta "10% OFF" junto al método de pago (solo visual en checkout; no se guarda en el pedido). */
	public static function gateway_badge( $icon, $gateway_id ) {
		$o = self::options();
		if ( is_admin() || ! self::should_apply( $o ) || ! in_array( (string) $gateway_id, (array) $o['gateways'], true ) || ! $o['badge'] ) {
			return $icon;
		}
		if ( ! function_exists( 'is_checkout' ) || ! is_checkout() || ( function_exists( 'is_order_received_page' ) && is_order_received_page() ) ) {
			return $icon;
		}
		$pct = rtrim( rtrim( number_format( FanDeco_Transfer_Calc::clamp_percent( $o['percent'] ), 2, '.', '' ), '0' ), '.' );
		return $icon . ' <span class="fd-pay-badge">' . esc_html( $pct ) . '% OFF</span>';
	}

	/** Refresca los totales al cambiar de método de pago (por si el checkout no lo hace por sí solo). */
	public static function checkout_script(): void {
		if ( ! function_exists( 'is_checkout' ) || ! is_checkout() || ! self::should_apply( self::options() ) || ! wp_script_is( 'wc-checkout', 'enqueued' ) ) {
			return;
		}
		wp_add_inline_script( 'wc-checkout', "jQuery(function($){\$('form.checkout').on('change','input[name=\"payment_method\"]',function(){\$(document.body).trigger('update_checkout');});});" );
	}

	/* ---------------- Administración ---------------- */

	public static function menu(): void {
		add_submenu_page( 'woocommerce', 'Descuento por transferencia', 'Descuento por transferencia', 'manage_woocommerce', 'fandeco-transfer', array( __CLASS__, 'render' ) );
	}

	public static function register(): void {
		register_setting(
			'fandeco_transfer',
			self::OPTION,
			array(
				'type'              => 'array',
				'sanitize_callback' => array( __CLASS__, 'sanitize' ),
				'default'           => self::defaults(),
			)
		);
	}

	public static function sanitize( $input ): array {
		$in  = is_array( $input ) ? $input : array();
		$def = self::defaults();
		$ids = static fn( $v ): array => array_values( array_unique( array_filter( array_map( 'absint', (array) $v ) ) ) );
		return array(
			'enabled'            => empty( $in['enabled'] ) ? 0 : 1,
			'percent'            => FanDeco_Transfer_Calc::clamp_percent( $in['percent'] ?? $def['percent'] ),
			'label'              => sanitize_text_field( $in['label'] ?? '' ) ?: $def['label'],
			'gateways'           => array_values( array_filter( array_map( 'sanitize_key', (array) ( $in['gateways'] ?? array() ) ) ) ),
			'scope'              => in_array( $in['scope'] ?? '', array( 'all', 'categories', 'products' ), true ) ? $in['scope'] : 'all',
			'categories'         => $ids( $in['categories'] ?? array() ),
			'products'           => $ids( $in['products'] ?? array() ),
			'taxable'            => empty( $in['taxable'] ) ? 0 : 1,
			'badge'              => empty( $in['badge'] ) ? 0 : 1,
			'confirm_no_overlap' => empty( $in['confirm_no_overlap'] ) ? 0 : 1,
		);
	}

	public static function admin_assets( string $hook ): void {
		if ( false === strpos( $hook, 'fandeco-transfer' ) ) {
			return;
		}
		wp_enqueue_style( 'woocommerce_admin_styles' );
		wp_enqueue_script( 'wc-enhanced-select' );
	}

	public static function conflict_notice(): void {
		$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
		if ( ! $screen || false === strpos( (string) $screen->id, 'fandeco-transfer' ) ) {
			return;
		}
		if ( self::conflicting_plugin_active() ) {
			echo '<div class="notice notice-warning"><p><strong>Hay un plugin activo de “Payment Gateway Based Fees and Discounts”.</strong> Para no descontar dos veces, este descuento <u>no se aplica</u> hasta que desactives esa regla/plugin y marques la casilla de confirmación de abajo.</p></div>';
		}
	}

	public static function render(): void {
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			return;
		}
		$o        = self::options();
		$name     = self::OPTION;
		$gateways = function_exists( 'WC' ) && WC()->payment_gateways() ? WC()->payment_gateways()->payment_gateways() : array();
		$cats     = get_terms( array( 'taxonomy' => 'product_cat', 'hide_empty' => false ) );
		?>
		<div class="wrap">
			<h1>Descuento por transferencia</h1>
			<p>Cuando el cliente elige uno de los métodos de pago seleccionados, se aplica un descuento al precio de los productos <strong>(después de cupones y sin incluir el envío)</strong>. Aparece como una línea de descuento en el carrito/checkout, el pedido y los emails. Solo funciona con el <strong>checkout clásico</strong>.</p>
			<form method="post" action="options.php">
				<?php settings_fields( 'fandeco_transfer' ); ?>
				<table class="form-table" role="presentation">
					<tr><th scope="row">Activar</th><td><label><input type="checkbox" name="<?php echo esc_attr( $name ); ?>[enabled]" value="1" <?php checked( $o['enabled'], 1 ); ?>> Aplicar el descuento</label><p class="description">Viene desactivado: probalo primero en staging.</p></td></tr>
					<tr><th scope="row"><label for="fdt_percent">Porcentaje (%)</label></th><td><input id="fdt_percent" type="number" step="0.01" min="0" max="99.99" name="<?php echo esc_attr( $name ); ?>[percent]" value="<?php echo esc_attr( (string) $o['percent'] ); ?>" class="small-text"></td></tr>
					<tr><th scope="row"><label for="fdt_label">Texto en el pedido</label></th><td><input id="fdt_label" type="text" class="regular-text" name="<?php echo esc_attr( $name ); ?>[label]" value="<?php echo esc_attr( (string) $o['label'] ); ?>"><p class="description">Se muestra como “Texto (10%)”.</p></td></tr>
					<tr><th scope="row">Métodos de pago que reciben el descuento</th><td>
						<?php foreach ( $gateways as $id => $gw ) : ?>
							<label style="display:block"><input type="checkbox" name="<?php echo esc_attr( $name ); ?>[gateways][]" value="<?php echo esc_attr( (string) $id ); ?>" <?php checked( in_array( (string) $id, (array) $o['gateways'], true ) ); ?>> <?php echo esc_html( $gw->get_method_title() ); ?> <code><?php echo esc_html( (string) $id ); ?></code><?php echo 'yes' === $gw->enabled ? '' : ' <em>(deshabilitado)</em>'; ?></label>
						<?php endforeach; ?>
					</td></tr>
					<tr><th scope="row">¿A qué productos aplica?</th><td>
						<label style="display:block"><input type="radio" name="<?php echo esc_attr( $name ); ?>[scope]" value="all" <?php checked( $o['scope'], 'all' ); ?>> A <strong>todos</strong> los productos</label>
						<label style="display:block"><input type="radio" name="<?php echo esc_attr( $name ); ?>[scope]" value="categories" <?php checked( $o['scope'], 'categories' ); ?>> Solo a ciertas <strong>categorías</strong></label>
						<label style="display:block"><input type="radio" name="<?php echo esc_attr( $name ); ?>[scope]" value="products" <?php checked( $o['scope'], 'products' ); ?>> Solo a ciertos <strong>productos</strong></label>
					</td></tr>
					<tr><th scope="row"><label for="fdt_cats">Categorías</label></th><td>
						<select id="fdt_cats" class="wc-enhanced-select" multiple style="min-width:350px" name="<?php echo esc_attr( $name ); ?>[categories][]" data-placeholder="Elegí categorías…">
							<?php if ( ! is_wp_error( $cats ) ) { foreach ( $cats as $c ) { printf( '<option value="%d" %s>%s</option>', (int) $c->term_id, selected( in_array( (int) $c->term_id, (array) $o['categories'], true ), true, false ), esc_html( $c->name ) ); } } ?>
						</select><p class="description">Incluye sus subcategorías.</p></td></tr>
					<tr><th scope="row"><label for="fdt_prods">Productos</label></th><td>
						<select id="fdt_prods" class="wc-product-search" multiple style="min-width:350px" name="<?php echo esc_attr( $name ); ?>[products][]" data-placeholder="Buscá productos…" data-action="woocommerce_json_search_products_and_variations">
							<?php foreach ( (array) $o['products'] as $pid ) { $p = wc_get_product( (int) $pid ); if ( $p ) { printf( '<option value="%d" selected>%s</option>', (int) $pid, esc_html( wp_strip_all_tags( $p->get_formatted_name() ) ) ); } } ?>
						</select></td></tr>
					<tr><th scope="row">Impuestos</th><td><label><input type="checkbox" name="<?php echo esc_attr( $name ); ?>[taxable]" value="1" <?php checked( $o['taxable'], 1 ); ?>> El descuento reduce también el impuesto (recomendado si los precios incluyen IVA)</label><p class="description">Solo tiene efecto si WooCommerce tiene impuestos activados. Confirmar con tu contador.</p></td></tr>
					<tr><th scope="row">Etiqueta en el checkout</th><td><label><input type="checkbox" name="<?php echo esc_attr( $name ); ?>[badge]" value="1" <?php checked( $o['badge'], 1 ); ?>> Mostrar “X% OFF” junto al método de pago (solo visual)</label></td></tr>
					<tr><th scope="row">Plugin de descuentos actual</th><td><label><input type="checkbox" name="<?php echo esc_attr( $name ); ?>[confirm_no_overlap]" value="1" <?php checked( $o['confirm_no_overlap'], 1 ); ?>> Confirmo que “Payment Gateway Based Fees and Discounts” está desactivado o sin reglas de descuento por transferencia</label></td></tr>
				</table>
				<?php submit_button(); ?>
			</form>
			<h2>Cómo se calcula</h2>
			<p>Descuento = <em>porcentaje</em> × (suma de los productos elegibles después de cupones). <strong>El envío y otros cargos no entran en la base.</strong> Los productos en oferta sí entran. Si el cliente cambia de método de pago, el total se recalcula.</p>
		</div>
		<?php
	}
}
