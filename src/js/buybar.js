/**
 * Barra fija de compra (móvil). Se muestra cuando el botón real "Añadir al carrito" no está en pantalla.
 * Refleja el precio de la variación elegida (evento fandeco:variation) y el precio con transferencia del
 * plugin Fan Deco Core si existe. El botón dispara el botón real; si falta elegir variación, lleva a los selectores.
 */
export function initBuybar() {
	const bar = document.querySelector( '[data-fd-buybar]' );
	const real = document.querySelector( 'form.cart .single_add_to_cart_button' );
	if ( ! bar || ! real ) {
		return;
	}
	const form = real.closest( 'form.cart' );
	const priceEl = bar.querySelector( '[data-fd-buybar-price]' );
	const transferEl = bar.querySelector( '[data-fd-buybar-transfer]' );
	const initialPrice = priceEl.innerHTML;

	const io = new IntersectionObserver( ( [ entry ] ) => {
		bar.hidden = entry.isIntersecting;
	}, { threshold: 0 } );
	io.observe( real );

	const syncTransfer = () => {
		const line = document.querySelector( '[data-fd-transfer-line]:not([hidden]) [data-fd-transfer-amount]' );
		transferEl.hidden = ! line;
		transferEl.textContent = line ? `${ line.textContent.trim() } transf.` : '';
	};
	syncTransfer();

	const fmtOf = () => {
		const el = document.querySelector( '[data-fd-installments]' );
		try { return el ? JSON.parse( el.getAttribute( 'data-fmt' ) ) : null; } catch ( e ) { return null; }
	};
	const money = ( v ) => {
		const f = fmtOf();
		if ( ! f ) {
			return v.toLocaleString( 'es-AR', { style: 'currency', currency: 'ARS', maximumFractionDigits: 0 } );
		}
		const p = Math.abs( v ).toFixed( f.dec ).split( '.' );
		p[ 0 ] = p[ 0 ].replace( /\B(?=(\d{3})+(?!\d))/g, f.ts );
		return f.format.replace( '%1$s', f.sym ).replace( '%2$s', p.join( f.ds ) );
	};
	document.addEventListener( 'fandeco:variation', ( e ) => {
		const price = e.detail && e.detail.price;
		if ( price === null || price === undefined ) {
			priceEl.innerHTML = initialPrice;
		} else {
			priceEl.textContent = money( Number( price ) );
		}
		setTimeout( syncTransfer, 0 ); // el plugin de cuotas actualiza su línea en el mismo evento
	} );

	bar.querySelector( '[data-fd-buybar-add]' ).addEventListener( 'click', () => {
		const needs = real.classList.contains( 'disabled' ) || real.classList.contains( 'wc-variation-selection-needed' );
		if ( needs ) {
			form.scrollIntoView( { behavior: matchMedia( '(prefers-reduced-motion: reduce)' ).matches ? 'auto' : 'smooth', block: 'center' } );
			form.classList.add( 'is-attention' );
			setTimeout( () => form.classList.remove( 'is-attention' ), 1200 );
		} else {
			real.click();
		}
	} );
}
