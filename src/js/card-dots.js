/* Puntos de color de la tarjeta: al pasar el mouse (o tocar/foco) se ve la foto de esa variante; al salir vuelve la foto elegida. */
( () => {
	const swap = ( win, url ) => {
		const img = win.querySelector( '.fd-product-card__media img:not(.fd-product-card__alt)' );
		if ( ! img ) {
			return;
		}
		if ( ! img.dataset.fdOrigSrc ) {
			img.dataset.fdOrigSrc = img.currentSrc || img.src;
			img.dataset.fdOrigSrcset = img.getAttribute( 'srcset' ) || '';
		}
		if ( url ) {
			img.removeAttribute( 'srcset' );
			img.src = url;
			win.classList.add( 'is-variant' );
		} else {
			if ( img.dataset.fdOrigSrcset ) {
				img.setAttribute( 'srcset', img.dataset.fdOrigSrcset );
			}
			img.src = img.dataset.fdOrigSrc;
			win.classList.remove( 'is-variant' );
		}
	};
	const dotOf = ( e ) => e.target.closest && e.target.closest( '.fd-dot[data-fd-img]' );
	const preview = ( e ) => {
		const dot = dotOf( e );
		const win = dot && dot.closest( '.fd-product-card__window' );
		if ( win ) {
			swap( win, dot.dataset.fdImg );
		}
	};
	const restore = ( e ) => {
		const win = e.target.closest && e.target.closest( '.fd-product-card__window' );
		if ( ! win ) {
			return;
		}
		const picked = win.querySelector( '.fd-dot.is-active' );
		swap( win, picked ? picked.dataset.fdImg : '' );
	};
	document.addEventListener( 'mouseover', preview );
	document.addEventListener( 'focusin', preview );
	document.addEventListener( 'mouseout', ( e ) => {
		if ( dotOf( e ) && ! ( e.relatedTarget && e.relatedTarget.closest && e.relatedTarget.closest( '.fd-dot[data-fd-img]' ) ) ) {
			restore( e );
		}
	} );
	document.addEventListener( 'click', ( e ) => {
		const dot = dotOf( e );
		if ( ! dot ) {
			return;
		}
		e.preventDefault();
		const same = dot.classList.contains( 'is-active' );
		dot.parentNode.querySelectorAll( '.fd-dot.is-active' ).forEach( ( d ) => d.classList.remove( 'is-active' ) );
		if ( ! same ) {
			dot.classList.add( 'is-active' );
		}
		swap( dot.closest( '.fd-product-card__window' ), same ? '' : dot.dataset.fdImg );
	} );
	document.addEventListener( 'keydown', ( e ) => {
		if ( ( 'Enter' === e.key || ' ' === e.key ) && dotOf( e ) ) {
			e.preventDefault();
			dotOf( e ).click();
		}
	} );
} )();
