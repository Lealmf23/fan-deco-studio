/**
 * Selectores visuales de variaciones. El <select> de WooCommerce es la fuente de verdad:
 * los botones solo cambian su valor y disparan `change` (el JS nativo calcula precio/stock/imagen).
 */
export function initSwatches() {
	document.querySelectorAll( '[data-fd-swatches]' ).forEach( ( group ) => {
		const wrap = group.closest( '.fd-swatches-wrap' );
		const select = wrap?.querySelector( 'select' );
		if ( ! select ) {
			return;
		}
		const buttons = [ ...group.querySelectorAll( '.fd-swatch' ) ];
		const current = wrap.querySelector( '[data-fd-swatch-current]' );

		const sync = () => {
			const available = new Set( [ ...select.options ].map( ( o ) => o.value ) );
			const value = select.value;
			let tabbable = null;
			buttons.forEach( ( b ) => {
				const v = b.dataset.value;
				const on = v === value;
				const unavailable = ! available.has( v );
				b.setAttribute( 'aria-checked', String( on ) );
				b.classList.toggle( 'is-unavailable', unavailable );
				if ( unavailable ) {
					b.setAttribute( 'aria-disabled', 'true' );
				} else {
					b.removeAttribute( 'aria-disabled' );
				}
				b.tabIndex = -1;
				if ( on || ( ! tabbable && ! unavailable ) ) {
					tabbable = on ? b : tabbable || b;
				}
			} );
			( buttons.find( ( b ) => b.getAttribute( 'aria-checked' ) === 'true' ) || tabbable )?.setAttribute( 'tabindex', '0' );
			if ( current ) {
				const sel = buttons.find( ( b ) => b.dataset.value === value );
				current.textContent = sel ? sel.dataset.name : '';
			}
		};

		const choose = ( button ) => {
			if ( button.getAttribute( 'aria-disabled' ) === 'true' ) {
				return;
			}
			// Volver a pulsar el elegido lo deselecciona (equivale a "Borrar").
			select.value = select.value === button.dataset.value ? '' : button.dataset.value;
			select.dispatchEvent( new Event( 'change', { bubbles: true } ) );
			sync();
		};

		group.addEventListener( 'click', ( e ) => {
			const b = e.target.closest( '.fd-swatch' );
			if ( b ) {
				choose( b );
			}
		} );
		group.addEventListener( 'keydown', ( e ) => {
			if ( ! [ 'ArrowRight', 'ArrowLeft', 'ArrowDown', 'ArrowUp' ].includes( e.key ) ) {
				return;
			}
			const enabled = buttons.filter( ( b ) => b.getAttribute( 'aria-disabled' ) !== 'true' );
			const i = enabled.indexOf( document.activeElement );
			if ( i < 0 ) {
				return;
			}
			e.preventDefault();
			const dir = e.key === 'ArrowRight' || e.key === 'ArrowDown' ? 1 : -1;
			const next = enabled[ ( i + dir + enabled.length ) % enabled.length ];
			next.focus();
			select.value = next.dataset.value;
			select.dispatchEvent( new Event( 'change', { bubbles: true } ) );
			sync();
		} );

		// WooCommerce quita/agrega opciones según las demás elecciones y resetea con "Borrar".
		new MutationObserver( sync ).observe( select, { childList: true } );
		select.addEventListener( 'change', sync );
		const form = select.closest( 'form' );
		if ( form && window.jQuery ) {
			window.jQuery( form ).on( 'reset_data check_variations woocommerce_update_variation_values', () => setTimeout( sync, 0 ) );
		}
		sync();
	} );
}
