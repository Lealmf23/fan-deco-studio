/**
 * Pila de capas: las fotos se apilan como capas de una impresión 3D. La activa va al frente y limpia; las demás
 * asoman detrás (más chicas, inclinadas y con rayas de capa). Se avanza con flechas, puntos, teclado, deslizando o
 * tocando una tarjeta de atrás. El avance automático se detiene al pasar el cursor, al enfocar un control, con la
 * pestaña oculta, si el usuario pausa y con "prefers-reduced-motion". Sin JavaScript las fotos se ven en una grilla.
 */
export function initBannerCarousels() {
	document.querySelectorAll( '[data-fd-bancar]' ).forEach( ( root ) => {
		const stage = root.querySelector( '[data-fd-bancar-track]' );
		const controls = root.querySelector( '[data-fd-bancar-controls]' );
		if ( ! stage ) {
			return;
		}
		const slides = [ ...stage.children ];
		const n = slides.length;
		if ( n < 2 || ! controls ) {
			return; // una sola foto: se muestra tal cual
		}
		const dotsBox = controls.querySelector( '[data-fd-bancar-dots]' );
		const prev = controls.querySelector( '[data-fd-bancar-prev]' );
		const next = controls.querySelector( '[data-fd-bancar-next]' );
		const pauseBtn = controls.querySelector( '[data-fd-bancar-pause]' );
		const reduce = window.matchMedia( '(prefers-reduced-motion: reduce)' );
		let index = 0;
		let timer = null;
		let userPaused = false;
		let hover = false;
		let focus = false;

		const dots = slides.map( ( _, i ) => {
			const b = document.createElement( 'button' );
			b.type = 'button';
			b.className = 'fd-bancar__dot';
			b.setAttribute( 'aria-label', `Ir al banner ${ i + 1 }` );
			b.addEventListener( 'click', () => go( i ) );
			dotsBox.append( b );
			return b;
		} );

		const layout = () => {
			slides.forEach( ( li, i ) => {
				let d = i - index;
				if ( d > n / 2 ) {
					d -= n;
				} else if ( d < -n / 2 ) {
					d += n;
				}
				const a = Math.abs( d );
				const visible = a <= 2;
				li.style.setProperty( '--x', String( d * 0.58 ) );
				li.style.setProperty( '--s', String( Math.max( 0.6, 1 - a * 0.1 ) ) );
				li.style.setProperty( '--r', `${ d * 3.2 }deg` );
				li.style.setProperty( '--z', String( 20 - a ) );
				li.style.setProperty( '--o', visible ? String( a === 0 ? 1 : a === 1 ? 0.96 : 0.7 ) : '0' );
				li.classList.toggle( 'is-active', d === 0 );
				li.classList.toggle( 'is-before', d < 0 );
				// Solo el enlace queda fuera del teclado; la tarjeta sigue respondiendo al toque para traerla al frente.
				const link = li.querySelector( '.fd-bancar__link' );
				if ( link ) {
					link.toggleAttribute( 'inert', d !== 0 );
				}
				li.setAttribute( 'aria-hidden', d === 0 ? 'false' : 'true' );
			} );
			dots.forEach( ( b, i ) => {
				if ( i === index ) {
					b.setAttribute( 'aria-current', 'true' );
				} else {
					b.removeAttribute( 'aria-current' );
				}
			} );
		};
		const go = ( i ) => {
			index = ( i + n ) % n;
			layout();
		};

		const stop = () => {
			clearInterval( timer );
			timer = null;
		};
		const play = () => {
			stop();
			if ( root.dataset.autoplay !== '1' || reduce.matches || userPaused || hover || focus || document.hidden ) {
				return;
			}
			const ms = Math.max( 3, Number( root.dataset.interval ) || 5 ) * 1000;
			timer = setInterval( () => go( index + 1 ), ms );
		};

		prev.addEventListener( 'click', () => go( index - 1 ) );
		next.addEventListener( 'click', () => go( index + 1 ) );
		root.addEventListener( 'keydown', ( e ) => {
			if ( e.key === 'ArrowRight' ) {
				e.preventDefault();
				go( index + 1 );
			} else if ( e.key === 'ArrowLeft' ) {
				e.preventDefault();
				go( index - 1 );
			}
		} );

		stage.addEventListener( 'dragstart', ( e ) => e.preventDefault() ); // el arrastre nativo de la imagen cancelaría el deslizamiento
		// Deslizar con el dedo o el mouse; un toque en una tarjeta de atrás la trae al frente.
		let startX = null;
		let moved = false;
		stage.addEventListener( 'pointerdown', ( e ) => {
			startX = e.clientX;
			moved = false;
			stop();
		} );
		stage.addEventListener( 'pointermove', ( e ) => {
			if ( startX !== null && Math.abs( e.clientX - startX ) > 8 ) {
				moved = true;
			}
		} );
		const end = ( e ) => {
			if ( startX === null ) {
				return;
			}
			const dx = e.clientX - startX;
			startX = null;
			if ( Math.abs( dx ) > 40 ) {
				go( index + ( dx < 0 ? 1 : -1 ) );
			}
			play();
		};
		stage.addEventListener( 'pointerup', end );
		stage.addEventListener( 'pointercancel', () => { startX = null; play(); } );
		stage.addEventListener( 'click', ( e ) => {
			if ( moved ) {
				e.preventDefault(); // un arrastre no debe abrir el enlace
				moved = false;
				return;
			}
			const li = e.target.closest( '.fd-bancar__slide' );
			if ( li && ! li.classList.contains( 'is-active' ) ) {
				e.preventDefault();
				go( slides.indexOf( li ) );
			}
		}, true );

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
		document.addEventListener( 'visibilitychange', play );
		reduce.addEventListener( 'change', play );

		controls.hidden = false;
		root.classList.add( 'is-ready' );
		layout();
		play();
	} );
}
