<?php
/** Pruebas del interruptor de luz (marcado, datos de variación y guardado) con stubs. */
define( 'ABSPATH', __DIR__ . '/' );
define( 'FANDECO_CORE_URL', '/p/' );
define( 'FANDECO_CORE_VERSION', 't' );
$GLOBALS['meta'] = array(); $GLOBALS['saved'] = array(); $GLOBALS['can'] = true; $GLOBALS['nonce_ok'] = true;
function add_action() {} function add_filter() {}
function get_post_meta( $id, $k, $single = true ) { return $GLOBALS['meta'][ $id ][ $k ] ?? ''; }
function update_post_meta( $id, $k, $v ) { $GLOBALS['saved'][ $id ][ $k ] = $v; }
function delete_post_meta( $id, $k ) { $GLOBALS['saved'][ $id ][ $k ] = null; }
function wp_attachment_is_image( $id ) { return $id > 0 && $id < 100; }
function wp_get_attachment_image_url( $id, $size ) { return "/img/$id-$size.jpg"; }
function wp_get_attachment_image_srcset( $id, $size ) { return "/img/$id-s.jpg 600w"; }
function esc_url( $u ) { return htmlspecialchars( (string) $u, ENT_QUOTES ); }
function esc_attr( $s ) { return htmlspecialchars( (string) $s, ENT_QUOTES ); }
function esc_attr__( $s ) { return htmlspecialchars( $s, ENT_QUOTES ); }
function esc_html__( $s ) { return htmlspecialchars( $s, ENT_QUOTES ); }
function absint( $n ) { return abs( (int) $n ); }
function wp_unslash( $v ) { return $v; }
function sanitize_text_field( $v ) { return trim( strip_tags( (string) $v ) ); }
function wp_verify_nonce() { return $GLOBALS['nonce_ok']; }
function current_user_can() { return $GLOBALS['can']; }
class WC_Product { function __construct( public int $id, public int $img = 5 ) {} function get_id() { return $this->id; } function get_image_id() { return $this->img; } }
class WC_Product_Variable extends WC_Product { public array $kids = array(); function get_children() { return $this->kids; } }
class WC_Product_Variation extends WC_Product {}
require __DIR__ . '/../../plugins/fan-deco-core/includes/class-lights.php';

$fail = 0;
function t( string $n, $got, $want ): void { global $fail; $ok = $got === $want; echo ( $ok ? '  ok   ' : '  FAIL ' ) . $n . ( $ok ? '' : "\n         got:  " . var_export( $got, true ) . "\n         want: " . var_export( $want, true ) ) . "\n"; if ( ! $ok ) { ++$fail; } }
function deleted( int $id ): bool { return array_key_exists( '_fandeco_lit_image', $GLOBALS['saved'][ $id ] ?? array() ) && null === $GLOBALS['saved'][ $id ]['_fandeco_lit_image']; }
function out( callable $f ): string { ob_start(); $f(); return (string) ob_get_clean(); }
$html = '<div class="woocommerce-product-gallery__image"><a href="#"><img class="wp-post-image" src="/m.jpg"></a></div>';

echo "Sin fotos encendidas\n";
$GLOBALS['product'] = new WC_Product( 1 );
t( 'la imagen no se modifica', FanDeco_Lights::main_image( $html, 5 ), $html );
t( 'no hay interruptor', out( array( 'FanDeco_Lights', 'switch_button' ) ), '' );

echo "Producto con foto encendida\n";
$GLOBALS['meta'][2]['_fandeco_lit_image'] = 7;
$GLOBALS['product'] = new WC_Product( 2 );
$r = FanDeco_Lights::main_image( $html, 5 );
t( 'agrega la foto encendida dentro del contenedor, antes del cierre', str_ends_with( $r, '</a><img class="fd-lit-img" alt="" aria-hidden="true" decoding="async" fetchpriority="low" data-fd-lit src="/img/7-woocommerce_single.jpg" srcset="/img/7-s.jpg 600w" sizes="(max-width: 767px) 100vw, 50vw"></div>' ), true );
t( 'la foto agregada no usa la clase wp-post-image (WooCommerce no la reemplaza)', substr_count( $r, 'wp-post-image' ), 1 );
t( 'otra imagen de la galería no se toca', FanDeco_Lights::main_image( $html, 6 ), $html );
t( 'no se duplica si se vuelve a llamar', FanDeco_Lights::main_image( $html, 5 ), $html );
$sw = out( array( 'FanDeco_Lights', 'switch_button' ) );
t( 'interruptor role=switch, apagado y visible', str_contains( $sw, 'role="switch"' ) && str_contains( $sw, 'aria-checked="false"' ) && ! str_contains( $sw, ' hidden' ), true );
t( 'guarda el id de la imagen principal', str_contains( $sw, 'data-main-id="5"' ), true );

