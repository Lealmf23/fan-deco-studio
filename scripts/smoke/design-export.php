<?php
/** Exporta el esquema y varios casos (valores + CSS esperado) para la prueba de paridad con el JS. */
define( 'ABSPATH', __DIR__ . '/' );
define( 'FANDECO_URI', '/t' );
define( 'FANDECO_VERSION', 't' );
function __( $t ) { return $t; }
function add_action() {}
function add_filter() {}
function get_theme_mod( $k, $d = false ) { return $d; }
function sanitize_hex_color( $c ) { return preg_match( '/^#([0-9a-fA-F]{3}|[0-9a-fA-F]{6})$/', (string) $c ) ? $c : ''; }
require dirname( __DIR__, 2 ) . '/inc/design.php';

$cases = array(
	array( 'name' => 'valores por defecto (CSS vacío)', 'values' => array() ),
	array( 'name' => 'colores válidos e inválidos', 'values' => array( 'c_accent' => '#AA3300', 'c_bg' => '#fff', 'c_text' => 'rojo', 'c_btn_bg' => '#123456', 'c_head_bg' => '' ) ),
	array( 'name' => 'rangos con límites y decimales', 'values' => array( 'f_size' => '112', 't_scale' => '125', 't_scale_m' => '80', 't_lh' => '1.05', 't_ls' => '-0.035', 'l_container' => '99999', 'h_logo' => '1', 'f_line' => 'abc' ) ),
	array( 'name' => 'selects con y sin mapa, y valor no permitido', 'values' => array( 'f_body' => 'georgia', 'f_heading' => 'inexistente', 'l_radius' => 'square', 'b_radius' => 'soft', 'p_ratio' => '1-1', 't_weight' => '600', 'f_accent_st' => 'normal' ) ),
	array( 'name' => 'margen lateral en 0 (automático) y con valor', 'values' => array( 'l_gutter' => '0' ) ),
	array( 'name' => 'margen lateral con valor y móvil', 'values' => array( 'l_gutter' => '24', 'h_logo_m' => '30', 'h_height_m' => '70', 'h_height' => '96' ) ),
	array( 'name' => 'interruptores apagados', 'values' => array( 'p_quickview' => '0', 'p_badges' => 0, 'p_dots' => '1' ) ),
	array( 'name' => 'inyección de CSS rechazada', 'values' => array( 'c_accent' => '#fff;}body{display:none', 'f_body' => 'x}</style><script>', 'f_size' => '100;color:red' ) ),
);
foreach ( $cases as &$c ) {
	$c['css']     = fandeco_design_build_css( $c['values'] );
	$c['classes'] = implode( ' ', fandeco_design_body_classes( $c['values'] ) );
}
echo json_encode( array( 'schema' => fandeco_design_js_schema(), 'cases' => $cases ), JSON_UNESCAPED_UNICODE );
