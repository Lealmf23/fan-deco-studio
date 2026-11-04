<?php
/**
 * Render del bloque fandeco/featured-products. Solo lee productos reales; no modifica nada.
 *
 * @var array $attributes
 * @package FanDecoStudio
 */

defined( 'ABSPATH' ) || exit;

$notice = static function ( string $message ): void {
	if ( current_user_can( 'edit_posts' ) ) {
		echo '<p class="fd-notice">' . esc_html( $message ) . '</p>';
	}
};

if ( ! fandeco_has_woocommerce() ) {
	$notice( __( 'Fan Deco: activá WooCommerce para mostrar productos.', 'fan-deco-studio' ) );
	return;
}

$source = fandeco_enum( $attributes['source'] ?? '', array( 'featured', 'latest', 'sale', 'category', 'manual' ), 'featured' );
$layout = fandeco_enum( $attributes['layout'] ?? '', array( 'grid', 'carousel', 'editorial' ), 'carousel' );
$count  = max( 1, min( 12, absint( $attributes['count'] ?? 8 ) ) );
$args   = array(
	'status'     => 'publish',
	'limit'      => $count,
	'visibility' => 'catalog',
	'orderby'    => 'date',
	'order'      => 'DESC',
);

switch ( $source ) {
	case 'featured':
		$args['featured'] = true;
		break;
	case 'sale':
		$sale_ids        = wc_get_product_ids_on_sale();
		$args['include'] = $sale_ids ? array_slice( array_map( 'absint', $sale_ids ), 0, 100 ) : array( 0 );
		break;
	case 'category':
		$term = get_term( absint( $attributes['categoryId'] ?? 0 ), 'product_cat' );
		if ( ! $term || is_wp_error( $term ) ) {
			$notice( __( 'Fan Deco: elegí una categoría de producto.', 'fan-deco-studio' ) );
			return;
		}
		$args['category'] = array( $term->slug );
		break;
	case 'manual':
		$manual = array_values( array_filter( array_map( 'absint', (array) ( $attributes['productIds'] ?? array() ) ) ) );
		if ( ! $manual ) {
			$notice( __( 'Fan Deco: agregá productos a la selección manual.', 'fan-deco-studio' ) );
			return;
		}
		$args['include'] = $manual;
		$args['limit']   = count( $manual );
		break;
}

$products = wc_get_products( $args );
if ( 'manual' === $source && $products ) {
	usort(
		$products,
		static fn( $a, $b ) => array_search( $a->get_id(), $manual, true ) <=> array_search( $b->get_id(), $manual, true )
	);
}
if ( ! $products ) {
	$notice( __( 'Fan Deco: no hay productos para mostrar con esta configuración.', 'fan-deco-studio' ) );
	return;
}
$show_cart = ! empty( $attributes['showAddToCart'] );
?>
<?php
$f_title = sanitize_text_field( $attributes['title'] ?? '' );
$f_desc  = sanitize_textarea_field( $attributes['description'] ?? '' );
$f_link  = (string) ( $attributes['linkUrl'] ?? '' );
$f_ltxt  = trim( (string) ( $attributes['linkText'] ?? '' ) );
$editorial = 'editorial' === $layout;
if ( $editorial ) {
	$layout = 'carousel';
}
?>
<section <?php echo fandeco_block_attrs( 'featured-products', $attributes, 'fd-featured--' . ( $editorial ? 'editorial' : $layout ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?>>
	<div class="fd-container<?php echo $editorial ? ' fd-featured__split' : ''; ?>">
		<?php if ( $editorial ) : ?>
			<div class="fd-featured__side">
				<?php if ( $f_title ) : ?><h2 class="fd-featured__title" data-fd-reveal><?php echo esc_html( $f_title ); ?></h2><?php endif; ?>
				<?php if ( $f_desc ) : ?><p class="fd-featured__desc" data-fd-reveal><?php echo esc_html( $f_desc ); ?></p><?php endif; ?>
				<?php if ( $f_link && $f_ltxt ) : ?><a class="fd-link-arrow" href="<?php echo esc_url( $f_link ); ?>" data-fd-reveal><?php echo esc_html( $f_ltxt ); ?> <?php echo fandeco_icon( 'arrow-right' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></a><?php endif; ?>
			</div>
			<div class="fd-featured__main">
		<?php endif; ?>
		<?php if ( 'carousel' === $layout ) : ?>
		<div class="fd-carousel" data-fd-carousel>
			<div class="fd-carousel__controls">
				<button type="button" class="fd-icon-btn fd-icon-btn--ring" data-fd-carousel-prev aria-label="<?php esc_attr_e( 'Anterior', 'fan-deco-studio' ); ?>"><?php echo fandeco_icon( 'chevron-l' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></button>
				<button type="button" class="fd-icon-btn fd-icon-btn--ring" data-fd-carousel-next aria-label="<?php esc_attr_e( 'Siguiente', 'fan-deco-studio' ); ?>"><?php echo fandeco_icon( 'chevron-r' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></button>
			</div>
			<ul class="fd-carousel__track fd-product-grid" tabindex="0" aria-label="<?php esc_attr_e( 'Productos destacados', 'fan-deco-studio' ); ?>">
		<?php else : ?>
			<ul class="fd-product-grid">
		<?php endif; ?>
			<?php foreach ( $products as $product ) : ?>
				<li class="fd-product-grid__item">
					<?php get_template_part( 'template-parts/components/product-card', null, array( 'product' => $product, 'show_cart' => $show_cart ) ); ?>
				</li>
			<?php endforeach; ?>
			</ul>
		<?php if ( 'carousel' === $layout ) : ?>
		</div>
		<?php endif; ?>
		<?php if ( $editorial ) : ?></div><?php endif; ?>
	</div>
</section>
