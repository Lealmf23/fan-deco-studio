<?php
/** Pruebas de la lógica pura del plugin Fan Deco Core (sin WordPress). */
define( 'FANDECO_CORE_TEST', true );
require __DIR__ . '/../../plugins/fan-deco-core/includes/class-text.php';
require __DIR__ . '/../../plugins/fan-deco-core/includes/class-redirect-rules.php';

$fail = 0;
function t( string $name, $got, $want ): void {
	global $fail;
	$ok = $got === $want;
	echo ( $ok ? "  ok   " : "  FAIL " ) . $name . ( $ok ? '' : "\n         got:  " . var_export( $got, true ) . "\n         want: " . var_export( $want, true ) ) . "\n";
	if ( ! $ok ) { ++$fail; }
}

echo "Texto\n";
$v = array( 'title' => 'Lámpara Luna', 'sitename' => 'Fan Deco', 'tagline' => 'Impresión 3D', 'sep' => '|', 'page' => '' );
t( 'plantilla estándar', FanDeco_Text::replace_vars( '%title% %sep% %sitename%', $v ), 'Lámpara Luna | Fan Deco' );
t( 'variable vacía no deja separadores colgando', FanDeco_Text::replace_vars( '%title% %sep% %page% %sep% %sitename%', $v ), 'Lámpara Luna | Fan Deco' );
t( 'variable desconocida se elimina', FanDeco_Text::replace_vars( '%title% %nada%', $v ), 'Lámpara Luna' );
t( 'separador sobrante al borde', FanDeco_Text::replace_vars( '%page% %sep% %title%', $v ), 'Lámpara Luna' );
t( 'truncate corto intacto', FanDeco_Text::truncate( 'Hola <b>mundo</b>', 160 ), 'Hola mundo' );
$long = str_repeat( 'palabra ', 40 );
$tr = FanDeco_Text::truncate( $long, 160 );
t( 'truncate ≤ 160 y termina en …', mb_strlen( $tr ) <= 160 && str_ends_with( $tr, '…' ), true );
t( 'truncate respeta acentos/UTF-8', mb_check_encoding( FanDeco_Text::truncate( str_repeat( 'ñandú ', 60 ), 100 ), 'UTF-8' ), true );
t( 'truncate decodifica entidades', FanDeco_Text::truncate( 'Mesa &amp; silla', 160 ), 'Mesa & silla' );

echo "AIOSEO -> Fan Deco\n";
t( 'plantilla por defecto', FanDeco_Text::from_aioseo( '#post_title #separator_sa #site_title' ), array( '%title% %sep% %sitename%', array() ) );
t( 'tagline y término', FanDeco_Text::from_aioseo( '#taxonomy_title - #tagline' ), array( '%term% - %tagline%', array() ) );
t( 'etiqueta desconocida descartada y reportada', FanDeco_Text::from_aioseo( '#post_title #custom_field-precio' ), array( '%title%', array( '#custom_field-precio' ) ) );
t( 'separator_sa no se rompe por prefijo', FanDeco_Text::from_aioseo( 'A #separator_sa B' )[0], 'A %sep% B' );

echo "Redirecciones\n";
t( 'normaliza barra final, query y mayúsculas', FanDeco_Redirect_Rules::normalize_path( '/Producto/Lampara/?utm=1' ), '/producto/lampara' );
t( 'raíz', FanDeco_Redirect_Rules::normalize_path( '/' ), '/' );
$p = FanDeco_Redirect_Rules::parse( "# comentario\n/viejo -> /nuevo\n/a/ => /b/ 302\n/x -> /x\nmal formato\nsin-barra -> /y" );
t( 'parsea 2 reglas válidas', count( $p['rules'] ), 2 );
t( 'código por defecto 301', $p['rules'][0]['code'], 301 );
t( 'código explícito 302', $p['rules'][1]['code'], 302 );
t( '3 errores reportados (bucle, formato, origen)', count( $p['errors'] ), 3 );
t( 'match ignora mayúsculas/barra/query', FanDeco_Redirect_Rules::match( $p['rules'], '/Viejo/?a=1' )['to'] ?? null, '/nuevo' );
t( 'sin match', FanDeco_Redirect_Rules::match( $p['rules'], '/otra' ), null );
t( 'ida y vuelta texto', FanDeco_Redirect_Rules::parse( FanDeco_Redirect_Rules::to_text( $p['rules'] ) )['rules'], $p['rules'] );

echo $fail ? "\n$fail fallaron\n" : "\nTodas las verificaciones pasaron\n";
exit( $fail ? 1 : 0 );
