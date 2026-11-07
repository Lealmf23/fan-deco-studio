/* Selector de foto (biblioteca de medios) para la "foto encendida", en producto y variaciones. */
jQuery( function ( $ ) {
	var frame;
	$( document ).on( 'click', '[data-fdl-select]', function ( e ) {
		e.preventDefault();
		var $picker = $( this ).closest( '[data-fdl-picker]' );
		frame = wp.media( { title: 'Foto encendida', button: { text: 'Usar esta foto' }, library: { type: 'image' }, multiple: false } );
		frame.on( 'select', function () {
			var att = frame.state().get( 'selection' ).first().toJSON();
			var url = ( att.sizes && att.sizes.thumbnail ) ? att.sizes.thumbnail.url : att.url;
			$picker.find( '[data-fdl-input]' ).val( att.id ).trigger( 'change' );
			$picker.find( '[data-fdl-preview]' ).attr( 'src', url ).show();
			$picker.find( '[data-fdl-clear]' ).show();
			// Marca la variación como modificada para que WooCommerce permita guardarla.
			$picker.closest( '.woocommerce_variation' ).addClass( 'variation-needs-update' );
			$( '.save-variation-changes, .cancel-variation-changes' ).prop( 'disabled', false );
		} );
		frame.open();
	} );
	$( document ).on( 'click', '[data-fdl-clear]', function ( e ) {
		e.preventDefault();
		var $picker = $( this ).closest( '[data-fdl-picker]' );
		$picker.find( '[data-fdl-input]' ).val( '' ).trigger( 'change' );
		$picker.find( '[data-fdl-preview]' ).hide().attr( 'src', '' );
		$( this ).hide();
		$picker.closest( '.woocommerce_variation' ).addClass( 'variation-needs-update' );
		$( '.save-variation-changes, .cancel-variation-changes' ).prop( 'disabled', false );
	} );
} );
