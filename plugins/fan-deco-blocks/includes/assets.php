<?php
/**
 * Estilos y scripts de los bloques: se cargan solo en las páginas que usan algún bloque fandeco/*.
 *
 * @package FanDecoBlocks
 */

defined( 'ABSPATH' ) || exit;

/** ¿Se está mostrando contenido con algún bloque de Fan Deco? */
function fandeco_blocks_needed(): bool {
	if ( is_singular() ) {
		$post = get_post();
		return $post && false !== strpos( (string) $post->post_content, '<!-- wp:fandeco/' );
	}
	return false;
}

function fandeco_blocks_enqueue(): void {
	static $done = false;
	if ( $done ) {
		return;
	}
	$done = true;
	if ( is_readable( FANDECO_BLOCKS_DIR . 'dist/blocks.css' ) ) {
		wp_enqueue_style( 'fandeco-blocks', FANDECO_BLOCKS_URL . 'dist/blocks.css', array(), FANDECO_BLOCKS_VERSION );
	}
	if ( is_readable( FANDECO_BLOCKS_DIR . 'dist/blocks.js' ) ) {
		wp_enqueue_script( 'fandeco-blocks', FANDECO_BLOCKS_URL . 'dist/blocks.js', array(), FANDECO_BLOCKS_VERSION, array( 'in_footer' => true, 'strategy' => 'defer' ) );
	}
}

add_action(
	'wp_enqueue_scripts',
	static function () {
		if ( fandeco_blocks_needed() ) {
			fandeco_blocks_enqueue();
		}
	}
);

// Respaldo: bloques dentro de plantillas o patrones reutilizables (se detectan al dibujarse).
add_filter(
	'render_block',
	static function ( $html, $block ) {
		if ( isset( $block['blockName'] ) && 0 === strpos( (string) $block['blockName'], 'fandeco/' ) ) {
			fandeco_blocks_enqueue();
		}
		return $html;
	},
	10,
	2
);

// Los estilos también se cargan en el editor para ver los bloques como quedan.
add_action(
	'enqueue_block_editor_assets',
	static function () {
		if ( is_readable( FANDECO_BLOCKS_DIR . 'dist/blocks.css' ) ) {
			wp_enqueue_style( 'fandeco-blocks-editor', FANDECO_BLOCKS_URL . 'dist/blocks.css', array(), FANDECO_BLOCKS_VERSION );
		}
	}
);
