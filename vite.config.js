import { defineConfig } from 'vite';
import tailwindcss from '@tailwindcss/vite';

// Build de frontend del tema. Los bloques de Gutenberg se compilan aparte con @wordpress/scripts
// (ver README, "Decisiones técnicas").
export default defineConfig( {
	base: './', // rutas relativas: el tema se instala en cualquier ruta de wp-content/themes
	plugins: [ tailwindcss() ],
	server: {
		host: 'localhost',
		port: 5173,
		strictPort: true,
		cors: true,
		origin: 'http://localhost:5173',
	},
	build: {
		outDir: 'dist',
		emptyOutDir: true,
		manifest: true,
		target: 'es2020',
		rollupOptions: { input: 'src/js/main.js' },
	},
} );
