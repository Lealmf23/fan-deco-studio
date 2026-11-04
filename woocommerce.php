<?php
/**
 * Archivo de tienda, categorías, etiquetas, búsqueda de productos y producto individual.
 * El markup interno lo genera WooCommerce (plantillas oficiales + hooks); acá solo se envuelve.
 * Los wrappers están definidos en inc/woocommerce.php.
 *
 * @package FanDecoStudio
 */

defined( 'ABSPATH' ) || exit;

get_header();
woocommerce_content();
get_footer();
