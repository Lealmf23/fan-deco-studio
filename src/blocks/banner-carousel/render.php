<?php
/**
 * Render del bloque fandeco/banner-carousel: pila de capas (las fotos se apilan como capas de una impresión 3D).
 *
 * @var array $attributes
 * @package FanDecoStudio
 */

defined( 'ABSPATH' ) || exit;

$slides = array();
foreach ( array_slice( (array) ( $attributes['slides'] ?? array() ), 0, 8 ) as $slide ) {
	if ( ! is_array( $slide ) ) {
		continue;
	}
	$id = absint( $slide['imageId'] ?? 0 );
	if ( $id <= 0 || ! wp_attachment_is_image( $id ) ) {
		continue;
	}
	$slides[] = array(
		'id'  => $id,
		'alt' => sanitize_text_field( (string) ( $slide['alt'] ?? '' ) ),
		'url' => esc_url( (string) ( $slide['url'] ?? '' ) ),
	);
}
if ( ! $slides ) {
	if ( current_user_can( 'edit_posts' ) ) {
		echo '<p class="fd-notice">' . esc_html__( 'Fan Deco: agregá banners al carrusel (panel derecho del bloque).', 'fan-deco-studio' ) . '</p>';
	}
	return;
}
$total    = count( $slides );
$autoplay = ! empty( $attributes['autoplay'] ) && $total > 1;
$interval = max( 3, min( 8, (int) ( $attributes['interval'] ?? 5 ) ) );
?>
<section <?php echo fandeco_block_attrs( 'bancar', $attributes ); // phpcs:ignore WordPress.Security.EscapeOutput ?> data-fd-bancar data-autoplay="<?php echo $autoplay ? '1' : '0'; ?>" data-interval="<?php echo (int) $interval; ?>" role="region" aria-roledescription="<?php esc_attr_e( 'carrusel', 'fan-deco-studio' ); ?>" aria-label="<?php esc_attr_e( 'Banners destacados', 'fan-deco-studio' ); ?>">
	<div class="fd-container">
		<div class="fd-bancar__frame" data-fd-reveal>
			<ul class="fd-bancar__track" data-fd-bancar-track role="list">
				<?php foreach ( $slides as $i => $slide ) : ?>
					<?php
					$img = fandeco_attachment_image(
						$slide['id'],
						'large',
						array(
							'alt'      => $slide['alt'],
							'loading'  => 0 === $i ? 'eager' : 'lazy',
							'decoding' => 'async',
							'sizes'    => '(max-width: 639px) 74vw, 34rem',
						)
					);
					if ( ! $img ) {
						continue;
					}
					?>
					<li class="fd-bancar__slide" role="group" aria-roledescription="<?php esc_attr_e( 'banner', 'fan-deco-studio' ); ?>" aria-label="<?php echo esc_attr( sprintf( /* translators: 1: número, 2: total */ __( '%1$d de %2$d', 'fan-deco-studio' ), $i + 1, $total ) ); ?>">
						<?php if ( $slide['url'] ) : ?>
							<a class="fd-bancar__link" href="<?php echo esc_url( $slide['url'] ); ?>"><?php echo $img; // phpcs:ignore WordPress.Security.EscapeOutput -- imagen de WordPress. ?></a>
						<?php else : ?>
							<?php echo $img; // phpcs:ignore WordPress.Security.EscapeOutput -- imagen de WordPress. ?>
						<?php endif; ?>
					</li>
				<?php endforeach; ?>
			</ul>
			<?php if ( $total > 1 ) : ?>
				<div class="fd-bancar__controls" data-fd-bancar-controls hidden>
					<button type="button" class="fd-bancar__arrow" data-fd-bancar-prev aria-label="<?php esc_attr_e( 'Banner anterior', 'fan-deco-studio' ); ?>"><?php echo fandeco_icon( 'chevron-l' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></button>
					<div class="fd-bancar__dots" data-fd-bancar-dots></div>
					<?php if ( $autoplay ) : ?>
						<button type="button" class="fd-bancar__pause" data-fd-bancar-pause aria-pressed="false" data-pause="<?php esc_attr_e( 'Pausar el avance automático', 'fan-deco-studio' ); ?>" data-play="<?php esc_attr_e( 'Reanudar el avance automático', 'fan-deco-studio' ); ?>" aria-label="<?php esc_attr_e( 'Pausar el avance automático', 'fan-deco-studio' ); ?>"><span aria-hidden="true"></span></button>
					<?php endif; ?>
					<button type="button" class="fd-bancar__arrow" data-fd-bancar-next aria-label="<?php esc_attr_e( 'Banner siguiente', 'fan-deco-studio' ); ?>"><?php echo fandeco_icon( 'chevron-r' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></button>
				</div>
			<?php endif; ?>
		</div>
	</div>
</section>
