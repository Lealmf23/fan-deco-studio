<?php
/** Pruebas de "Ficha de producto": parseo puro + render + pestaña + guardado (stubs). */
define( 'FANDECO_CORE_TEST', true );
define( 'ABSPATH', __DIR__ . '/' );
$GLOBALS['opts'] = array(); $GLOBALS['meta'] = array(); $GLOBALS['saved'] = array(); $GLOBALS['can'] = true; $GLOBALS['nonce'] = true;
function add_action() {} function add_filter() {}
function get_option( $k, $d = false ) { return $GLOBALS['opts'][ $k ] ?? $d; }
function get_post_meta( $id, $k, $s = true ) { return $GLOBALS['meta'][ $id ][ $k ] ?? ''; }
function update_post_meta( $id, $k, $v ) { $GLOBALS['saved'][ $id ] = $v; }
function delete_post_meta( $id, $k ) { $GLOBALS['saved'][ $id ] = 'DELETED'; }
function esc_html( $s ) { return htmlspecialchars( (string) $s, ENT_QUOTES ); }
function esc_attr( $s ) { return htmlspecialchars( (string) $s, ENT_QUOTES ); }
function esc_url( $s ) { return preg_match( '#^https?://#', (string) $s ) ? htmlspecialchars( $s, ENT_QUOTES ) : ''; }
function esc_url_raw( $s ) { return esc_url( $s ); }
function esc_html__( $s ) { return $s; } function __( $s ) { return $s; }
function wp_strip_all_tags( $s ) { return strip_tags( (string) $s ); }
function sanitize_text_field( $s ) { return trim( strip_tags( (string) $s ) ); }
function wp_unslash( $v ) { return $v; }
function wp_verify_nonce() { return $GLOBALS['nonce']; }
function current_user_can() { return $GLOBALS['can']; }
function wc_format_dimensions( $d ) { return implode( ' × ', $d ) . ' cm'; }
function wc_format_weight( $w ) { return str_replace( '.', ',', (string) $w ) . ' kg'; }
function wc_attribute_label( $n ) { return ucfirst( str_replace( 'pa_', '', $n ) ); }
function wc_get_product_terms( $id, $tax, $a ) { return array( 'Cálido' ); }
class WC_Product_Attribute { function __construct( public string $n, public bool $vis, public bool $var, public bool $tax = false, public array $opts = array() ) {} function get_visible() { return $this->vis; } function get_variation() { return $this->var; } function get_name() { return $this->n; } function is_taxonomy() { return $this->tax; } function get_options() { return $this->opts; } }
class WC_Product { public array $attrs = array(); public array $dim = array(); public string $weight = ''; function __construct( public int $id = 1 ) {} function get_id() { return $this->id; } function has_dimensions() { return (bool) $this->dim; } function get_dimensions( $f ) { return $this->dim; } function has_weight() { return '' !== $this->weight; } function get_weight() { return $this->weight; } function get_attributes() { return $this->attrs; } }
require __DIR__ . '/../../plugins/fan-deco-core/includes/class-pinfo-parse.php';
require __DIR__ . '/../../plugins/fan-deco-core/includes/class-product-info.php';

$fail = 0;
function t( string $n, $got, $want ): void { global $fail; $ok = $got === $want; echo ( $ok ? '  ok   ' : '  FAIL ' ) . $n . ( $ok ? '' : "\n         got:  " . var_export( $got, true ) . "\n         want: " . var_export( $want, true ) ) . "\n"; if ( ! $ok ) { ++$fail; } }
function out( callable $f ): string { ob_start(); $f(); return (string) ob_get_clean(); }

