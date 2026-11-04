<?php
/**
 * Render del bloque fandeco/hero.
 *
 * @var array $attributes
 * @package FanDecoStudio
 */

defined( 'ABSPATH' ) || exit;

$variant = fandeco_enum( $attributes['variant'] ?? '', array( 'split', 'split-reverse', 'overlay', 'immersive' ), 'split' );
$align   = fandeco_enum( $attributes['align'] ?? '', array( 'left', 'center' ), 'left' );
$eyebrow = sanitize_text_field( $attributes['eyebrow'] ?? '' );
$title   = sanitize_text_field( $attributes['title'] ?? '' );
$desc    = sanitize_textarea_field( $attributes['description'] ?? '' );
$btn_url = (string) ( $attributes['buttonUrl'] ?? '' );
if ( '' === $btn_url && fandeco_has_woocommerce() ) {
	$btn_url = wc_get_page_permalink( 'shop' );
}
$lit_id  = absint( $attributes['imageLitId'] ?? 0 );
$has_lit = $lit_id > 0 && wp_attachment_is_image( $lit_id );
$lit_img = $has_lit ? fandeco_attachment_image( $lit_id, 'fandeco-hero', array( 'class' => 'fd-hero__lit', 'alt' => '', 'aria-hidden' => 'true', 'decoding' => 'async', 'fetchpriority' => 'low', 'sizes' => '100vw' ) ) : '';
$is_lit  = $has_lit && ! empty( $attributes['startLit'] );
$picture = fandeco_picture( absint( $attributes['desktopImageId'] ?? 0 ), absint( $attributes['mobileImageId'] ?? 0 ), 'fd-hero__img', true );
?>
<section <?php echo fandeco_block_attrs( 'hero', $attributes, "fd-hero--{$variant} fd-hero--align-{$align}" . ( $has_lit ? ' fd-hero--has-light' : '' ) . ( $is_lit ? ' is-lit' : '' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?><?php echo $has_lit ? ' data-fd-hero-light' : ''; ?>>
	<div class="fd-hero__inner fd-container">
		<div class="fd-hero__content">
			<?php if ( $eyebrow ) : ?><p class="fd-eyebrow" data-fd-reveal><?php echo esc_html( $eyebrow ); ?></p><?php endif; ?>
			<?php if ( $title ) : ?><h1 class="fd-hero__title" data-fd-reveal><?php echo esc_html( $title ); ?></h1><?php endif; ?>
			<?php if ( $desc ) : ?><p class="fd-hero__desc" data-fd-reveal><?php echo esc_html( $desc ); ?></p><?php endif; ?>
			<?php $btn = fandeco_button( (string) ( $attributes['buttonText'] ?? '' ), $btn_url, 'primary', 'fd-btn--lg' ); ?>
			<?php if ( $btn || $has_lit ) : ?>
				<div class="fd-hero__actions" data-fd-reveal>
					<?php echo $btn; // phpcs:ignore WordPress.Security.EscapeOutput ?>
					<?php if ( $has_lit ) : ?>
						<button type="button" class="fd-hero-switch" role="switch" aria-checked="<?php echo $is_lit ? 'true' : 'false'; ?>" data-fd-hero-switch data-off="<?php esc_attr_e( 'Luz apagada', 'fan-deco-studio' ); ?>" data-on="<?php esc_attr_e( 'Luz encendida', 'fan-deco-studio' ); ?>">
							<span class="fd-hero-switch__track" aria-hidden="true"><span class="fd-hero-switch__thumb"></span></span>
							<span class="fd-hero-switch__label" data-fd-hero-switch-label><?php echo $is_lit ? esc_html__( 'Luz encendida', 'fan-deco-studio' ) : esc_html__( 'Luz apagada', 'fan-deco-studio' ); ?></span>
						</button>
					<?php endif; ?>
				</div>
			<?php endif; ?>
		</div>
		<div class="fd-hero__media" data-fd-reveal="media">
			<?php
			if ( $picture ) {
				echo $picture; // phpcs:ignore WordPress.Security.EscapeOutput -- imagen de WordPress.
				echo $lit_img; // phpcs:ignore WordPress.Security.EscapeOutput -- imagen de WordPress.
			} else {
				echo '<div class="fd-media-placeholder" aria-hidden="true"></div>';
			}
			?>
		</div>
	</div>
</section>
