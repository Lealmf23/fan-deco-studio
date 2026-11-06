<?php
/**
 * Fan Deco Studio — bootstrap del tema.
 *
 * @package FanDecoStudio
 */

defined( 'ABSPATH' ) || exit;

define( 'FANDECO_VERSION', '0.3.10' );
define( 'FANDECO_DIR', get_template_directory() );
define( 'FANDECO_URI', get_template_directory_uri() );

foreach ( array( 'helpers', 'setup', 'menus', 'customizer', 'assets', 'blocks', 'seo', 'woocommerce', 'swatches', 'design' ) as $fandeco_module ) {
	require_once FANDECO_DIR . "/inc/{$fandeco_module}.php";
}
unset( $fandeco_module );
