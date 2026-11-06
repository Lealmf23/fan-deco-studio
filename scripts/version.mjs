// Sube la versión del tema (y opcionalmente del plugin) en todos los lugares a la vez.
// Uso: npm run bump -- minor|patch|major|X.Y.Z [--core] [--blocks]   (--core también sube fan-deco-core)
import { readFileSync, writeFileSync } from 'node:fs';

const args = process.argv.slice( 2 );
const target = args.find( ( a ) => ! a.startsWith( '--' ) );
const withCore = args.includes( '--core' );
const withBlocks = args.includes( '--blocks' );
const cur = /Version:\s*(\d+\.\d+\.\d+)/.exec( readFileSync( 'style.css', 'utf8' ) )?.[ 1 ];
if ( ! target || ! cur ) {
	console.error( 'Uso: npm run bump -- minor|patch|major|X.Y.Z [--core] [--blocks]' );
	process.exit( 1 );
}
const bump = ( v, how ) => {
	if ( /^\d+\.\d+\.\d+$/.test( how ) ) return how;
	const [ a, b, c ] = v.split( '.' ).map( Number );
	return { major: `${ a + 1 }.0.0`, minor: `${ a }.${ b + 1 }.0`, patch: `${ a }.${ b }.${ c + 1 }` }[ how ];
};
const next = bump( cur, target );
if ( ! next ) {
	console.error( `Versión no válida: ${ target }` );
	process.exit( 1 );
}
const edit = ( file, re, rep ) => {
	const s = readFileSync( file, 'utf8' );
	if ( ! re.test( s ) ) throw new Error( `No encontré la versión en ${ file }` );
	writeFileSync( file, s.replace( re, rep ) );
};
edit( 'style.css', /(Version:\s*)\d+\.\d+\.\d+/, `$1${ next }` );
edit( 'functions.php', /(define\( 'FANDECO_VERSION', ')\d+\.\d+\.\d+/, `$1${ next }` );
edit( 'package.json', /("version":\s*")\d+\.\d+\.\d+/, `$1${ next }` );
console.log( `Tema: ${ cur } -> ${ next }` );
if ( withCore ) {
	edit( 'plugins/fan-deco-core/fan-deco-core.php', /(\* Version:\s*)\d+\.\d+\.\d+/, `$1${ next }` );
	edit( 'plugins/fan-deco-core/fan-deco-core.php', /(define\( 'FANDECO_CORE_VERSION', ')\d+\.\d+\.\d+/, `$1${ next }` );
	console.log( `Plugin: -> ${ next }` );
}
if ( withBlocks ) {
	edit( 'plugins/fan-deco-blocks/fan-deco-blocks.php', /(\* Version:\s*)\d+\.\d+\.\d+/, `$1${ next }` );
	edit( 'plugins/fan-deco-blocks/fan-deco-blocks.php', /(define\( 'FANDECO_BLOCKS_VERSION', ')\d+\.\d+\.\d+/, `$1${ next }` );
	console.log( `Plugin de bloques: -> ${ next }` );
}
console.log( 'Recordá anotar los cambios en CHANGELOG.md y correr "npm run package".' );
