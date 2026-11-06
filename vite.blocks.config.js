import { defineConfig } from 'vite';

// Paquete de estilos y scripts del plugin fan-deco-blocks (sin Tailwind: el CSS lo genera scripts/build-blocks-css.mjs).
export default defineConfig( {
	base: './',
	build: {
		outDir: 'plugins/fan-deco-blocks/dist',
		emptyOutDir: true,
		target: 'es2020',
		cssCodeSplit: false,
		rollupOptions: {
			input: 'src/js/blocks.js',
			output: {
				format: 'iife',
				entryFileNames: 'blocks.js',
				assetFileNames: ( a ) => ( a.names?.[ 0 ]?.endsWith( '.css' ) || a.name?.endsWith?.( '.css' ) ? 'blocks.css' : 'fonts/[name][extname]' ),
			},
		},
	},
} );
