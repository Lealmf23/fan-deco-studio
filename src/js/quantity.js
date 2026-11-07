/** Botones − / + para el campo de cantidad de WooCommerce (respeta min, max y step; el input sigue siendo el original). */
export function initQuantity() {
	const enhance = () => {
		document.querySelectorAll( '.quantity' ).forEach( ( wrap ) => {
			const input = wrap.querySelector( 'input.qty' );
			if ( ! input || input.type === 'hidden' || wrap.dataset.fdQty ) {
				return;
			}
			wrap.dataset.fdQty = '1';
			const make = ( sign, label ) => {
				const b = document.createElement( 'button' );
				b.type = 'button';
				b.className = 'fd-qty-btn';
				b.setAttribute( 'aria-label', label );
				b.textContent = sign;
				b.addEventListener( 'click', () => {
					const step = Number( input.step ) || 1;
					const min = input.min === '' ? 1 : Number( input.min );
					const max = input.max === '' ? Infinity : Number( input.max );
					const cur = Number( input.value ) || min;
					const next = Math.min( max, Math.max( min, cur + ( sign === '+' ? step : -step ) ) );
					if ( next !== cur ) {
						input.value = String( next );
						input.dispatchEvent( new Event( 'change', { bubbles: true } ) );
					}
				} );
				return b;
			};
			wrap.prepend( make( '−', 'Disminuir cantidad' ) );
			wrap.append( make( '+', 'Aumentar cantidad' ) );
		} );
	};
	enhance();
	const $ = window.jQuery;
	if ( $ ) { // el carrito vuelve a dibujar su HTML por AJAX
		$( document.body ).on( 'updated_cart_totals updated_wc_div wc_fragments_refreshed', enhance );
	}
}
