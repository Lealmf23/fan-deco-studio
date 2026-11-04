import '../css/main.css';
import { initHeader } from './header.js';
import { initCarousels } from './carousel.js';
import { initCartFeedback } from './cart.js';
import { initTicker } from './ticker.js';
import { initQuantity } from './quantity.js';
import { initBuybar } from './buybar.js';
import { initHeroLight } from './hero-light.js';

/** Nota: el JS crítico es mínimo y sin dependencias. GSAP se carga bajo demanda (ver animations.js). */
function boot() {
	initHeader();
	initCarousels();
	initCartFeedback();
	initTicker();
	initQuantity();
	initBuybar();
	initHeroLight();
	loadSwatches();
	loadVariationEvents();
	loadAnimations();
}

function loadSwatches() {
	if ( document.querySelector( '[data-fd-swatches]' ) ) {
		import( './swatches.js' ).then( ( m ) => m.initSwatches() );
	}
}

function loadVariationEvents() {
	if ( document.querySelector( '.variations_form' ) ) {
		import( './variations.js' ).then( ( m ) => m.initVariationEvents() );
	}
}

function loadAnimations() {
	const root = document.documentElement;
	// Sin hidratación animada si el usuario prefiere menos movimiento (el CSS tampoco oculta nada).
	if ( ! root.classList.contains( 'fd-anim' ) ) {
		return;
	}
	if ( ! document.querySelector( '[data-fd-animate="true"] [data-fd-reveal]' ) ) {
		root.classList.remove( 'fd-anim' );
		return;
	}
	// Red de seguridad: si GSAP no carga, el contenido nunca queda oculto.
	const failsafe = setTimeout( () => root.classList.remove( 'fd-anim' ), 4000 );
	import( './animations.js' )
		.then( ( m ) => m.initAnimations() )
		.catch( () => root.classList.remove( 'fd-anim' ) )
		.finally( () => clearTimeout( failsafe ) );
}

if ( document.readyState === 'loading' ) {
	document.addEventListener( 'DOMContentLoaded', boot );
} else {
	boot();
}
