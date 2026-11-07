/** Interruptor de luz del hero: alterna la portada entre día (apagada) y noche (encendida). Lo dispara el visitante. */
export function initHeroLight() {
	document.querySelectorAll( '[data-fd-hero-light]' ).forEach( ( hero ) => {
		const sw = hero.querySelector( '[data-fd-hero-switch]' );
		const label = hero.querySelector( '[data-fd-hero-switch-label]' );
		if ( ! sw ) {
			return;
		}
		sw.addEventListener( 'click', () => {
			const on = sw.getAttribute( 'aria-checked' ) !== 'true';
			sw.setAttribute( 'aria-checked', String( on ) );
			hero.classList.toggle( 'is-lit', on );
			label.textContent = sw.getAttribute( on ? 'data-on' : 'data-off' );
		} );
	} );
}
