// Genera src/css/blocks.generated.css: SOLO las reglas del CSS del tema que usan los bloques (y sus variables),
// para empaquetarlas en el plugin fan-deco-blocks sin pisar el estilo de otro tema (p. ej. Blocksy).
import { readFileSync, writeFileSync } from 'node:fs';
import { globSync } from 'node:fs';
import postcss from 'postcss';

// Clases que usan los bloques y la tarjeta de producto (se leen de los render.php, así no hay que mantener la lista).
const used = new Set();
for ( const f of [ ...globSync( 'src/blocks/*/render.php' ), 'template-parts/components/product-card.php' ] ) {
	for ( const m of readFileSync( f, 'utf8' ).matchAll( /fd-[a-z0-9]+(?:-[a-z0-9]+)*/g ) ) {
		used.add( m[ 0 ] );
	}
}
const bases = new Set( [ 'fd-section', 'fd-container', 'fd-eyebrow', 'fd-btn', 'fd-link-arrow', 'fd-icon', 'fd-notice', 'fd-media-placeholder', 'fd-product-grid', 'fd-carousel', 'fd-dot', 'fd-dots-more' ] );
for ( const c of used ) bases.add( c.replace( /(__|--).*$/, '' ) );
const WANT = new RegExp( `\\.(?:${ [ ...bases ].map( ( b ) => b.replace( /-/g, '\\-' ) ).sort( ( a, b ) => b.length - a.length ).join( '|' ) })(?![a-z0-9])` );
const DENY = /\.fd-(?:header|footer|drawer|announcement|tabbar|mobile-search|main|woo|quickview|qv|cartpanel|share|buybar|stepper|live|search)\b|\.woocommerce|\bbody\b|\bhtml\b|\.admin-bar|\.single-product|\.is-scrolled/;

const keepSelector = ( sel ) => {
	if ( sel.trim() === ':root' ) return true;
	return WANT.test( sel ) && ! DENY.test( sel );
};
const clean = ( sel ) => sel.replace( /\.fd-home\s+/g, '' ).replace( /\.home\s+/g, '' ); // las reglas "solo en la portada" valen en cualquier página

function filterRoot( root, fromFile ) {
	const out = postcss.root();
	root.each( ( node ) => {
		if ( node.type === 'rule' ) {
			const sels = node.selectors.filter( keepSelector );
			if ( sels.length ) {
				const r = node.clone();
				r.selectors = sels.map( clean );
				out.append( r );
			}
		} else if ( node.type === 'atrule' ) {
			if ( node.name === 'font-face' ) {
				if ( /DM Sans/.test( node.toString() ) ) out.append( node.clone() );
			} else if ( node.name === 'keyframes' ) {
				if ( /^fd-/.test( node.params ) ) out.append( node.clone() );
			} else if ( [ 'media', 'supports', 'layer' ].includes( node.name ) && node.nodes ) {
				const inner = filterRoot( node, fromFile );
				if ( inner.nodes.length ) {
					const a = node.clone( { nodes: [] } );
					inner.each( ( n ) => a.append( n.clone() ) );
					// @layer base { ... } -> sin capa: así las reglas valen igual en cualquier tema
					if ( node.name === 'layer' ) a.each( ( n ) => out.append( n.clone() ) );
					else out.append( a );
				}
			}
		}
	} );
	return out;
}

const tokens = readFileSync( 'src/css/tokens.css', 'utf8' ).replace( /@theme static\s*\{/, ':root {' );
const files = [ 'fonts.css', 'base.css', 'components.css', 'home.css', 'home2.css', 'shop.css', 'banners.css', 'marquee.css', 'identity.css', 'mobile.css' ];
let css = '/* GENERADO por scripts/build-blocks-css.mjs a partir del CSS del tema Fan Deco Studio. No editar a mano. */\n' + tokens + '\n';
for ( const f of files ) {
	const filtered = filterRoot( postcss.parse( readFileSync( `src/css/${ f }`, 'utf8' ) ), f );
	css += `\n/* ---- ${ f } ---- */\n${ filtered.toString() }\n`;
}
css += '\n' + readFileSync( 'src/css/blocks-plugin.css', 'utf8' );
writeFileSync( 'src/css/blocks.generated.css', css );
console.log( `blocks.generated.css: ${ ( css.length / 1024 ).toFixed( 0 ) } kB, ${ bases.size } familias de clases` );
