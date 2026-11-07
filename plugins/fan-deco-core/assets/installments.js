/**
 * Actualiza "N cuotas sin interés de $X" y el precio con transferencia al elegir una variación.
 * Solo visual. Usa los eventos de variación de WooCommerce (jQuery).
 */
( function () {
	'use strict';

	function formatMoney( value, fmt ) {
		var parts = Math.abs( value ).toFixed( fmt.dec ).split( '.' );
		parts[ 0 ] = parts[ 0 ].replace( /\B(?=(\d{3})+(?!\d))/g, fmt.ts );
		return fmt.format.replace( '%1$s', fmt.sym ).replace( '%2$s', parts.join( fmt.ds ) );
	}

	function eligible( spec, variationId ) {
		if ( spec === 'all' ) {
			return true;
		}
		if ( spec === 'none' ) {
			return false;
		}
		try {
			return JSON.parse( spec ).indexOf( Number( variationId ) ) !== -1;
		} catch ( e ) {
			return false;
		}
	}

	function update( root, variationId, price ) {
		var fmt = JSON.parse( root.getAttribute( 'data-fmt' ) );
		var n = Number( root.getAttribute( 'data-n' ) );
		var pct = Number( root.getAttribute( 'data-pct' ) );
		var min = Number( root.getAttribute( 'data-min' ) );
		var line = root.querySelector( '[data-fd-inst-line]' );
		var tline = root.querySelector( '[data-fd-transfer-line]' );
		root.querySelectorAll( '[data-fd-inst-prefix]' ).forEach( function ( el ) {
			el.hidden = price !== null;
		} );
		if ( price === null ) { // sin variación: se restaura el estado "desde" renderizado en el servidor
			if ( root._initial ) {
				root._initial();
			}
			return;
		}
		var dec = fmt.dec;
		var f = Math.pow( 10, dec );
		line.hidden = ! ( price >= min && price > 0 );
		line.querySelector( '[data-fd-inst-amount]' ).textContent = formatMoney( Math.round( ( price / n ) * f ) / f, fmt );
		var show = pct > 0 && eligible( root.getAttribute( 'data-eligible' ), variationId );
		tline.hidden = ! show;
		if ( show ) {
			tline.querySelector( '[data-fd-transfer-amount]' ).textContent = formatMoney( Math.round( price * ( 1 - pct / 100 ) * f ) / f, fmt );
		}
	}

	function init() {
		var $ = window.jQuery;
		if ( ! $ ) {
			return;
		}
		document.querySelectorAll( '[data-fd-installments]' ).forEach( function ( root ) {
			var snapshot = root.innerHTML;
			var hiddenLine = root.querySelector( '[data-fd-inst-line]' ).hidden;
			var hiddenTr = root.querySelector( '[data-fd-transfer-line]' ).hidden;
			root._initial = function () {
				root.innerHTML = snapshot;
				root.querySelector( '[data-fd-inst-line]' ).hidden = hiddenLine;
				root.querySelector( '[data-fd-transfer-line]' ).hidden = hiddenTr;
			};
		} );
		$( document ).on( 'found_variation', '.variations_form', function ( event, variation ) {
			var price = variation && variation.display_price !== undefined ? Number( variation.display_price ) : null;
			document.querySelectorAll( '[data-fd-installments]' ).forEach( function ( root ) {
				update( root, variation ? variation.variation_id : 0, price );
			} );
		} );
		$( document ).on( 'reset_data hide_variation', '.variations_form', function () {
			document.querySelectorAll( '[data-fd-installments]' ).forEach( function ( root ) {
				update( root, 0, null );
			} );
		} );
	}

	if ( typeof module !== 'undefined' ) {
		module.exports = { formatMoney: formatMoney, eligible: eligible };
	}
	if ( typeof document !== 'undefined' ) {
		if ( document.readyState === 'loading' ) {
			document.addEventListener( 'DOMContentLoaded', init );
		} else {
			init();
		}
	}
}() );
