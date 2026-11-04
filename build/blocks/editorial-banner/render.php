<?php
/**
 * Render del bloque fandeco/editorial-banner.
 *
 * @var array $attributes
 * @package FanDecoStudio
 */

defined( 'ABSPATH' ) || exit;

$variant  = fandeco_enum( $attributes['variant'] ?? '', array( 'light', 'dark', 'wine', 'immersive' ), 'light' );
$position = fandeco_enum( $attributes['imagePosition'] ?? '', array( 'left', 'right' ), 'right' );
$mount    = fandeco_enum( $attributes['mount'] ?? '', array( '', 'configurator' ), '' );
$eyebrow  = sanitize_text_field( $attributes['eyebrow'] ?? '' );
$title    = sanitize_text_field( $attributes['title'] ?? '' );
$desc     = sanitize_textarea_field( $attributes['description'] ?? '' );
$badge    = sanitize_text_field( $attributes['badge'] ?? '' );
$image    = fandeco_attachment_image(
	absint( $attributes['imageId'] ?? 0 ),
	'fandeco-wide',
	array( 'loading' => 'lazy', 'decoding' => 'async', 'sizes' => '(max-width: 767px) 100vw, 50vw', 'alt' => '' )
);
$btn_var = 'light' === $variant ? 'primary' : 'light';
$image_large = 'immersive' === $variant ? fandeco_attachment_image( absint( $attributes['imageId'] ?? 0 ), 'fandeco-hero', array( 'loading' => 'lazy', 'decoding' => 'async', 'sizes' => '100vw', 'alt' => '' ) ) : '';
?>
<section <?php echo fandeco_block_attrs( 'editorial-banner', $attributes, "fd-banner--{$variant} fd-banner--img-{$position}" ); // phpcs:ignore WordPress.Security.EscapeOutput ?>>
	<div class="fd-container">
		<div class="fd-banner__card">
			<div class="fd-banner__content">
				<?php if ( $badge ) : ?><p class="fd-badge" data-fd-reveal><?php echo esc_html( $badge ); ?></p><?php endif; ?>
				<?php if ( $eyebrow ) : ?><p class="fd-eyebrow" data-fd-reveal><?php echo esc_html( $eyebrow ); ?></p><?php endif; ?>
				<?php if ( $title ) : ?><h2 class="fd-banner__title" data-fd-reveal><?php echo esc_html( $title ); ?></h2><?php endif; ?>
				<?php if ( $desc ) : ?><p class="fd-banner__desc" data-fd-reveal><?php echo esc_html( $desc ); ?></p><?php endif; ?>
				<?php $btn = fandeco_button( (string) ( $attributes['buttonText'] ?? '' ), (string) ( $attributes['buttonUrl'] ?? '' ), $btn_var ); ?>
				<?php if ( $btn ) : ?><div data-fd-reveal><?php echo $btn; // phpcs:ignore WordPress.Security.EscapeOutput ?></div><?php endif; ?>
			</div>
			<div class="fd-banner__media" data-fd-reveal="media">
				<?php if ( $image_large ) : ?>
					<?php echo $image_large; // phpcs:ignore WordPress.Security.EscapeOutput ?>
				<?php elseif ( $image ) : ?>
					<?php echo $image; // phpcs:ignore WordPress.Security.EscapeOutput ?>
				<?php else : ?>
					<div class="fd-media-placeholder" aria-hidden="true"></div>
				<?php endif; ?>
				<?php if ( 'configurator' === $mount ) : ?>
					<div class="fd-banner__mount" data-fd-mount="configurator">
						<?php if ( ! $image ) : ?><span class="fd-banner__soon"><?php esc_html_e( 'Próximamente', 'fan-deco-studio' ); ?></span><?php endif; ?>
					</div>
				<?php endif; ?>
			</div>
		</div>
	</div>
</section>
