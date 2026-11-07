// Genera plugins/fan-deco-core/assets/core.css: los estilos de lo que el plugin agrega en la ficha (cuotas, precio con transferencia,
// garantías, tarjetas, características y especificaciones), extraídos del CSS del tema para que se vean igual con Blocksy.
import { readFileSync, writeFileSync } from 'node:fs';
import { makeFilter, tokensAsRoot } from './lib/css-extract.mjs';

const bases = [ 'fd-installments', 'fd-inst', 'fd-pay-badge', 'fd-pinfo', 'fd-transfer' ];
const want = new RegExp( `\\.(?:${ bases.join( '|' ) })(?![a-z0-9])` );
const deny = /\.fd-(?:header|footer|drawer|announcement|tabbar|buybar|qv|quickview|cartpanel)\b|\bhtml\b/;
const filter = makeFilter( { want, deny, strip: [], fontFamily: null } );

let css = '/* GENERADO por scripts/build-core-css.mjs a partir del CSS del tema Fan Deco Studio. No editar a mano.\n   Estilos de lo que Fan Deco Core agrega en la ficha de producto y en el checkout; sirven con cualquier tema (p. ej. Blocksy). */\n';
css += tokensAsRoot().replace( /--font-[a-z]+:[^;]+;/g, '' ).replace( /--(radius|shadow|ease|duration)[^;]*;/g, ( m ) => m ) + '\n';
for ( const f of [ 'shop.css', 'woocommerce.css', 'checkout.css', 'mobile.css' ] ) {
	const part = filter( `src/css/${ f }` );
	if ( part.trim() ) css += `\n/* ---- ${ f } ---- */\n${ part }\n`;
}
// Tipografía del tema Blocksy en las piezas: heredan la del tema salvo que se defina --font-sans.
css += '\n.fd-installments, .fd-pinfo-trust, .fd-pinfo-cards, .fd-pinfo-features, .fd-pinfo-specs { font-family: var(--font-sans, inherit); }\n';
writeFileSync( 'plugins/fan-deco-core/assets/core.css', css );
console.log( `core.css: ${ ( css.length / 1024 ).toFixed( 1 ) } kB` );
