/** Animación discreta del carrito al agregar productos (eventos jQuery de WooCommerce). */
export function initCartFeedback() {
	const $ = window.jQuery;
	if ( ! $ ) {
		return;
	}
	$( document.body ).on( 'added_to_cart', () => {
		// WooCommerce reemplaza el enlace por fragmentos: buscar el nodo ya actualizado.
		setTimeout( () => {
			const link = document.querySelector( '.fd-cart-link' );
			if ( ! link || window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches ) {
				return;
			}
			link.classList.remove( 'is-bump' );
			void link.offsetWidth; // reinicia la animación
			link.classList.add( 'is-bump' );
		}, 60 );
	} );
}
