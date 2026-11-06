/** Rendimiento: las animaciones continuas (franja de mensajes) se pausan cuando no están a la vista. */
export function initOffscreenPause() {
	if ( ! ( 'IntersectionObserver' in window ) ) {
		return;
	}
	const io = new IntersectionObserver( ( entries ) => {
		entries.forEach( ( e ) => e.target.classList.toggle( 'is-offscreen', ! e.isIntersecting ) );
	}, { rootMargin: '80px' } );
	document.querySelectorAll( '.fd-marquee' ).forEach( ( el ) => io.observe( el ) );
}
