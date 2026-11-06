<?php
/**
 * Registro de los bloques fandeco/* (compilados en build/blocks), categoría, tamaños de imagen, tarjeta de producto y patrón de portada.
 *
 * @package FanDecoBlocks
 */

defined( 'ABSPATH' ) || exit;

add_filter(
	'block_categories_all',
	static function ( array $categories ): array {
		array_unshift( $categories, array( 'slug' => 'fandeco', 'title' => __( 'Fan Deco', 'fan-deco-blocks' ) ) );
		return $categories;
	}
);

add_action(
	'init',
	static function () {
		foreach ( glob( FANDECO_BLOCKS_DIR . 'build/blocks/*/block.json' ) ?: array() as $json ) {
			register_block_type_from_metadata( dirname( $json ) );
		}
	}
);

// Tamaños de imagen que usan los bloques (si no existen, WordPress usa la imagen completa).
add_action(
	'after_setup_theme',
	static function () {
		add_image_size( 'fandeco-hero', 1920, 1080, false );
		add_image_size( 'fandeco-hero-mobile', 900, 1100, true );
		add_image_size( 'fandeco-card', 640, 800, true );
	}
);

// La tarjeta de producto de los bloques (el tema de Blocksy no la trae): sin botón de vista rápida, que ya ofrece Blocksy Pro.
add_action(
	'get_template_part_template-parts/components/product-card',
	static function ( $slug, $name, $args ) {
		if ( locate_template( 'template-parts/components/product-card.php' ) ) {
			return; // un tema con su propia tarjeta manda.
		}
		$args = is_array( $args ) ? $args : array();
		$args['no_quickview'] = true;
		include FANDECO_BLOCKS_DIR . 'template-parts/product-card.php';
	},
	10,
	3
);

// Patrón de portada completo.
add_action(
	'init',
	static function () {
		register_block_pattern_category( 'fandeco', array( 'label' => __( 'Fan Deco', 'fan-deco-blocks' ) ) );
		$file = FANDECO_BLOCKS_DIR . 'patterns/home.php';
		if ( ! is_readable( $file ) ) {
			return;
		}
		ob_start();
		include $file;
		$content = (string) ob_get_clean();
		register_block_pattern(
			'fan-deco-blocks/home',
			array(
				'title'      => __( 'Fan Deco: Portada completa', 'fan-deco-blocks' ),
				'categories' => array( 'fandeco' ),
				'keywords'   => array( 'home', 'portada', 'inicio' ),
				'content'    => $content,
			)
		);
	},
	6
);

// Las funciones fandeco_* del tema leen opciones del Personalizador del tema; sin él devuelven el valor por defecto.
if ( ! function_exists( 'fandeco_option' ) ) {
	function fandeco_option( string $key, $default = '' ) {
		return get_theme_mod( 'fandeco_' . $key, $default );
	}
}
