import { spawnSync } from 'node:child_process';
let code = 0;
for ( const f of [ 'scripts/smoke/run.php', 'scripts/smoke/core-test.php' ] ) {
	const r = spawnSync( 'php', [ f ], { stdio: 'inherit' } );
	code ||= r.status ?? 1;
}
process.exit( code );
