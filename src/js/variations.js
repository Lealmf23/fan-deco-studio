/**
 * Puente de eventos de variaciones: expone el cambio de variación como evento DOM estándar para que
 * cualquier componente de precio/cuotas (snippet existente o futuro) reaccione sin depender de jQuery.
 * NO calcula precios, cuotas ni descuentos: solo reenvía lo que WooCommerce ya calculó.
 *
 *   document.addEventListener( 'fandeco:variation', ( e ) => {
 *     const { variationId, price, regularPrice, inStock, form } = e.detail; // variationId 0 = sin variación elegida
 *   } );
 */
export function initVariationEvents() {
	const $ = window.jQuery;
	if ( ! $ ) {
		return;
	}
	const emit = ( form, detail ) => {
		document.dispatchEvent( new CustomEvent( 'fandeco:variation', { detail: { form, ...detail } } ) );
	};
	$( document ).on( 'found_variation', '.variations_form', function ( event, variation ) {
		emit( this, {
			variationId: Number( variation.variation_id ) || 0,
			price: variation.display_price ?? null,
			regularPrice: variation.display_regular_price ?? null,
			inStock: !! variation.is_in_stock,
			attributes: variation.attributes || {},
		} );
	} );
	$( document ).on( 'reset_data hide_variation', '.variations_form', function () {
		emit( this, { variationId: 0, price: null, regularPrice: null, inStock: false, attributes: {} } );
	} );
}