echo "Parseo\n";
t( 'líneas: recorta, descarta vacías y etiquetas HTML', FanDeco_Pinfo_Parse::lines( "  Luz <b>cálida</b>  \n\n\nCable 1,5 m" ), array( 'Luz cálida', 'Cable 1,5 m' ) );
t( 'líneas: respeta el límite', count( FanDeco_Pinfo_Parse::lines( str_repeat( "x\n", 20 ), 8 ) ), 8 );
t( 'specs: separador |', FanDeco_Pinfo_Parse::specs( 'Material | PLA' ), array( array( 'Material', 'PLA' ) ) );
t( 'specs: acepta ":" y valor con ":"', FanDeco_Pinfo_Parse::specs( 'Hora: 10:30' ), array( array( 'Hora', '10:30' ) ) );
t( 'specs: ignora líneas sin separador o con valor vacío', FanDeco_Pinfo_Parse::specs( "sin separador\nVacío |\n| sin etiqueta" ), array() );
t( 'confianza: icono válido', FanDeco_Pinfo_Parse::trust( 'shield | 6 meses' ), array( array( 'icon' => 'shield', 'text' => '6 meses' ) ) );
t( 'confianza: icono desconocido o ausente -> check', array_column( FanDeco_Pinfo_Parse::trust( "raro | A\nB" ), 'icon' ), array( 'check', 'check' ) );
t( 'confianza: máx. 6', count( FanDeco_Pinfo_Parse::trust( str_repeat( "check | x\n", 12 ) ) ), 6 );
$c = FanDeco_Pinfo_Parse::cards( "leaf | Material sustentable | PLA vegetal | green\nlock | Compra protegida | | hack\nleaf |  | texto sin título | wine" );
t( 'tarjetas: 2 válidas (la sin título se descarta)', count( $c ), 2 );
t( 'tarjetas: tono válido y tono inválido -> neutral', array( $c[0]['tone'], $c[1]['tone'] ), array( 'green', 'neutral' ) );

echo "Render\n";
$GLOBALS['opts']['fandeco_core_pinfo'] = array( 'trust' => "shield | 6 meses de garantía\nreturn | 30 días para devolverlo", 'cards' => 'leaf | Material sustentable | Bioplástico PLA | green', 'rating_value' => '5,0', 'rating_label' => 'en reseñas de Google', 'rating_url' => 'https://g.page/x' );
$GLOBALS['meta'][1][ FanDeco_Product_Info::META ] = array( 'features' => "Luz cálida\n<script>x</script>Cable incluido", 'production' => 'Se fabrica a pedido', 'specs' => 'Material | PLA', 'hide_trust' => 0, 'hide_cards' => 0 );
$GLOBALS['product'] = new WC_Product( 1 );
$f = out( array( 'FanDeco_Product_Info', 'render_features' ) );
t( 'características con tilde, saneadas', str_contains( $f, 'Luz cálida' ) && str_contains( $f, 'Cable incluido' ) && ! str_contains( $f, '<script>' ), true );
$tr = out( array( 'FanDeco_Product_Info', 'render_trust' ) );
t( 'confianza: garantía, devolución, producción y calificación', str_contains( $tr, '6 meses de garantía' ) && str_contains( $tr, '30 días para devolverlo' ) && str_contains( $tr, 'Se fabrica a pedido' ) && str_contains( $tr, '<strong>5,0</strong> en reseñas de Google' ), true );
t( 'calificación enlaza a la URL cargada', str_contains( $tr, 'href="https://g.page/x"' ), true );
t( 'tarjeta con tono', str_contains( out( array( 'FanDeco_Product_Info', 'render_cards' ) ), 'fd-pinfo-card--green' ), true );
$GLOBALS['meta'][1][ FanDeco_Product_Info::META ]['hide_trust'] = 1; $GLOBALS['meta'][1][ FanDeco_Product_Info::META ]['hide_cards'] = 1;
$tr2 = out( array( 'FanDeco_Product_Info', 'render_trust' ) );
t( 'ocultar globales: queda solo el tiempo de producción', str_contains( $tr2, 'Se fabrica a pedido' ) && ! str_contains( $tr2, 'garantía' ) && ! str_contains( $tr2, '5,0' ), true );
t( 'ocultar tarjetas por producto', out( array( 'FanDeco_Product_Info', 'render_cards' ) ), '' );
$GLOBALS['opts'] = array(); $GLOBALS['product'] = new WC_Product( 9 );
t( 'sin datos cargados no se muestra nada (nada precargado)', out( array( 'FanDeco_Product_Info', 'render_features' ) ) . out( array( 'FanDeco_Product_Info', 'render_trust' ) ) . out( array( 'FanDeco_Product_Info', 'render_cards' ) ), '' );

