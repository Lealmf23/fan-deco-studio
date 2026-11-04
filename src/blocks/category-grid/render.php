<?php
/**
 * Render del bloque fandeco/category-grid (datos reales de WooCommerce).
 *
 * @var array $attributes
 * @package FanDecoStudio
 */

defined( 'ABSPATH' ) || exit;

if ( ! fandeco_has_woocommerce() ) {
	if ( current_user_can( 'edit_posts' ) ) {
		echo '<p class="fd-notice">' . esc_html__( 'Fan Deco: activá WooCommerce para mostrar categorías.', 'fan-deco-studio' ) . '</p>';
	}
	return;
}

$ids  = array_values( array_filter( array_map( 'absint', (array) ( $attributes['categoryIds'] ?? array() ) ) ) );
$cols = fandeco_enum( (string) ( $attributes['columns'] ?? 4 ), array( '2', '3', '4' ), '4' );
$args = array(
	'taxonomy'   => 'product_cat',
	'hide_empty' => false,
	'exclude'    => array( (int) get_option( 'default_product_cat' ) ),
);
if ( $ids ) {
	$args['include'] = $ids;
	$args['orderby'] = 'include';
} else {
	$args['parent']     = 0;
	$args['hide_empty'] = true;
	$args['number']     = 4;
	$args['orderby']    = 'menu_order';
}
$terms = get_terms( $args );
if ( is_wp_error( $terms ) || ! $terms ) {
	if ( current_user_can( 'edit_posts' ) ) {
		echo '<p class="fd-notice">' . esc_html__( 'Fan Deco: no hay categorías para mostrar.', 'fan-deco-studio' ) . '</p>';
	}
	return;
}
$show_desc  = ! empty( $attributes['showDescription'] );
$show_count = ! empty( $attributes['showCount'] );
?>
<section <?php echo fandeco_block_attrs( 'category-grid', $attributes, 'fd-cols-' . $cols ); // phpcs:ignore WordPress.Security.EscapeOutput ?>>
	<div class="fd-container">
		<ul class="fd-cat-grid">
			<?php
			foreach ( $terms as $term ) :
				$thumb = (int) get_term_meta( $term->term_id, 'thumbnail_id', true );
				$link  = get_term_link( $term );
				if ( is_wp_error( $link ) ) {
					continue;
				}
				?>
				<li class="fd-cat-grid__item" data-fd-reveal>
					<a class="fd-cat-card" href="<?php echo esc_url( $link ); ?>">
						<span class="fd-cat-card__media">
							<?php
							$img = $thumb ? fandeco_attachment_image( $thumb, 'fandeco-card', array( 'loading' => 'lazy', 'decoding' => 'async', 'sizes' => '(max-width: 767px) 50vw, 25vw', 'alt' => '' ) ) : '';
							echo $img ? $img : '<span class="fd-media-placeholder" aria-hidden="true"></span>'; // phpcs:ignore WordPress.Security.EscapeOutput
							?>
						</span>
						<span class="fd-cat-card__body">
							<span class="fd-cat-card__title"><?php echo esc_html( $term->name ); ?></span>
							<?php if ( $show_count ) : ?>
								<span class="fd-cat-card__count">
									<?php
									/* translators: %d: cantidad de productos */
									echo esc_html( sprintf( _n( '%d producto', '%d productos', (int) $term->count, 'fan-deco-studio' ), (int) $term->count ) );
									?>
								</span>
							<?php endif; ?>
							<?php if ( $show_desc && $term->description ) : ?>
								<span class="fd-cat-card__desc"><?php echo esc_html( wp_strip_all_tags( $term->description ) ); ?></span>
							<?php endif; ?>
						</span>
					</a>
				</li>
			<?php endforeach; ?>
		</ul>
	</div>
</section>
