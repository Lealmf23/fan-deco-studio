import { execFileSync } from 'node:child_process';
import { readdirSync, statSync } from 'node:fs';
import { join } from 'node:path';

const skip = new Set( [ 'node_modules', 'dist', 'release', '.git' ] );
const files = [];
( function walk( dir ) {
	for ( const name of readdirSync( dir ) ) {
		if ( skip.has( name ) ) continue;
		const p = join( dir, name );
		if ( statSync( p ).isDirectory() ) walk( p );
		else if ( p.endsWith( '.php' ) ) files.push( p );
	}
} )( '.' );

let failed = 0;
for ( const f of files ) {
	try {
		execFileSync( 'php', [ '-l', f ], { stdio: 'pipe' } );
	} catch ( e ) {
		failed++;
		console.error( String( e.stdout || e.stderr ) );
	}
}
console.log( `${ files.length } archivos PHP revisados, ${ failed } con errores` );
process.exit( failed ? 1 : 0 );
