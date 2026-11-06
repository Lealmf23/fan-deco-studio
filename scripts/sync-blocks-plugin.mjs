// Genera plugins/fan-deco-blocks/includes/theme-shared.php copiando del tema las funciones de apoyo que usan los bloques
// (una sola fuente de verdad). Cada función se envuelve en function_exists por seguridad.
import { readFileSync, writeFileSync, mkdirSync, cpSync, rmSync, existsSync } from 'node:fs';

const SOURCES = {
	'inc/helpers.php': [ 'fandeco_has_woocommerce', 'fandeco_enum', 'fandeco_icon_choices', 'fandeco_icon', 'fandeco_attachment_image', 'fandeco_picture', 'fandeco_button', 'fandeco_block_attrs', 'fandeco_title_html' ],
	'inc/woocommerce.php': [ 'fandeco_product_badges', 'fandeco_loop_add_to_cart_html' ],
	'inc/swatches.php': [ 'fandeco_find_hex', 'fandeco_color_names', 'fandeco_swatch_color', 'fandeco_swatch_type', 'fandeco_variation_swatch_data' ],
};

function extract( src, name ) {
	const lines = src.split( '\n' );
	const start = lines.findIndex( ( l ) => l.startsWith( `function ${ name }(` ) );
	if ( start < 0 ) throw new Error( `No encontré ${ name }` );
	let end = start;
	while ( end < lines.length && lines[ end ] !== '}' ) end++;
	if ( end >= lines.length ) throw new Error( `No encontré el cierre de ${ name }` );
	// Comentario de documentación inmediato anterior, si hay.
	let from = start;
	if ( lines[ start - 1 ]?.trim().endsWith( '*/' ) ) {
		from = start - 1;
		while ( from > 0 && ! lines[ from ].trim().startsWith( '/*' ) ) from--;
	}
	return lines.slice( from, end + 1 ).join( '\n' );
}

let out = `<?php\n/**\n * GENERADO por scripts/sync-blocks-plugin.mjs a partir del tema Fan Deco Studio. No editar a mano.\n * Funciones de apoyo de los bloques, definidas solo si no existen (el tema Fan Deco Studio ya las trae).\n *\n * @package FanDecoBlocks\n */\n\ndefined( 'ABSPATH' ) || exit;\n`;
for ( const [ file, names ] of Object.entries( SOURCES ) ) {
	const src = readFileSync( file, 'utf8' );
	for ( const n of names ) {
		out += `\nif ( ! function_exists( '${ n }' ) ) {\n${ extract( src, n ).split( '\n' ).map( ( l ) => ( l ? '\t' + l : l ) ).join( '\n' ) }\n}\n`;
	}
}
mkdirSync( 'plugins/fan-deco-blocks/includes', { recursive: true } );
writeFileSync( 'plugins/fan-deco-blocks/includes/theme-shared.php', out );

// Bloques compilados y tarjeta de producto
if ( existsSync( 'build/blocks' ) ) {
	rmSync( 'plugins/fan-deco-blocks/build', { recursive: true, force: true } );
	cpSync( 'build/blocks', 'plugins/fan-deco-blocks/build/blocks', { recursive: true } );
}
mkdirSync( 'plugins/fan-deco-blocks/template-parts', { recursive: true } );
cpSync( 'template-parts/components/product-card.php', 'plugins/fan-deco-blocks/template-parts/product-card.php' );
cpSync( 'patterns/home.php', 'plugins/fan-deco-blocks/patterns/home.php' );
console.log( 'plugins/fan-deco-blocks: funciones, bloques, tarjeta y patrón sincronizados' );
