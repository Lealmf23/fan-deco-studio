/** Mensajes rotativos de la barra superior. Sin movimiento si el usuario prefiere reducirlo; se pausa al pasar el mouse o enfocar. */
export function initTicker() {
	const root = document.querySelector( '[data-fd-ticker]' );
	if ( ! root || window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches ) {
		return;
	}
	const items = [ ...root.querySelectorAll( '.fd-announcement__msg' ) ];
	let i = 0;
	let paused = false;
	const show = ( n ) => items.forEach( ( el, k ) => el.classList.toggle( 'is-active', k === n ) );
	root.addEventListener( 'mouseenter', () => { paused = true; } );
	root.addEventListener( 'mouseleave', () => { paused = false; } );
	root.addEventListener( 'focusin', () => { paused = true; } );
	root.addEventListener( 'focusout', () => { paused = false; } );
	setInterval( () => {
		if ( ! paused && ! document.hidden ) {
			i = ( i + 1 ) % items.length;
			show( i );
		}
	}, 4500 );
}
