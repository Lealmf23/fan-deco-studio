/** Carrusel con scroll-snap nativo; el JS solo conecta los botones. */
export function initCarousels() {
	document.querySelectorAll( '[data-fd-carousel]' ).forEach( ( root ) => {
		const track = root.querySelector( '.fd-carousel__track' );
		if ( ! track ) {
			return;
		}
		const step = () => {
			const item = track.querySelector( '.fd-product-grid__item' );
			return ( item ? item.getBoundingClientRect().width : track.clientWidth * 0.8 ) + 24;
		};
		const go = ( dir ) => {
			const reduce = window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches;
			track.scrollBy( { left: dir * step(), behavior: reduce ? 'auto' : 'smooth' } );
		};
		root.querySelector( '[data-fd-carousel-prev]' )?.addEventListener( 'click', () => go( -1 ) );
		root.querySelector( '[data-fd-carousel-next]' )?.addEventListener( 'click', () => go( 1 ) );
		track.addEventListener( 'keydown', ( e ) => {
			if ( e.key === 'ArrowRight' ) {
				e.preventDefault();
				go( 1 );
			} else if ( e.key === 'ArrowLeft' ) {
				e.preventDefault();
				go( -1 );
			}
		} );
	} );
}
