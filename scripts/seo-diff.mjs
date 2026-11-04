// Compara dos snapshots (antes = sitio actual con AIOSEO, después = staging con Fan Deco Core).
// Uso: node scripts/seo-diff.mjs antes.json despues.json [--host-map=https://fandeco.com.ar=https://staging.example.com]
import { readFileSync } from 'node:fs';

const [ , , aPath, bPath, ...rest ] = process.argv;
if ( ! aPath || ! bPath ) {
	console.error( 'Uso: node scripts/seo-diff.mjs antes.json despues.json [--host-map=origen=destino]' );
	process.exit( 1 );
}
const map = ( rest.find( ( a ) => a.startsWith( '--host-map=' ) ) || '' ).replace( '--host-map=', '' ).split( '=' );
const norm = ( s ) => ( map[ 0 ] && map[ 1 ] ? String( s ).replaceAll( map[ 1 ], map[ 0 ] ) : String( s ) );
const A = JSON.parse( readFileSync( aPath, 'utf8' ) );
const B = JSON.parse( readFileSync( bPath, 'utf8' ) );
const toPath = ( u ) => { try { return new URL( u ).pathname; } catch { return u; } };
const Bp = Object.fromEntries( Object.entries( B ).map( ( [ u, v ] ) => [ toPath( u ), v ] ) );

const fields = [ 'title', 'description', 'canonical', 'robots', 'h1' ];
let problems = 0;
for ( const [ url, a ] of Object.entries( A ) ) {
	const path = toPath( url );
	const b = Bp[ path ];
	if ( ! b ) { console.log( `FALTA   ${ path }` ); problems++; continue; }
	if ( b.status !== a.status ) { console.log( `ESTADO  ${ path }: ${ a.status } → ${ b.status }` ); problems++; continue; }
	for ( const f of fields ) {
		const av = f === 'canonical' ? toPath( a[ f ] ?? '' ) : String( a[ f ] ?? '' );
		const bv = f === 'canonical' ? toPath( b[ f ] ?? '' ) : norm( b[ f ] ?? '' );
		if ( av !== bv ) { console.log( `${ f.toUpperCase().padEnd( 7 ) } ${ path }\n   antes:   ${ av }\n   después: ${ bv }` ); problems++; }
	}
}
console.log( problems ? `\n${ problems } diferencia(s). Revisá cada una antes de lanzar.` : '\nSin diferencias SEO on-page.' );
process.exit( problems ? 1 : 0 );
