<?php
/**
 * Plugin Name: Fan Deco Blocks
 * Description: Bloques de diseño de Fan Deco (pila de banners, hero con interruptor de luz, franja de mensajes, categorías, productos destacados, banner editorial, pasos y beneficios) para usar sobre cualquier tema, como Blocksy. Con el tema Fan Deco Studio activo no hace nada (el tema ya los incluye).
 * Version: 0.4.3
 * Requires at least: 6.5
 * Requires PHP: 8.0
 * Author: Fan Deco
 * License: GPL-2.0-or-later
 * Text Domain: fan-deco-blocks
 *
 * @package FanDecoBlocks
 */

defined( 'ABSPATH' ) || exit;

define( 'FANDECO_BLOCKS_VERSION', '0.4.3' );
define( 'FANDECO_BLOCKS_DIR', plugin_dir_path( __FILE__ ) );
define( 'FANDECO_BLOCKS_URL', plugin_dir_url( __FILE__ ) );

// Con el tema Fan Deco Studio activo, el tema ya registra los bloques y sus estilos: el plugin se aparta.
if ( 'fan-deco-studio' === get_template() ) {
	add_action(
		'admin_notices',
		static function () {
			if ( current_user_can( 'activate_plugins' ) ) {
				echo '<div class="notice notice-info"><p>' . esc_html__( 'Fan Deco Blocks: el tema Fan Deco Studio ya incluye los bloques, así que este plugin no está haciendo nada.', 'fan-deco-blocks' ) . '</p></div>';
			}
		}
	);
	return;
}

require_once FANDECO_BLOCKS_DIR . 'includes/theme-shared.php';
require_once FANDECO_BLOCKS_DIR . 'includes/blocks.php';
require_once FANDECO_BLOCKS_DIR . 'includes/assets.php';
require_once FANDECO_BLOCKS_DIR . 'includes/related.php';