echo "Especificaciones\n";
$p = new WC_Product( 1 ); $p->dim = array( 17, 15, 15 ); $p->weight = '0.5';
$p->attrs = array( new WC_Product_Attribute( 'pa_luz', true, false, true ), new WC_Product_Attribute( 'pa_color', true, true, true ), new WC_Product_Attribute( 'oculto', false, false, false, array( 'x' ) ) );
$rows = FanDeco_Product_Info::spec_rows( $p );
t( 'medidas y peso nativos + atributo visible no-variación + fila propia (sin variación ni ocultos)', array_column( $rows, 0 ), array( 'Medidas', 'Peso', 'Luz', 'Material' ) );
t( 'formato de medidas y peso', array( $rows[0][1], $rows[1][1] ), array( '17 × 15 × 15 cm', '0,5 kg' ) );
$GLOBALS['product'] = $p;
$tabs = FanDeco_Product_Info::tabs( array( 'description' => array(), 'additional_information' => array( 'title' => 'x' ) ) );
t( 'agrega "Especificaciones" y quita "Información adicional" (sin duplicar)', array( isset( $tabs['fandeco_specs'] ), isset( $tabs['additional_information'] ) ), array( true, false ) );
$p2 = new WC_Product( 5 ); $GLOBALS['product'] = $p2;
t( 'sin datos: no toca las pestañas', array_keys( FanDeco_Product_Info::tabs( array( 'additional_information' => array() ) ) ), array( 'additional_information' ) );

echo "Guardado\n";
$_POST = array( 'fandeco_pinfo_nonce' => 'x', 'fdp' => array( 'features' => " A \n<b>B</b>", 'production' => '<i>3 días</i>', 'specs' => "Material | PLA\nmal", 'hide_trust' => '1' ) );
FanDeco_Product_Info::save_product( 7 ); $s = $GLOBALS['saved'][7] ?? null;
t( 'guarda saneado', array( $s['features'], $s['production'], $s['specs'], $s['hide_trust'], $s['hide_cards'] ), array( "A\nB", '3 días', 'Material | PLA', 1, 0 ) );
$_POST['fdp'] = array(); FanDeco_Product_Info::save_product( 8 ); t( 'todo vacío: borra el meta', $GLOBALS['saved'][8] ?? null, 'DELETED' );
$GLOBALS['nonce'] = false; FanDeco_Product_Info::save_product( 9 ); t( 'nonce inválido: no guarda', isset( $GLOBALS['saved'][9] ), false );
$GLOBALS['nonce'] = true; $GLOBALS['can'] = false; FanDeco_Product_Info::save_product( 10 ); t( 'sin permisos: no guarda', isset( $GLOBALS['saved'][10] ), false );
$san = FanDeco_Product_Info::sanitize( array( 'trust' => 'raro | X', 'cards' => 'lock | T | x | feo', 'rating_value' => '5,0<b>', 'rating_url' => 'javascript:alert(1)' ) );
t( 'global saneado: icono y tono inválidos, url peligrosa vacía', array( $san['trust'], $san['cards'], $san['rating_value'], $san['rating_url'] ), array( 'check | X', 'lock | T | x | neutral', '5,0', '' ) );

echo $fail ? "\n$fail fallaron\n" : "\nTodas las verificaciones pasaron\n";
exit( $fail ? 1 : 0 );
