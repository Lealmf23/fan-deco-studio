/**
 * Buscador en vivo: sugiere hasta 5 productos mientras se escribe, usando la API pública de WooCommerce
 * (Store API: /wc/store/v1/products). Combobox accesible (ARIA), teclado completo, sin dependencias.
 * Si la API no responde, no pasa nada: el formulario sigue buscando normalmente.
 */
const MIN_CHARS = 2;
const DELAY = 220;

function apiUrl( query ) {
	const link = document.querySelector( 'link[rel="https://api.w.org/"]' );
	const root = link ? link.href : '/wp-json/';
	const url = new URL( root + 'wc/store/v1/products', window.location.href );
	url.searchParams.set( 'search', query );
	url.searchParams.set( 'per_page', '5' );
	return url;
}

const decode = ( html ) => {
	const t = document.createElement( 'textarea' );
	t.innerHTML = String( html || '' );
	return t.value;
};

function price( prices ) {
	if ( ! prices || prices.price === undefined ) {
		return '';
	}
	const minor = Number( prices.currency_minor_unit ?? 2 );
	const value = Number( prices.price ) / 10 ** minor;
	const parts = value.toFixed( minor ).split( '.' );
	parts[ 0 ] = parts[ 0 ].replace( /\B(?=(\d{3})+(?!\d))/g, prices.currency_thousand_separator ?? '.' );
	return `${ decode( prices.currency_prefix ?? prices.currency_symbol ?? '' ) }${ parts.join( prices.currency_decimal_separator ?? ',' ) }${ decode( prices.currency_suffix ?? '' ) }`;
}

export function initSearch() {
	let uid = 0;
	document.querySelectorAll( 'form[role="search"]' ).forEach( ( form ) => {
		const input = form.querySelector( 'input[type="search"]' );
		if ( ! input || form.dataset.fdLive || ! form.querySelector( 'input[name="post_type"][value="product"]' ) ) {
			return; // solo en formularios de productos
		}
		form.dataset.fdLive = '1';
		const id = `fd-live-${ ++uid }`;
		const box = document.createElement( 'div' );
		box.className = 'fd-live';
		box.id = id;
		box.setAttribute( 'role', 'listbox' );
		box.setAttribute( 'aria-label', 'Sugerencias de productos' );
		box.hidden = true;
		form.append( box );
		input.setAttribute( 'role', 'combobox' );
		input.setAttribute( 'aria-autocomplete', 'list' );
		input.setAttribute( 'aria-controls', id );
		input.setAttribute( 'aria-expanded', 'false' );

		let timer;
		let controller;
		let options = [];
		let active = -1;

		const close = () => {
			box.hidden = true;
			input.setAttribute( 'aria-expanded', 'false' );
			input.removeAttribute( 'aria-activedescendant' );
			active = -1;
		};
		const setActive = ( n ) => {
			options.forEach( ( o, i ) => o.setAttribute( 'aria-selected', String( i === n ) ) );
			active = n;
			if ( n >= 0 ) {
				input.setAttribute( 'aria-activedescendant', options[ n ].id );
				options[ n ].scrollIntoView( { block: 'nearest' } );
			} else {
				input.removeAttribute( 'aria-activedescendant' );
			}
		};
		const render = ( products, q ) => {
			box.textContent = '';
			options = [];
			if ( ! products.length ) {
				const p = document.createElement( 'p' );
				p.className = 'fd-live__empty';
				p.textContent = `No encontramos productos para “${ q }”.`;
				box.append( p );
			}
			products.forEach( ( pr, i ) => {
				const a = document.createElement( 'a' );
				a.className = 'fd-live__item';
				a.href = pr.permalink;
				a.id = `${ id }-${ i }`;
				a.setAttribute( 'role', 'option' );
				a.setAttribute( 'aria-selected', 'false' );
				const img = pr.images && pr.images[ 0 ];
				if ( img && ( img.thumbnail || img.src ) ) {
					const im = document.createElement( 'img' );
					im.src = img.thumbnail || img.src;
					im.alt = '';
					im.loading = 'lazy';
					im.width = 48;
					im.height = 48;
					a.append( im );
				}
				const name = document.createElement( 'span' );
				name.className = 'fd-live__name';
				name.textContent = decode( pr.name );
				const pri = document.createElement( 'span' );
				pri.className = 'fd-live__price';
				pri.textContent = price( pr.prices );
				a.append( name, pri );
				box.append( a );
				options.push( a );
			} );
			const all = document.createElement( 'a' );
			all.className = 'fd-live__all';
			all.setAttribute( 'role', 'option' );
			all.id = `${ id }-all`;
			all.setAttribute( 'aria-selected', 'false' );
			const u = new URL( form.action, window.location.href );
			u.searchParams.set( 's', q );
			u.searchParams.set( 'post_type', 'product' );
			all.href = u.toString();
			all.textContent = 'Ver todos los resultados';
			box.append( all );
			options.push( all );
			box.hidden = false;
			input.setAttribute( 'aria-expanded', 'true' );
			active = -1;
		};

		const search = async () => {
			const q = input.value.trim();
			if ( controller ) {
				controller.abort();
			}
			if ( q.length < MIN_CHARS ) {
				close();
				return;
			}
			controller = new AbortController();
			try {
				const res = await fetch( apiUrl( q ), { signal: controller.signal, headers: { Accept: 'application/json' } } );
				if ( ! res.ok ) {
					return close();
				}
				const data = await res.json();
				if ( Array.isArray( data ) && input.value.trim() === q ) {
					render( data, q );
				}
			} catch ( e ) {
				if ( e.name !== 'AbortError' ) {
					close(); // sin API: el formulario sigue funcionando normal
				}
			}
		};

		input.addEventListener( 'input', () => {
			clearTimeout( timer );
			timer = setTimeout( search, DELAY );
		} );
		input.addEventListener( 'keydown', ( e ) => {
			if ( box.hidden || ! options.length ) {
				return;
			}
			if ( e.key === 'ArrowDown' ) {
				e.preventDefault();
				setActive( ( active + 1 ) % options.length );
			} else if ( e.key === 'ArrowUp' ) {
				e.preventDefault();
				setActive( ( active - 1 + options.length ) % options.length );
			} else if ( e.key === 'Enter' && active >= 0 ) {
				e.preventDefault();
				options[ active ].click();
			} else if ( e.key === 'Escape' ) {
				e.stopPropagation();
				close();
			}
		} );
		document.addEventListener( 'click', ( e ) => {
			if ( ! form.contains( e.target ) ) {
				close();
			}
		} );
		form.addEventListener( 'focusout', ( e ) => {
			if ( ! form.contains( e.relatedTarget ) ) {
				setTimeout( close, 120 );
			}
		} );
	} );
}
