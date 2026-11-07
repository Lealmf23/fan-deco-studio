<?php
/**
 * Render del bloque fandeco/section-heading.
 *
 * @var array $attributes
 * @package FanDecoStudio
 */

defined( 'ABSPATH' ) || exit;

$align   = fandeco_enum( $attributes['align'] ?? '', array( 'left', 'center' ), 'left' );
$level   = in_array( (int) ( $attributes['level'] ?? 2 ), array( 1, 3 ), true ) ? (int) $attributes['level'] : 2;
$eyebrow = sanitize_text_field( $attributes['eyebrow'] ?? '' );
$title   = sanitize_text_field( $attributes['title'] ?? '' );
$desc    = sanitize_textarea_field( $attributes['description'] ?? '' );
$link    = (string) ( $attributes['linkUrl'] ?? '' );
$link_tx = trim( (string) ( $attributes['linkText'] ?? '' ) );
if ( '' === $title ) {
	return;
}
// Tamaños y espaciados ajustables (números acotados; 0 = automático).
$t_size = max( 0, min( 96, absint( $attributes['titleSize'] ?? 0 ) ) );
$d_size = max( 0, min( 40, absint( $attributes['descSize'] ?? 0 ) ) );
$custom = ! empty( $attributes['customSpace'] );
$extra  = '';
$style  = '';
if ( $t_size ) {
	$extra .= ' fd-section-heading--t';
	$style .= '--fd-sh-title:' . $t_size . 'px;';
}
if ( $d_size ) {
	$extra .= ' fd-section-heading--d';
	$style .= '--fd-sh-desc:' . $d_size . 'px;';
}
if ( $custom ) {
	$extra .= ' fd-section-heading--space';
	$style .= '--fd-sh-top:' . max( 0, min( 240, absint( $attributes['spaceTop'] ?? 0 ) ) ) . 'px;--fd-sh-bottom:' . max( 0, min( 240, absint( $attributes['spaceBottom'] ?? 0 ) ) ) . 'px;';
}
?>
<div <?php echo fandeco_block_attrs( 'section-heading', $attributes, "fd-section-heading--{$align}{$extra}", $style ); // phpcs:ignore WordPress.Security.EscapeOutput ?>>
	<div class="fd-container fd-section-heading__inner">
		<div class="fd-section-heading__text">
			<?php if ( $eyebrow ) : ?><p class="fd-eyebrow" data-fd-reveal><?php echo esc_html( $eyebrow ); ?></p><?php endif; ?>
			<h<?php echo (int) $level; ?> class="fd-section-heading__title" data-fd-reveal><?php echo fandeco_title_html( $title ); // phpcs:ignore WordPress.Security.EscapeOutput -- escapado en la función. ?></h<?php echo (int) $level; ?>>
			<?php if ( $desc ) : ?><p class="fd-section-heading__desc" data-fd-reveal><?php echo esc_html( $desc ); ?></p><?php endif; ?>
		</div>
		<?php if ( $link && $link_tx ) : ?>
			<a class="fd-link-arrow" href="<?php echo esc_url( $link ); ?>" data-fd-reveal><?php echo esc_html( $link_tx ); ?> <?php echo fandeco_icon( 'arrow-right' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></a>
		<?php endif; ?>
	</div>
</div>
