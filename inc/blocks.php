<?php
/**
 * Registro de bloques Gutenberg (prefijo fandeco/) y patrones.
 *
 * Los bloques se compilan con @wordpress/scripts: src/blocks/* -> build/blocks/*.
 *
 * @package FanDecoStudio
 */

defined( 'ABSPATH' ) || exit;

add_filter(
	'block_categories_all',
	static function ( array $categories ): array {
		array_unshift(
			$categories,
			array(
				'slug'  => 'fandeco',
				'title' => __( 'Fan Deco', 'fan-deco-studio' ),
			)
		);
		return $categories;
	}
);

add_action(
	'init',
	static function () {
		$dirs = glob( FANDECO_DIR . '/build/blocks/*/block.json' ) ?: array();
		foreach ( $dirs as $json ) {
			register_block_type_from_metadata( dirname( $json ) );
		}
		if ( function_exists( 'wp_set_script_translations' ) ) {
			// Traducciones JS (opcional; no falla si no existen).
			foreach ( $dirs as $json ) {
				$name   = basename( dirname( $json ) );
				$handle = generate_block_asset_handle( 'fandeco/' . $name, 'editorScript' );
				wp_set_script_translations( $handle, 'fan-deco-studio', FANDECO_DIR . '/languages' );
			}
		}
	}
);

// Prioridad 5: la categoría debe existir antes de que se registren los patrones de /patterns.
add_action(
	'init',
	static function () {
		register_block_pattern_category(
			'fandeco',
			array( 'label' => __( 'Fan Deco', 'fan-deco-studio' ) )
		);
	},
	5
);

add_action(
	'admin_notices',
	static function () {
		if ( ! current_user_can( 'edit_theme_options' ) || glob( FANDECO_DIR . '/build/blocks/*/block.json' ) ) {
			return;
		}
		echo '<div class="notice notice-warning"><p>' . esc_html__( 'Fan Deco Studio: faltan los bloques compilados (build/blocks). Ejecutá "npm run build" o instalá el ZIP generado con "npm run package".', 'fan-deco-studio' ) . '</p></div>';
	}
);

/**
 * Contenido de la home por defecto cuando no hay una página estática con bloques.
 */
function fandeco_default_home_content(): string {
	$registry = WP_Block_Patterns_Registry::get_instance();
	$pattern  = $registry->get_registered( 'fan-deco-studio/home' );
	return $pattern ? (string) $pattern['content'] : '';
}
