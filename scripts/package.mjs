// Genera release/fan-deco-studio.zip con SOLO lo necesario para producción (multiplataforma).
import AdmZip from 'adm-zip';
import { existsSync, mkdirSync, readdirSync, statSync, rmSync } from 'node:fs';
import { join } from 'node:path';

const THEME = 'fan-deco-studio';
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
const out = `release/${ THEME }.zip`;
rmSync( out, { force: true } );
zip.writeZip( out );
console.log( `OK: ${ out } (${ zip.getEntries().length } archivos, ${ ( statSync( out ).size / 1024 ).toFixed( 0 ) } kB)` );
