<?php
/**
 * Render del bloque fandeco/marquee: franja de mensajes que se desplaza (solo CSS).
 *
 * @var array $attributes
 * @package FanDecoStudio
 */

defined( 'ABSPATH' ) || exit;

$items = array_values( array_filter( array_map( 'sanitize_text_field', preg_split( '/\R/', (string) ( $attributes['items'] ?? '' ) ) ?: array() ) ) );
$items = array_slice( $items, 0, 12 );
if ( ! $items ) {
	return;
}
$speed = max( 10, min( 90, (int) ( $attributes['speed'] ?? 30 ) ) );
// Alto (espacio de arriba y de abajo de las letras) y tamaño de letra en px; 0 = automático.
$pad   = max( 0, min( 80, absint( $attributes['height'] ?? 0 ) ) );
$fsz   = max( 0, min( 40, absint( $attributes['textSize'] ?? 0 ) ) );
$mstyle = '--fd-marquee-speed:' . (int) $speed . 's;' . ( $pad ? '--fd-marquee-py:' . $pad . 'px;' : '' ) . ( $fsz ? '--fd-marquee-fs:' . $fsz . 'px;' : '' );
// El grupo se repite hasta cubrir pantallas anchas; la copia exacta (aria-hidden) hace el bucle sin saltos.
$group = '';
for ( $i = 0; $i < 2; $i++ ) {
	$dup = $i > 0 ? ' class="is-dup"' : ''; // la repetición solo sirve para llenar el ancho: se oculta con "reducir movimiento"
	foreach ( $items as $item ) {
		$group .= '<span' . $dup . '>' . esc_html( $item ) . '</span><i' . $dup . ' aria-hidden="true">✦</i>';
	}
}
?>
<section <?php echo fandeco_block_attrs( 'marquee', $attributes, '', $mstyle ); // phpcs:ignore WordPress.Security.EscapeOutput ?> aria-label="<?php echo esc_attr( implode( ', ', $items ) ); ?>" role="group">
	<div class="fd-marquee__track" aria-hidden="true">
		<div class="fd-marquee__group"><?php echo $group; // phpcs:ignore WordPress.Security.EscapeOutput -- construido con esc_html. ?></div>
		<div class="fd-marquee__group"><?php echo $group; // phpcs:ignore WordPress.Security.EscapeOutput -- construido con esc_html. ?></div>
	</div>
</section>
