// Paquete del plugin fan-deco-blocks: estilos + comportamiento de los bloques (carrusel de productos, pila de banners, interruptor de luz).
import '../css/blocks.generated.css';
import { initCarousels } from './carousel.js';
import { initHeroLight } from './hero-light.js';
import { initBannerCarousels } from './banner-carousel.js';
import './card-dots.js';
import { initOffscreenPause } from './perf.js';

const boot = () => {
	initCarousels();
	initHeroLight();
	initBannerCarousels();
	initOffscreenPause();
};
if ( document.readyState === 'loading' ) {
	document.addEventListener( 'DOMContentLoaded', boot );
} else {
	boot();
}
