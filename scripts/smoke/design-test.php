<?php
/** Pruebas del sistema de diseño personalizable (inc/design.php). */
define( 'ABSPATH', __DIR__ . '/' );
define( 'FANDECO_URI', '/t' );
define( 'FANDECO_VERSION', 't' );
function __( $t ) { return $t; }
function add_action() {}
function add_filter() {}
function get_theme_mod( $k, $d = false ) { return $GLOBALS['mods'][ $k ] ?? $d; }
function sanitize_hex_color( $c ) { return preg_match( '/^#([0-9a-fA-F]{3}|[0-9a-fA-F]{6})$/', (string) $c ) ? $c : ''; }
require dirname( __DIR__, 2 ) . '/inc/design.php';

$fail = 0;
function check( bool $ok, string $label ) { global $fail; echo ( $ok ? '  ok   ' : '  FAIL ' ) . $label . "\n"; if ( ! $ok ) { $fail++; } }

echo "Sistema de diseño\n";
$fields = fandeco_design_fields();
check( count( $fields ) >= 45, 'esquema: más de 45 ajustes (' . count( $fields ) . ')' );

$bad = array();
foreach ( $fields as $k => $f ) {
	$n = fandeco_design_normalize( $f, $f['default'] );
	if ( null === $n ) { $bad[] = "$k: el valor por defecto no es válido"; }
	if ( 'toggle' !== $f['type'] && empty( $f['vars'] ) ) { $bad[] = "$k: sin variable CSS"; }
	foreach ( $f['vars'] ?? array() as $v => $tpl ) {
		if ( ! str_starts_with( $v, '--' ) || ! str_contains( $tpl, '{v}' ) ) { $bad[] = "$k: variable o plantilla inválida"; }
	}
	if ( 'range' === $f['type'] && ( $f['default'] < $f['min'] || $f['default'] > $f['max'] ) ) { $bad[] = "$k: valor por defecto fuera de rango"; }
	if ( 'select' === $f['type'] && ! array_key_exists( $f['default'], $f['choices'] ) ) { $bad[] = "$k: valor por defecto fuera de la lista"; }
	if ( 'select' === $f['type'] && isset( $f['map'] ) && array_diff( array_map( 'strval', array_keys( $f['choices'] ) ), array_map( 'strval', array_keys( $f['map'] ) ) ) ) { $bad[] = "$k: opciones sin valor en el mapa"; }
	if ( 'toggle' === $f['type'] && empty( $f['class_off'] ) ) { $bad[] = "$k: sin clase de apagado"; }
}
check( ! $bad, 'esquema: todos los ajustes son coherentes' . ( $bad ? ' - ' . implode( '; ', $bad ) : '' ) );

$GLOBALS['mods'] = array();
check( '' === fandeco_design_build_css() && array() === fandeco_design_body_classes(), 'sin cambios: el CSS de salida está vacío (el sitio se ve igual)' );

$GLOBALS['mods'] = array( 'fandeco_d_c_accent' => '#aa3300', 'fandeco_d_t_scale' => '120', 'fandeco_d_t_scale_m' => '85', 'fandeco_d_f_size' => '110', 'fandeco_d_p_quickview' => '0', 'fandeco_d_b_radius' => 'square' );
$css = fandeco_design_build_css();
check( str_contains( $css, '--color-fd-wine:#aa3300' ) && str_contains( $css, '--fd-title-scale:1.2' ) && str_contains( $css, '--fd-font-size:110%' ) && str_contains( $css, '--fd-btn-radius:0' ), 'con cambios: salen solo las variables modificadas' );
check( str_contains( $css, '@media (max-width:767px){:root{--fd-title-scale:0.85}}' ), 'con cambios: el tamaño de títulos de móvil va en su media query' );
check( ! str_contains( $css, '--color-fd-ink' ) && ! str_contains( $css, '--font-sans' ), 'con cambios: lo que no se tocó no se emite' );
check( array( 'fd-hide-quickview' ) === fandeco_design_body_classes(), 'interruptor apagado: agrega su clase al body' );

$GLOBALS['mods'] = array( 'fandeco_d_c_accent' => '#fff;}body{display:none', 'fandeco_d_f_body' => 'x}</style><script>', 'fandeco_d_f_size' => '999', 'fandeco_d_l_gutter' => '-5' );
$css = fandeco_design_build_css();
check( ! preg_match( '/[<>]|script|display:none/', $css ), 'entradas hostiles: nada de HTML ni CSS inyectado' );
check( str_contains( $css, '--fd-font-size:125%' ) && ! str_contains( $css, '--spacing-fd-gutter' ), 'entradas fuera de rango: se acotan al máximo y el margen automático no se emite' );
check( 1 === preg_match( '/^(:root\{[^{}<>]*\})?(@media \(max-width:767px\)\{:root\{[^{}<>]*\}\})?$/', fandeco_design_build_css( array( 'c_accent' => '#112233', 'h_height' => 100, 'f_body' => 'georgia' ) ) ), 'formato: bloque :root y media query bien formados' );

$js = fandeco_design_js_schema();
check( is_string( $js['t_weight']['choices'][0] ) && count( $js ) === count( $fields ), 'esquema JS: claves numéricas como texto y todos los ajustes' );
echo $fail ? "\n$fail verificación(es) fallaron\n" : "\nTodas las verificaciones pasaron\n";
exit( $fail ? 1 : 0 );
