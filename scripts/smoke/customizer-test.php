<?php
/**
 * Registra el Personalizador con un WP_Customize_Manager simulado y verifica que no haya errores de tipos
 * (en PHP 8 un validador pasado como función a un parámetro "string" es un error fatal) y que todos los
 * validadores sean ejecutables.
 */
define( 'ABSPATH', __DIR__ . '/' );
define( 'FANDECO_URI', '/t' );
define( 'FANDECO_VERSION', 't' );
$GLOBALS['cz'] = array( 'settings' => array(), 'controls' => array(), 'sections' => array(), 'cbs' => array() );
function add_action( $tag, $cb ) { if ( 'customize_register' === $tag ) { $GLOBALS['cz']['cbs'][] = $cb; } }
function add_filter() {}
function sanitize_hex_color( $c ) { return preg_match( '/^#([0-9a-fA-F]{3}|[0-9a-fA-F]{6})$/', (string) $c ) ? $c : ''; }
class WP_Customize_Color_Control { public function __construct( $m, $id, $a = array() ) { $GLOBALS['cz']['controls'][] = $id; } }
function __( $t ) { return $t; }
function esc_html__( $t ) { return $t; }
class WP_Customize_Media_Control { public function __construct( $m, $id, $a = array() ) { $GLOBALS['cz']['controls'][] = $id; } }
class WP_Customize_Manager {
	public function add_panel( $id, $a = array() ) {}
	public function add_section( $id, $a = array() ) { $GLOBALS['cz']['sections'][ $id ] = $a; }
	public function add_setting( $id, $a = array() ) { $GLOBALS['cz']['settings'][ $id ] = $a; }
	public function add_control( $id, $a = array() ) { $GLOBALS['cz']['controls'][] = $id; }
}
function sanitize_text_field( $v ) { return trim( strip_tags( (string) $v ) ); }
function sanitize_textarea_field( $v ) { return trim( strip_tags( (string) $v ) ); }
function sanitize_email( $v ) { return (string) $v; }
function esc_url_raw( $v ) { return (string) $v; }
function absint( $v ) { return abs( (int) $v ); }
function rest_sanitize_boolean( $v ) { return (bool) $v; }

$fail = 0;
function check( bool $ok, string $label ) { global $fail; echo ( $ok ? '  ok   ' : '  FAIL ' ) . $label . "\n"; if ( ! $ok ) { $fail++; } }

echo "Personalizador\n";
require dirname( __DIR__, 2 ) . '/inc/customizer.php';
require dirname( __DIR__, 2 ) . '/inc/design.php';
check( count( $GLOBALS['cz']['cbs'] ) >= 2, 'registra los ganchos customize_register (marca/contacto y diseño)' );
try {
	$mgr = new WP_Customize_Manager();
	foreach ( $GLOBALS['cz']['cbs'] as $cb ) { $cb( $mgr ); }
	check( true, 'el registro completo se ejecuta sin errores de tipos' );
} catch ( Throwable $e ) {
	check( false, 'el registro falló: ' . $e->getMessage() );
}
$bad = array();
foreach ( $GLOBALS['cz']['settings'] as $id => $a ) {
	if ( ! isset( $a['sanitize_callback'] ) || ! is_callable( $a['sanitize_callback'] ) ) { $bad[] = $id; }
}
check( count( $GLOBALS['cz']['settings'] ) > 20 && ! $bad, 'todos los ajustes tienen validador ejecutable' . ( $bad ? ' (fallan: ' . implode( ', ', $bad ) . ')' : '' ) );
check( 51 <= count( array_filter( array_keys( $GLOBALS['cz']['settings'] ), static fn( $k ) => str_starts_with( $k, 'fandeco_d_' ) ) ), 'diseño: un ajuste por campo del esquema' );
check( 5 === count( array_filter( array_keys( $GLOBALS['cz']['sections'] ), static fn( $k ) => str_starts_with( $k, 'fandeco_design_' ) ) ), 'diseño: 5 secciones (colores, tipografía, diseño, header, tarjetas)' );
$post = array_filter( $GLOBALS['cz']['settings'], static fn( $a ) => ( $a['transport'] ?? '' ) !== 'postMessage' );
check( count( array_filter( array_keys( $post ), static fn( $k ) => str_starts_with( $k, 'fandeco_d_' ) ) ) === 0, 'diseño: todos con vista previa en vivo (postMessage)' );
check( '#112233' === ( $GLOBALS['cz']['settings']['fandeco_d_c_accent']['sanitize_callback'] )( '#112233' ) && '#626545' === ( $GLOBALS['cz']['settings']['fandeco_d_c_accent']['sanitize_callback'] )( 'rojo;}' ), 'diseño: el validador de color rechaza lo inválido y vuelve al original' );
check( '5491155551234' === ( $GLOBALS['cz']['settings']['fandeco_whatsapp']['sanitize_callback'] )( '+54 9 11 5555-1234' ), 'WhatsApp: el validador deja solo dígitos' );
echo $fail ? "\n$fail verificación(es) fallaron\n" : "\nTodas las verificaciones pasaron\n";
exit( $fail ? 1 : 0 );
