<?php
/**
 * Productos relacionados con el estilo de las tarjetas de Fan Deco, en la ficha de producto (Blocksy u otro tema).
 * Reemplaza la sección de relacionados del tema por un carrusel con las mismas tarjetas del bloque "Productos destacados".
 * Para apagarlo: add_filter( 'fandeco_blocks_related', '__return_false' );
 *
 * @package FanDecoBlocks
 */

defined( 'ABSPATH' ) || exit;

function fandeco_blocks_related_enabled(): bool {
	return function_exists( 'is_product' ) && is_product() && (bool) apply_filters( 'fandeco_blocks_related', true );
}

// La sección de relacionados que arma el tema/WooCommerce queda vacía (la nuestra la reemplaza).
add_filter(
	'woocommerce_related_products',
	static function ( $ids ) {
		return ( empty( $GLOBALS['fandeco_related_own'] ) && fandeco_blocks_related_enabled() ) ? array() : $ids;
	},
	99
);

add_action(
	'woocommerce_after_single_product_summary',
	static function () {
		if ( ! fandeco_blocks_related_enabled() || ! function_exists( 'wc_get_related_products' ) || ! function_exists( 'fandeco_has_woocommerce' ) ) {
			return;
		}
		$product_id = get_the_ID();
		$count      = max( 1, min( 12, (int) apply_filters( 'fandeco_blocks_related_count', 8 ) ) );
		$GLOBALS['fandeco_related_own'] = true;
		$ids                            = array_values( array_filter( array_map( 'absint', wc_get_related_products( $product_id, $count ) ) ) );
		$GLOBALS['fandeco_related_own'] = false;
		if ( ! $ids ) {
			return;
		}
		$title = (string) apply_filters( 'fandeco_blocks_related_title', __( 'Productos relacionados', 'fan-deco-blocks' ) );
		$html  = render_block(
			array(
				'blockName'    => 'fandeco/featured-products',
				'attrs'        => array(
					'source'        => 'manual',
					'productIds'    => $ids,
					'layout'        => 'carousel',
					'showAddToCart' => true,
					'animate'       => false,
				),
				'innerBlocks'  => array(),
				'innerHTML'    => '',
				'innerContent' => array(),
			)
		);
		if ( '' === trim( $html ) ) {
			return;
		}
		echo '<section class="fd-related"><div class="fd-container"><h2 class="fd-related__title">' . esc_html( $title ) . '</h2></div>' . $html . '</section>'; // phpcs:ignore WordPress.Security.EscapeOutput -- el bloque escapa su salida.
	},
	21
);
