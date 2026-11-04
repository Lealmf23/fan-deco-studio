// Captura el SEO on-page de un sitio a partir de su sitemap.
// Uso: node scripts/seo-snapshot.mjs <URL-del-sitemap | URL-de-lista.txt> <salida.json> [--max=500]
// Ej.:  node scripts/seo-snapshot.mjs https://fandeco.com.ar/sitemap.xml antes.json
// Solo lectura (GET). Concurrencia baja para no cargar el servidor.
import { writeFileSync } from 'node:fs';

const [ , , source, out, ...rest ] = process.argv;
if ( ! source || ! out ) {
	console.error( 'Uso: node scripts/seo-snapshot.mjs <sitemap-url> <salida.json> [--max=500]' );
	process.exit( 1 );
}
const max = Number( ( rest.find( ( a ) => a.startsWith( '--max=' ) ) || '--max=500' ).split( '=' )[ 1 ] );

const get = async ( url ) => {
	const r = await fetch( url, { redirect: 'manual', headers: { 'user-agent': 'FanDecoSEOSnapshot/1.0' } } );
	return { status: r.status, location: r.headers.get( 'location' ), text: r.status < 300 ? await r.text() : '' };
};
const locs = ( xml ) => [ ...xml.matchAll( /<loc>\s*([^<\s]+)\s*<\/loc>/g ) ].map( ( m ) => m[ 1 ].replace( /&amp;/g, '&' ) );

async function collect( url, depth = 0 ) {
	const { text } = await get( url );
	const found = locs( text );
	if ( /<sitemapindex/i.test( text ) && depth < 2 ) {
		const nested = [];
		for ( const child of found ) nested.push( ...( await collect( child, depth + 1 ) ) );
		return nested;
	}
	return found;
}

const attr = ( html, re ) => ( html.match( re ) || [] )[ 1 ]?.trim() ?? '';
export function extract( html ) {
	const tag = ( name, a, b ) => attr( html, new RegExp( `<${ name }[^>]*${ a }=["']${ b }["'][^>]*>`, 'i' ) ) ;
	const metaContent = ( key, val ) => {
		const m = html.match( new RegExp( `<meta[^>]*${ key }=["']${ val }["'][^>]*>`, 'i' ) );
		return m ? ( m[ 0 ].match( /content=["']([^"']*)["']/i ) || [] )[ 1 ] ?? '' : '';
	};
	const canonical = ( html.match( /<link[^>]*rel=["']canonical["'][^>]*>/i ) || [ '' ] )[ 0 ].match( /href=["']([^"']*)["']/i )?.[ 1 ] ?? '';
	return {
		title: attr( html, /<title[^>]*>([\s\S]*?)<\/title>/i ).replace( /\s+/g, ' ' ),
		description: metaContent( 'name', 'description' ),
		canonical,
		robots: metaContent( 'name', 'robots' ),
		ogTitle: metaContent( 'property', 'og:title' ),
		ogImage: metaContent( 'property', 'og:image' ),
		h1: ( html.match( /<h1[\s>]/gi ) || [] ).length,
		jsonld: ( html.match( /application\/ld\+json/gi ) || [] ).length,
	};
}

const isList = source.endsWith( '.txt' );
let urls = isList ? ( await get( source ) ).text.split( /\s+/ ).filter( Boolean ) : await collect( source );
urls = [ ...new Set( urls ) ].slice( 0, max );
console.error( `${ urls.length } URLs` );

const result = {};
let i = 0;
async function worker() {
	while ( i < urls.length ) {
		const url = urls[ i++ ];
		try {
			const r = await get( url );
			result[ url ] = r.status >= 300 ? { status: r.status, location: r.location } : { status: r.status, ...extract( r.text ) };
		} catch ( e ) {
			result[ url ] = { error: String( e.message ) };
		}
	}
}
await Promise.all( Array.from( { length: 3 }, worker ) );
writeFileSync( out, JSON.stringify( result, null, 2 ) );
console.error( `Guardado en ${ out }` );