echo "Solo variaciones con foto encendida\n";
$v = new WC_Product_Variable( 3 ); $v->kids = array( 31, 32 ); $GLOBALS['meta'][32]['_fandeco_lit_image'] = 8; $GLOBALS['product'] = $v;
$r = FanDeco_Lights::main_image( $html, 5 );
t( 'agrega un elemento oculto para completar luego', str_contains( $r, 'data-fd-lit hidden' ), true );
t( 'el interruptor comienza oculto', str_contains( out( array( 'FanDeco_Lights', 'switch_button' ) ), ' hidden>' ) || str_contains( out( array( 'FanDeco_Lights', 'switch_button' ) ), 'hidden><span' ), true );

echo "Datos de variación\n";
t( 'variación con foto: src y srcset', FanDeco_Lights::variation_data( array(), $v, new WC_Product_Variation( 32 ) )['fandeco_lit'], array( 'src' => '/img/8-woocommerce_single.jpg', 'srcset' => '/img/8-s.jpg 600w' ) );
t( 'variación sin foto: null', FanDeco_Lights::variation_data( array(), $v, new WC_Product_Variation( 31 ) )['fandeco_lit'], null );
$GLOBALS['meta'][33]['_fandeco_lit_image'] = 999;
t( 'foto inexistente se ignora', FanDeco_Lights::variation_data( array(), $v, new WC_Product_Variation( 33 ) )['fandeco_lit'], null );

echo "Guardado\n";
$_POST = array( 'fandeco_lit_nonce' => 'x', 'fandeco_lit_image' => '7' );
FanDeco_Lights::save_product( 10 ); t( 'guarda un id válido', $GLOBALS['saved'][10]['_fandeco_lit_image'] ?? 'nada', 7 );
$_POST['fandeco_lit_image'] = '999'; FanDeco_Lights::save_product( 11 ); t( 'id que no es imagen: borra', deleted( 11 ), true );
$_POST['fandeco_lit_image'] = ''; FanDeco_Lights::save_product( 12 ); t( 'vacío: borra', deleted( 12 ), true );
$GLOBALS['nonce_ok'] = false; $_POST['fandeco_lit_image'] = '7'; FanDeco_Lights::save_product( 13 ); t( 'nonce inválido: no guarda', isset( $GLOBALS['saved'][13] ), false );
$GLOBALS['nonce_ok'] = true; $GLOBALS['can'] = false; FanDeco_Lights::save_product( 14 ); t( 'sin permisos: no guarda', isset( $GLOBALS['saved'][14] ), false );
$GLOBALS['can'] = true; unset( $_POST['fandeco_lit_nonce'] ); FanDeco_Lights::save_product( 15 ); t( 'sin nonce: no guarda', isset( $GLOBALS['saved'][15] ), false );
$_POST = array( 'fandeco_lit_image_var' => array( 2 => '8', 3 => 'abc' ) );
FanDeco_Lights::save_variation( 40, 2 ); t( 'variación: guarda por índice', $GLOBALS['saved'][40]['_fandeco_lit_image'] ?? 'nada', 8 );
FanDeco_Lights::save_variation( 41, 3 ); t( 'variación: valor inválido borra', deleted( 41 ), true );
$GLOBALS['can'] = false; FanDeco_Lights::save_variation( 42, 2 ); t( 'variación sin permisos: no guarda', isset( $GLOBALS['saved'][42] ), false );

echo $fail ? "\n$fail fallaron\n" : "\nTodas las verificaciones pasaron\n";
exit( $fail ? 1 : 0 );
