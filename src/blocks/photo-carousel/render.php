<?php
/**
 * Render del bloque fandeco/photo-carousel: carrusel de solo fotos con zoom suave y elevación al pasar el mouse.
 *
 * @var array $attributes
 * @package FanDecoStudio
 */

defined( 'ABSPATH' ) || exit;

$photos = array();
foreach ( array_slice( (array) ( $attributes['photos'] ?? array() ), 0, 16 ) as $photo ) {
	if ( ! is_array( $photo ) ) {
		continue;
	}
	$id = absint( $photo['imageId'] ?? 0 );
	if ( $id <= 0 || ! wp_attachment_is_image( $id ) ) {
		continue;
	}
	$photos[] = array(
		'id'      => $id,
		'alt'     => sanitize_text_field( (string) ( $photo['alt'] ?? '' ) ),
		'caption' => sanitize_text_field( (string) ( $photo['caption'] ?? '' ) ),
		'url'     => esc_url( (string) ( $photo['url'] ?? '' ) ),
	);
}
if ( ! $photos ) {
	if ( current_user_can( 'edit_posts' ) ) {
		echo '<p class="fd-notice">' . esc_html__( 'Fan Deco: agregá fotos al carrusel (panel derecho del bloque).', 'fan-deco-studio' ) . '</p>';
	}
	return;
}
$per      = max( 2, min( 5, absint( $attributes['perView'] ?? 4 ) ) );
$ratio    = fandeco_enum( (string) ( $attributes['ratio'] ?? '' ), array( '3/4', '4/5', '1/1', '16/9' ), '3/4' );
$captions = ! isset( $attributes['showCaptions'] ) || ! empty( $attributes['showCaptions'] );
$arrows   = ( ! isset( $attributes['showArrows'] ) || ! empty( $attributes['showArrows'] ) ) && count( $photos ) > 1;
$hover    = ! isset( $attributes['hoverEffect'] ) || ! empty( $attributes['hoverEffect'] );
$style    = '--fd-per:' . $per . ';--fd-photo-ratio:' . str_replace( '/', ' / ', $ratio ) . ';';
?>
<section <?php echo fandeco_block_attrs( 'photos', $attributes, $hover ? 'fd-photos--hover' : '', $style ); // phpcs:ignore WordPress.Security.EscapeOutput ?> role="region" aria-roledescription="<?php esc_attr_e( 'carrusel', 'fan-deco-studio' ); ?>" aria-label="<?php esc_attr_e( 'Galería de fotos', 'fan-deco-studio' ); ?>">
	<div class="fd-container">
		<div class="fd-carousel" data-fd-carousel>
			<?php if ( $arrows ) : ?>
				<div class="fd-carousel__controls">
					<button type="button" class="fd-icon-btn fd-icon-btn--ring" data-fd-carousel-prev aria-label="<?php esc_attr_e( 'Anterior', 'fan-deco-studio' ); ?>"><?php echo fandeco_icon( 'chevron-l' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></button>
					<button type="button" class="fd-icon-btn fd-icon-btn--ring" data-fd-carousel-next aria-label="<?php esc_attr_e( 'Siguiente', 'fan-deco-studio' ); ?>"><?php echo fandeco_icon( 'chevron-r' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></button>
				</div>
			<?php endif; ?>
			<ul class="fd-carousel__track fd-photos__track" tabindex="0" role="list" aria-label="<?php esc_attr_e( 'Fotos', 'fan-deco-studio' ); ?>">
				<?php foreach ( $photos as $i => $photo ) : ?>
					<?php
					$img = fandeco_attachment_image(
						$photo['id'],
						'large',
						array(
							'alt'      => $photo['alt'],
							'loading'  => $i < 2 ? 'eager' : 'lazy',
							'decoding' => 'async',
							'sizes'    => '(max-width: 767px) 72vw, ' . (int) round( 100 / $per ) . 'vw',
						)
					);
					if ( '' === $img ) {
						continue;
					}
					$tag   = $photo['url'] ? 'a' : 'div';
					$attrs = $photo['url'] ? ' href="' . esc_url( $photo['url'] ) . '"' : '';
					?>
					<li class="fd-photo-item">
						<<?php echo $tag; // phpcs:ignore WordPress.Security.EscapeOutput -- 'a' o 'div'. ?> class="fd-photo"<?php echo $attrs; // phpcs:ignore WordPress.Security.EscapeOutput -- esc_url arriba. ?>>
							<?php echo $img; // phpcs:ignore WordPress.Security.EscapeOutput -- salida de WordPress. ?>
							<?php if ( $captions && $photo['caption'] ) : ?><span class="fd-photo__cap"><?php echo esc_html( $photo['caption'] ); ?></span><?php endif; ?>
						</<?php echo $tag; // phpcs:ignore WordPress.Security.EscapeOutput ?>>
					</li>
				<?php endforeach; ?>
			</ul>
		</div>
	</div>
</section>
