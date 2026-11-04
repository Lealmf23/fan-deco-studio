<?php
/**
 * Render del bloque fandeco/benefits.
 *
 * @var array $attributes
 * @package FanDecoStudio
 */

defined( 'ABSPATH' ) || exit;

$items   = array();
$choices = fandeco_icon_choices();
foreach ( array_slice( (array) ( $attributes['items'] ?? array() ), 0, 6 ) as $item ) {
	if ( ! is_array( $item ) ) {
		continue;
	}
	$title = sanitize_text_field( $item['title'] ?? '' );
	$text  = sanitize_text_field( $item['text'] ?? '' );
	if ( '' === $title && '' === $text ) {
		continue;
	}
	$items[] = array(
		'icon'  => fandeco_enum( $item['icon'] ?? '', array_keys( $choices ), 'sparkles' ),
		'title' => $title,
		'text'  => $text,
	);
}
if ( ! $items ) {
	return;
}
?>
<section <?php echo fandeco_block_attrs( 'benefits', $attributes, 'fd-benefits--' . fandeco_enum( $attributes['variant'] ?? '', array( 'cards', 'strip' ), 'cards' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?>>
	<div class="fd-container">
		<ul class="fd-benefits__list">
			<?php foreach ( $items as $item ) : ?>
				<li class="fd-benefit" data-fd-reveal>
					<span class="fd-benefit__icon"><?php echo fandeco_icon( $item['icon'] ); // phpcs:ignore WordPress.Security.EscapeOutput ?></span>
					<span class="fd-benefit__body">
						<?php if ( $item['title'] ) : ?><strong class="fd-benefit__title"><?php echo esc_html( $item['title'] ); ?></strong><?php endif; ?>
						<?php if ( $item['text'] ) : ?><span class="fd-benefit__text"><?php echo esc_html( $item['text'] ); ?></span><?php endif; ?>
					</span>
				</li>
			<?php endforeach; ?>
		</ul>
	</div>
</section>
