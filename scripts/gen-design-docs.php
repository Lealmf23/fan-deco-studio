<?php
/** Genera docs/PERSONALIZAR.md desde el esquema de inc/design.php (así la guía nunca se desactualiza). Uso: php scripts/gen-design-docs.php */
define( 'ABSPATH', __DIR__ . '/' ); define( 'FANDECO_URI', '/t' ); define( 'FANDECO_VERSION', 't' );
function __( $t ) { return $t; } function add_action() {} function add_filter() {} function get_theme_mod( $k, $d = false ) { return $d; }
function sanitize_hex_color( $c ) { return preg_match( '/^#([0-9a-fA-F]{3}|[0-9a-fA-F]{6})$/', (string) $c ) ? $c : ''; }
require dirname( __DIR__ ) . '/inc/design.php';

$md  = "# Guía de personalización (Apariencia > Personalizar > Fan Deco Studio)\n\n";
$md .= "Generado automáticamente desde `inc/design.php`. Todo lo que cambies se ve **al instante** en la vista previa y se guarda al tocar **Publicar**. Cada ajuste tiene un botón para volver a su valor original, y mientras no cambies nada el sitio se ve igual que el diseño base.\n\n";
$md .= "Además de estas secciones, **Marca y header** (logo, barra superior), **Contacto**, **Redes sociales** y **Footer** tienen sus propios campos, y **CSS adicional** permite agregar código propio.\n\n";
foreach ( fandeco_design_schema() as $section ) {
	$md .= '## ' . $section['title'] . "\n\n" . $section['desc'] . "\n\n| Ajuste | Qué puede ir | Por defecto |\n|---|---|---|\n";
	foreach ( $section['fields'] as $f ) {
		switch ( $f['type'] ) {
			case 'color':  $what = 'Un color'; $def = '' === $f['default'] ? 'del tema' : '`' . strtoupper( $f['default'] ) . '`'; break;
			case 'range':  $what = $f['min'] . ' a ' . $f['max']; $def = (string) $f['default']; break;
			case 'select': $what = implode( ', ', array_map( static fn( $l ) => str_replace( ' (por defecto)', '', $l ), $f['choices'] ) ); $def = str_replace( ' (por defecto)', '', $f['choices'][ $f['default'] ] ); break;
			default:       $what = 'Sí / No'; $def = $f['default'] ? 'Sí' : 'No';
		}
		$md .= '| ' . $f['label'] . ' | ' . $what . ' | ' . $def . " |\n";
	}
	$md .= "\n";
}
$md .= "## Cómo funciona por dentro\nCada ajuste cambia una variable CSS (por ejemplo `--color-fd-wine`, `--fd-title-scale`). El tema lee esas variables con un valor de reserva, y solo se imprime en la página lo que cambiaste (`<style id=\"fd-design\">`). Los tamaños de móvil se aplican en pantallas de hasta 767 px.\n";
file_put_contents( dirname( __DIR__ ) . '/docs/PERSONALIZAR.md', $md );
echo "docs/PERSONALIZAR.md generado\n";
