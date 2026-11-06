// Genera release/fan-deco-studio.zip con SOLO lo necesario para producción (multiplataforma).
import AdmZip from 'adm-zip';
import { existsSync, mkdirSync, readdirSync, statSync, rmSync, readFileSync } from 'node:fs';
import { join } from 'node:path';

const THEME = 'fan-deco-studio';
const themeVersion = /Version:\s*(\d+\.\d+\.\d+)/.exec( readFileSync( 'style.css', 'utf8' ) )?.[ 1 ] ?? '0.0.0';
const coreVersion = /\* Version:\s*(\d+\.\d+\.\d+)/.exec( readFileSync( 'plugins/fan-deco-core/fan-deco-core.php', 'utf8' ) )?.[ 1 ] ?? '0.0.0';
const INCLUDE = [ 'style.css', 'functions.php', 'theme.json', 'index.php', 'front-page.php', 'page.php', 'single.php', '404.php', 'header.php', 'footer.php', 'woocommerce.php', 'screenshot.png', 'README.md', 'inc', 'template-parts', 'patterns', 'languages', 'build', 'dist', 'assets' ];

for ( const need of [ 'dist/.vite/manifest.json', 'build/blocks' ] ) {
	if ( ! existsSync( need ) ) {
		console.error( `Falta ${ need }. Ejecutá "npm run build" primero.` );
		process.exit( 1 );
	}
}

const zip = new AdmZip();
function add( path ) {
	if ( ! existsSync( path ) ) return;
	if ( statSync( path ).isDirectory() ) {
		const entries = readdirSync( path );
		if ( ! entries.length ) return;
		entries.forEach( ( n ) => add( join( path, n ) ) );
	} else if ( ! path.endsWith( '.DS_Store' ) && ! path.endsWith( '.gitkeep' ) ) {
		const dir = path.split( /[\\/]/ ).slice( 0, -1 ).join( '/' );
		zip.addLocalFile( path, `${ THEME }/${ dir }`.replace( /\/$/, '' ) );
	}
}
INCLUDE.forEach( add );

mkdirSync( 'release', { recursive: true } );
for ( const old of readdirSync( 'release' ) ) {
	if ( old.endsWith( '.zip' ) ) rmSync( join( 'release', old ), { force: true } ); // solo queda la versión actual
}
const out = `release/${ THEME }-${ themeVersion }.zip`;
rmSync( out, { force: true } );
zip.writeZip( out );
console.log( `OK: ${ out } (${ zip.getEntries().length } archivos, ${ ( statSync( out ).size / 1024 ).toFixed( 0 ) } kB)` );

// ---- Plugin Fan Deco Core ----
const PLUGIN = 'fan-deco-core';
const pzip = new AdmZip();
( function addDir( dir ) {
	for ( const n of readdirSync( dir ) ) {
		const path = join( dir, n );
		if ( statSync( path ).isDirectory() ) addDir( path );
		else if ( ! n.endsWith( '.DS_Store' ) ) pzip.addLocalFile( path, `${ PLUGIN }/${ dir.replace( `plugins/${ PLUGIN }`, '' ).replace( /^[\\/]/, '' ).replace( /\\/g, '/' ) }`.replace( /\/$/, '' ) );
	}
} )( `plugins/${ PLUGIN }` );
const pout = `release/${ PLUGIN }-${ coreVersion }.zip`;
rmSync( pout, { force: true } );
pzip.writeZip( pout );
console.log( `OK: ${ pout } (${ pzip.getEntries().length } archivos)` );

// ---- Plugin Fan Deco Blocks ----
const BLOCKS = 'fan-deco-blocks';
const blocksVersion = /\* Version:\s*(\d+\.\d+\.\d+)/.exec( readFileSync( `plugins/${ BLOCKS }/${ BLOCKS }.php`, 'utf8' ) )?.[ 1 ] ?? '0.0.0';
for ( const need of [ `plugins/${ BLOCKS }/dist/blocks.css`, `plugins/${ BLOCKS }/dist/blocks.js`, `plugins/${ BLOCKS }/build/blocks` ] ) {
	if ( ! existsSync( need ) ) {
		console.error( `Falta ${ need }. Ejecutá "npm run build" primero.` );
		process.exit( 1 );
	}
}
const bzip = new AdmZip();
( function addDir( dir ) {
	for ( const n of readdirSync( dir ) ) {
		const path = join( dir, n );
		if ( statSync( path ).isDirectory() ) addDir( path );
		else if ( ! n.endsWith( '.DS_Store' ) ) bzip.addLocalFile( path, `${ BLOCKS }/${ dir.replace( `plugins/${ BLOCKS }`, '' ).replace( /^[\\/]/, '' ).replace( /\\/g, '/' ) }`.replace( /\/$/, '' ) );
	}
} )( `plugins/${ BLOCKS }` );
const bout = `release/${ BLOCKS }-${ blocksVersion }.zip`;
rmSync( bout, { force: true } );
bzip.writeZip( bout );
console.log( `OK: ${ bout } (${ bzip.getEntries().length } archivos)` );
