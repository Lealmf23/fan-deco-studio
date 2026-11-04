/**
 * Carrusel de banners: scroll-snap nativo (deslizar con el dedo o el mouse), flechas, puntos, pausa y avance
 * automático opcional. El avance se detiene con "prefers-reduced-motion", al pasar el cursor, al enfocar
 * cualquier control, mientras la pestaña está oculta y cuando el usuario toca "Pausar".
 * Sin JavaScript el carrusel sigue siendo desplazable y los controles quedan ocultos (atributo hidden).
 */
export function initBannerCarousels() {
	document.querySelectorAll( '[data-fd-bancar]' ).forEach( ( root ) => {
		const track = root.querySelector( '[data-fd-bancar-track]' );
		const controls = root.querySelector( '[data-fd-bancar-controls]' );
		if ( ! track || ! controls ) {
			return;
		}
		const slides = [ ...track.children ];
		const dotsBox = controls.querySelector( '[data-fd-bancar-dots]' );
		const prev = controls.querySelector( '[data-fd-bancar-prev]' );
		const next = controls.querySelector( '[data-fd-bancar-next]' );
		const pauseBtn = controls.querySelector( '[data-fd-bancar-pause]' );
		const reduce = window.matchMedia( '(prefers-reduced-motion: reduce)' );
		const behavior = () => ( reduce.matches ? 'auto' : 'smooth' );
		let dots = [];
		let timer = null;
		let userPaused = false;
		let hover = false;
		let focus = false;

		const step = () => {
			if ( slides.length < 2 ) {
				return track.clientWidth;
			}
			return slides[ 1 ].offsetLeft - slides[ 0 ].offsetLeft;
		};
		const positions = () => Math.max( 1, Math.round( ( track.scrollWidth - track.clientWidth ) / step() ) + 1 );
		const current = () => Math.min( positions() - 1, Math.max( 0, Math.round( track.scrollLeft / step() ) ) );

		const go = ( index ) => {
			const n = positions();
			const i = ( index + n ) % n;
			track.scrollTo( { left: i * step(), behavior: behavior() } );
		};

		const buildDots = () => {
			const n = positions();
			dotsBox.replaceChildren();
			dots = [];
			if ( n < 2 ) {
				controls.hidden = true;
				return;
			}
			controls.hidden = false;
			for ( let i = 0; i < n; i++ ) {
				const b = document.createElement( 'button' );
				b.type = 'button';
				b.className = 'fd-bancar__dot';
				b.setAttribute( 'aria-label', `Ir al banner ${ i + 1 }` );
				b.addEventListener( 'click', () => go( i ) );
				dotsBox.append( b );
				dots.push( b );
			}
			sync();
		};
		const sync = () => {
			const c = current();
			dots.forEach( ( d, i ) => {
				if ( i === c ) {
					d.setAttribute( 'aria-current', 'true' );
				} else {
					d.removeAttribute( 'aria-current' );
				}
			} );
		};

		const stop = () => {
			clearInterval( timer );
			timer = null;
		};
		const play = () => {
			stop();
			if ( root.dataset.autoplay !== '1' || reduce.matches || userPaused || hover || focus || document.hidden || positions() < 2 ) {
				return;
			}
			const ms = Math.max( 3, Number( root.dataset.interval ) || 5 ) * 1000;
			timer = setInterval( () => go( current() + 1 ), ms );
		};

		prev.addEventListener( 'click', () => go( current() - 1 ) );
		next.addEventListener( 'click', () => go( current() + 1 ) );
		track.addEventListener( 'scroll', () => window.requestAnimationFrame( sync ), { passive: true } );
		track.addEventListener( 'keydown', ( e ) => {
			if ( e.key === 'ArrowRight' ) {
				e.preventDefault();
				go( current() + 1 );
			} else if ( e.key === 'ArrowLeft' ) {
				e.preventDefault();
				go( current() - 1 );
			}
		} );
		if ( pauseBtn ) {
			pauseBtn.addEventListener( 'click', () => {
				userPaused = ! userPaused;
				pauseBtn.setAttribute( 'aria-pressed', userPaused ? 'true' : 'false' );
				pauseBtn.setAttribute( 'aria-label', userPaused ? pauseBtn.dataset.play : pauseBtn.dataset.pause );
				play();
			} );
		}
		root.addEventListener( 'mouseenter', () => { hover = true; play(); } );
		root.addEventListener( 'mouseleave', () => { hover = false; play(); } );
		root.addEventListener( 'focusin', () => { focus = true; play(); } );
		root.addEventListener( 'focusout', () => { focus = false; play(); } );
		root.addEventListener( 'touchstart', stop, { passive: true } );
		root.addEventListener( 'touchend', () => setTimeout( play, 4000 ), { passive: true } );
		document.addEventListener( 'visibilitychange', play );
		reduce.addEventListener( 'change', play );
		window.addEventListener( 'resize', () => { buildDots(); play(); } );

		buildDots();
		play();
	} );
}
