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
$variant = fandeco_enum( $attributes['variant'] ?? '', array( 'columns', 'index' ), 'columns' );
$p_title = sanitize_text_field( $attributes['title'] ?? '' );
$p_desc  = sanitize_textarea_field( $attributes['description'] ?? '' );
?>
<section <?php echo fandeco_block_attrs( 'process-steps', $attributes, 'fd-process--' . $variant ); // phpcs:ignore WordPress.Security.EscapeOutput ?>>
	<div class="fd-container<?php echo 'index' === $variant ? ' fd-process__split' : ''; ?>">
		<?php if ( 'index' === $variant && ( $p_title || $p_desc ) ) : ?>
			<div class="fd-process__side">
				<?php if ( $p_title ) : ?><h2 class="fd-process__title" data-fd-reveal><?php echo fandeco_title_html( $p_title ); // phpcs:ignore WordPress.Security.EscapeOutput -- escapado en la función. ?></h2><?php endif; ?>
				<?php if ( $p_desc ) : ?><p class="fd-process__desc" data-fd-reveal><?php echo esc_html( $p_desc ); ?></p><?php endif; ?>
			</div>
		<?php endif; ?>
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
