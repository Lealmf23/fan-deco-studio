import { spawnSync } from 'node:child_process';
let code = 0;
for ( const f of [ 'scripts/smoke/run.php', 'scripts/smoke/core-test.php', 'scripts/smoke/transfer-test.php', 'scripts/smoke/installments-test.php', 'scripts/smoke/lights-test.php', 'scripts/smoke/pinfo-test.php', 'scripts/smoke/customizer-test.php', 'scripts/smoke/design-test.php', 'scripts/smoke/blocks-plugin-test.php', 'scripts/smoke/core-css-test.php' ] ) {
	const r = spawnSync( 'php', [ f ], { stdio: 'inherit' } );
	code ||= r.status ?? 1;
}
{
	const r = spawnSync( 'node', [ 'scripts/design-parity.mjs' ], { stdio: 'inherit' } );
	code ||= r.status ?? 1;
}
process.exit( code );
