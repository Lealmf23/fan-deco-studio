<?php
/**
 * Plugin Name: Fan Deco Core
 * Description: Motor SEO propio (reemplazo de AIOSEO), redirecciones e importador para la tienda Fan Deco. Funciona con cualquier tema; pensado para Fan Deco Studio.
 * Version: 0.2.0
 * Requires at least: 6.5
 * Requires PHP: 8.0
 * Author: Fan Deco
 * License: GPL-2.0-or-later
 * Text Domain: fan-deco-core
 *
 * @package FanDecoCore
 */

defined( 'ABSPATH' ) || exit;

define( 'FANDECO_CORE_VERSION', '0.2.0' );
define( 'FANDECO_CORE_FILE', __FILE__ );
define( 'FANDECO_CORE_DIR', plugin_dir_path( __FILE__ ) );
define( 'FANDECO_CORE_URL', plugin_dir_url( __FILE__ ) );

// El plugin no toca pedidos ni el checkout: se declara compatible con HPOS y con Carrito/Checkout por bloques.
add_action(
	'before_woocommerce_init',
	static function () {
		if ( class_exists( '\\Automattic\\WooCommerce\\Utilities\\FeaturesUtil' ) ) {
			\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', FANDECO_CORE_FILE, true );
			\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'cart_checkout_blocks', FANDECO_CORE_FILE, true );
		}
	}
);

require_once FANDECO_CORE_DIR . 'includes/class-text.php';
require_once FANDECO_CORE_DIR . 'includes/class-redirect-rules.php';
require_once FANDECO_CORE_DIR . 'includes/class-settings.php';
require_once FANDECO_CORE_DIR . 'includes/class-meta.php';
require_once FANDECO_CORE_DIR . 'includes/class-frontend.php';
require_once FANDECO_CORE_DIR . 'includes/class-sitemaps.php';
require_once FANDECO_CORE_DIR . 'includes/class-redirects.php';
require_once FANDECO_CORE_DIR . 'includes/class-import-aioseo.php';
require_once FANDECO_CORE_DIR . 'includes/class-transfer-calc.php';
require_once FANDECO_CORE_DIR . 'includes/class-transfer-discount.php';
require_once FANDECO_CORE_DIR . 'includes/class-installments-calc.php';
require_once FANDECO_CORE_DIR . 'includes/class-installments.php';
require_once FANDECO_CORE_DIR . 'includes/class-lights.php';
require_once FANDECO_CORE_DIR . 'includes/class-pinfo-parse.php';
require_once FANDECO_CORE_DIR . 'includes/class-product-info.php';

add_action(
	'plugins_loaded',
	static function () {
		FanDeco_Settings::init();
		FanDeco_Meta::init();
		FanDeco_Frontend::init();
		FanDeco_Sitemaps::init();
		FanDeco_Redirects::init();
		FanDeco_Import_AIOSEO::init();
		if ( class_exists( 'WooCommerce' ) ) {
			FanDeco_Transfer_Discount::init();
			FanDeco_Installments::init();
			FanDeco_Lights::init();
			FanDeco_Product_Info::init();
		}
	}
);
