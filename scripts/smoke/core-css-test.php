<?php
/** Prueba de los estilos propios de Fan Deco Core (assets/core.css), generados desde el tema, para usarlo con Blocksy. */
$fail = 0;
function check( bool $ok, string $label ) { global $fail; echo ( $ok ? '  ok   ' : '  FAIL ' ) . $label . "\n"; if ( ! $ok ) { $fail++; } }
echo "Fan Deco Core: estilos propios\n";
$root = dirname( __DIR__, 2 ) . '/plugins/fan-deco-core';
$css  = (string) @file_get_contents( $root . '/assets/core.css' );
check( '' !== $css && strlen( $css ) < 20000, 'core.css existe y es liviano (' . round( strlen( $css ) / 1024, 1 ) . ' kB)' );

// Toda clase fd-* que imprime el plugin y que necesita estilo debe estar en core.css (o en lights.css).
$php = '';
foreach ( glob( $root . '/includes/*.php' ) as $f ) { $php .= file_get_contents( $f ); }
preg_match_all( '/fd-(?:installments|inst|pay-badge|pinfo|transfer)[a-z0-9_-]*/', $php, $m );
$classes = array_unique( $m[0] );
$theme = '';
foreach ( glob( dirname( __DIR__, 2 ) . '/src/css/*.css' ) as $f ) { $theme .= file_get_contents( $f ); }
$styled  = 0; $missing = array();
foreach ( $classes as $c ) {
	// Si el tema le da estilo a la clase, core.css también tiene que dárselo.
	if ( str_contains( $css, '.' . $c ) ) { $styled++; } elseif ( str_contains( $theme, '.' . $c ) ) { $missing[] = $c; }
}
check( $styled >= 8, "las piezas del plugin tienen estilo ($styled clases)" );
check( ! $missing, 'ninguna clase del plugin queda sin estilo' . ( $missing ? ' - ' . implode( ', ', $missing ) : '' ) );
check( str_contains( $css, '--color-fd-wine' ) && str_contains( $css, ':root' ), 'trae sus variables de color de reserva (no depende de otro plugin ni del tema)' );
check( ! str_contains( $css, '.fd-header' ) && ! str_contains( $css, '.fd-footer' ), 'no toca el header ni el footer del tema' );

$main = (string) file_get_contents( $root . '/fan-deco-core.php' );
check( str_contains( $main, "assets/core.css" ) && str_contains( $main, "'fan-deco-studio' === get_template()" ), 'se carga en ficha y checkout, salvo con el tema Fan Deco Studio (que ya lo trae)' );
$lights = (string) file_get_contents( $root . '/assets/lights.css' );
check( str_contains( $lights, 'ct-media-container' ) && str_contains( $lights, 'data-thumbs' ), 'interruptor de luz: reglas para la galería de Blocksy' );

// Cotizador de Andreani: solo CSS, solo con Andreani activo, no renombra su HTML y conserva los estados (abierto, cargando, resultados, error).
$sk = (string) @file_get_contents( $root . '/assets/andreani-skin.css' );
check( '' !== $sk && strlen( $sk ) < 12000, 'andreani-skin.css existe y es liviano (' . round( strlen( $sk ) / 1024, 1 ) . ' kB)' );
foreach ( array( 'andreani-calc-bubble', 'andreani-calc-content', 'andreani-calc-widget--expanded', 'andreani-calc-rate', 'andreani-calc-button' ) as $cls ) {
	check( str_contains( $sk, $cls ), "andreani-skin.css cubre .$cls" );
}
check( str_contains( $sk, '--andreani-calc-primary: var(--fd-a-accent)' ) && ! str_contains( $sk, '#d71920' ), 'recolorea a la paleta del sitio (sin el rojo de Andreani)' );
check( str_contains( $sk, ':not(.andreani-calc-widget--floating)' ), 'el modo flotante no se rediseña, solo se recolorea' );
check( str_contains( $main, 'andreani-skin.css' ) && str_contains( $main, "class_exists( 'Andreani_Cotizador_Widget' )" ) && str_contains( $main, 'fandeco_core_andreani_skin' ), 'se carga solo en la ficha, solo con Andreani activo y se puede apagar con un filtro' );
echo $fail ? "\n$fail verificación(es) fallaron\n" : "\nTodas las verificaciones pasaron\n";
exit( $fail ? 1 : 0 );
