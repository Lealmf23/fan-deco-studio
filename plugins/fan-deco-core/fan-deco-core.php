<?php
/**
 * Plugin Name: Fan Deco Core
 * Description: Motor SEO propio (reemplazo de AIOSEO), redirecciones e importador para la tienda Fan Deco. Funciona con cualquier tema; pensado para Fan Deco Studio.
 * Version: 0.1.0
 * Requires at least: 6.5
 * Requires PHP: 8.0
 * Author: Fan Deco
 * License: GPL-2.0-or-later
 * Text Domain: fan-deco-core
 *
 * @package FanDecoCore
 */

defined( 'ABSPATH' ) || exit;

define( 'FANDECO_CORE_VERSION', '0.1.0' );
define( 'FANDECO_CORE_FILE', __FILE__ );
define( 'FANDECO_CORE_DIR', plugin_dir_path( __FILE__ ) );
define( 'FANDECO_CORE_URL', plugin_dir_url( __FILE__ ) );

require_once FANDECO_CORE_DIR . 'includes/class-text.php';
require_once FANDECO_CORE_DIR . 'includes/class-redirect-rules.php';
require_once FANDECO_CORE_DIR . 'includes/class-settings.php';
require_once FANDECO_CORE_DIR . 'includes/class-meta.php';
require_once FANDECO_CORE_DIR . 'includes/class-frontend.php';
require_once FANDECO_CORE_DIR . 'includes/class-sitemaps.php';
require_once FANDECO_CORE_DIR . 'includes/class-redirects.php';
require_once FANDECO_CORE_DIR . 'includes/class-import-aioseo.php';

add_action(
	'plugins_loaded',
	static function () {
		FanDeco_Settings::init();
		FanDeco_Meta::init();
		FanDeco_Frontend::init();
		FanDeco_Sitemaps::init();
		FanDeco_Redirects::init();
		FanDeco_Import_AIOSEO::init();
	}
);
