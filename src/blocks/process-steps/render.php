<?php
/**
 * Render del bloque fandeco/process-steps.
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
	if ( '' === $title ) {
		continue;
	}
	$items[] = array(
		'icon'  => fandeco_enum( $item['icon'] ?? '', array_keys( $choices ), 'sparkles' ),
		'title' => $title,
		'text'  => sanitize_textarea_field( $item['text'] ?? '' ),
	);
}
if ( ! $items ) {
	return;
}
?>
<section <?php echo fandeco_block_attrs( 'process-steps', $attributes ); // phpcs:ignore WordPress.Security.EscapeOutput ?>>
	<div class="fd-container">
		<ol class="fd-steps">
			<?php foreach ( $items as $i => $item ) : ?>
				<li class="fd-step" data-fd-reveal>
					<span class="fd-step__num" aria-hidden="true"><?php echo esc_html( str_pad( (string) ( $i + 1 ), 2, '0', STR_PAD_LEFT ) ); ?></span>
					<span class="fd-step__icon"><?php echo fandeco_icon( $item['icon'] ); // phpcs:ignore WordPress.Security.EscapeOutput ?></span>
					<h3 class="fd-step__title"><?php echo esc_html( $item['title'] ); ?></h3>
					<?php if ( $item['text'] ) : ?><p class="fd-step__text"><?php echo esc_html( $item['text'] ); ?></p><?php endif; ?>
				</li>
			<?php endforeach; ?>
		</ol>
	</div>
</section>
