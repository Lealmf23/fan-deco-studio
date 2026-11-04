// Prueba de paridad: el CSS que genera PHP (inc/design.php) y el que genera la vista previa en vivo (JS) deben ser idénticos.
import { spawnSync } from 'node:child_process';
import { readFileSync } from 'node:fs';
import vm from 'node:vm';

const php = spawnSync( 'php', [ 'scripts/smoke/design-export.php' ], { encoding: 'utf8' } );
if ( php.status !== 0 ) {
	console.error( php.stderr || php.stdout );
	process.exit( 1 );
}
const { schema, cases } = JSON.parse( php.stdout );
const sandbox = { module: { exports: {} } };
vm.runInNewContext( readFileSync( 'assets/js/design-preview.js', 'utf8' ), sandbox ); // se evalúa como script (no como módulo), igual que en el navegador
const { buildCss, bodyClasses } = sandbox.module.exports;
let fail = 0;
for ( const c of cases ) {
	const css = buildCss( schema, ( k ) => c.values[ k ] );
	const classes = bodyClasses( schema, ( k ) => c.values[ k ] ).join( ' ' );
	const ok = css === c.css && classes === c.classes;
	console.log( `${ ok ? '  ok  ' : '  FAIL' } paridad PHP/JS: ${ c.name }` );
	if ( ! ok ) {
		fail++;
		console.log( '    PHP:', c.css, '|', c.classes );
		console.log( '    JS :', css, '|', classes );
	}
}
console.log( fail ? `\n${ fail } verificación(es) fallaron` : '\nTodas las verificaciones pasaron' );
process.exit( fail ? 1 : 0 );
