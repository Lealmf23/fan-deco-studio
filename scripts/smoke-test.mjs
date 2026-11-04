import { spawnSync } from 'node:child_process';
const r = spawnSync( 'php', [ 'scripts/smoke/run.php' ], { stdio: 'inherit' } );
process.exit( r.status ?? 1 );
