/**
 * Interruptor de luz: alterna la foto encendida. Sigue a las variaciones de WooCommerce:
 *  - variación con foto encendida propia -> se usa esa;
 *  - variación con la misma foto principal -> se usa la del producto;
 *  - variación con foto propia pero sin foto encendida -> se oculta el interruptor (no se muestra una foto equivocada).
 */
( function () {
	'use strict';

	function init() {
		var sw = document.querySelector( '[data-fd-light-switch]' );
		if ( ! sw ) {
			return;
		}
		var gallery = sw.closest( '.woocommerce-product-gallery' );
		var label = sw.querySelector( '[data-fd-light-label]' );
		var mainId = Number( sw.getAttribute( 'data-main-id' ) );
		var overlay = function () {
			return gallery.querySelector( '[data-fd-lit]' );
		};
		var first = overlay();
		var defaults = first && ! first.hidden ? { src: first.getAttribute( 'src' ), srcset: first.getAttribute( 'srcset' ) || '' } : null;

		function paint( on ) {
			sw.setAttribute( 'aria-checked', String( on ) );
			gallery.classList.toggle( 'is-lit', on );
			label.textContent = sw.getAttribute( on ? 'data-on' : 'data-off' );
		}
		function setSource( data ) {
			var img = overlay();
			if ( ! img ) {
				return false;
			}
			if ( data && data.src ) {
				img.src = data.src;
				if ( data.srcset ) {
					img.setAttribute( 'srcset', data.srcset );
				} else {
					img.removeAttribute( 'srcset' );
				}
				img.hidden = false;
				sw.hidden = false;
				return true;
			}
			img.hidden = true;
			sw.hidden = true;
			paint( false );
			return false;
		}

		sw.addEventListener( 'click', function () {
			paint( sw.getAttribute( 'aria-checked' ) !== 'true' );
		} );

		var $ = window.jQuery;
		if ( $ ) {
			$( document ).on( 'found_variation', '.variations_form', function ( event, variation ) {
				var own = variation && variation.fandeco_lit;
				if ( own ) {
					setSource( own );
				} else if ( variation && Number( variation.image_id ) === mainId ) {
					setSource( defaults );
				} else {
					setSource( null ); // variación con otra foto y sin foto encendida
				}
			} );
			$( document ).on( 'reset_data hide_variation', '.variations_form', function () {
				setSource( defaults );
			} );
		}
	}

	if ( typeof document !== 'undefined' ) {
		if ( document.readyState === 'loading' ) {
			document.addEventListener( 'DOMContentLoaded', init );
		} else {
			init();
		}
	}
}() );
