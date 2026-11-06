/* Puntos de color de la tarjeta: al pasar el mouse (o tocar/foco) se ve la foto de esa variante; al salir vuelve la foto elegida. */
( () => {
	// La foto de la variante es una capa encima de la foto principal: aparece y se va con fundido (sin saltos).
	const layerOf = ( win ) => {
		const media = win.querySelector( '.fd-product-card__media' );
		if ( ! media ) {
			return null;
		}
		let layer = media.querySelector( '.fd-product-card__variant' );
		if ( ! layer ) {
			layer = document.createElement( 'img' );
			layer.className = 'fd-product-card__variant';
			layer.alt = '';
			layer.decoding = 'async';
			media.appendChild( layer );
		}
		return layer;
	};
	const swap = ( win, url ) => {
		const layer = layerOf( win );
		if ( ! layer ) {
			return;
		}
		if ( ! url ) {
			layer.classList.remove( 'is-on' );
			return;
		}
		const show = () => layer.classList.add( 'is-on' );
		if ( layer.getAttribute( 'src' ) === url && layer.complete ) {
			show();
			return;
		}
		layer.classList.remove( 'is-on' );
		const loader = new Image();
		loader.onload = () => {
			layer.src = url; // ya en caché: el fundido arranca sin parpadeo
			requestAnimationFrame( show );
		};
		loader.src = url;
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
