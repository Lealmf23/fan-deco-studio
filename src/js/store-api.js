/**
 * Utilidades comunes para hablar con la API pública de WooCommerce (Store API) y formatear importes.
 * Solo lectura: no modifica pedidos, productos ni configuración.
 */
export function apiUrl( path, params = {} ) {
	const link = document.querySelector( 'link[rel="https://api.w.org/"]' );
	const root = link ? link.href : '/wp-json/';
	const url = new URL( root + 'wc/store/v1/' + path, window.location.href );
	Object.entries( params ).forEach( ( [ k, v ] ) => url.searchParams.set( k, v ) );
	return url;
}

/** Decodifica entidades HTML (&amp;, &uacute;…) devolviendo texto plano. */
export function decode( html ) {
	const t = document.createElement( 'textarea' );
	t.innerHTML = String( html || '' );
	return t.value;
}

/** Texto plano de un fragmento HTML (para descripciones: nunca se inserta HTML de la API). */
export function plainText( html ) {
	const doc = new DOMParser().parseFromString( String( html || '' ), 'text/html' );
	doc.querySelectorAll( 'script, style, template' ).forEach( ( n ) => n.remove() );
	return ( doc.body.textContent || '' ).replace( /\s+/g, ' ' ).trim();
}

/** Formatea un importe en unidades menores (string/number) con los datos de moneda de la API. */
export function formatMoney( amount, cur ) {
	if ( amount === undefined || amount === null || amount === '' || ! cur ) {
		return '';
	}
	const minor = Number( cur.currency_minor_unit ?? 2 );
	const value = Number( amount ) / 10 ** minor;
	const parts = value.toFixed( minor ).split( '.' );
	parts[ 0 ] = parts[ 0 ].replace( /\B(?=(\d{3})+(?!\d))/g, cur.currency_thousand_separator ?? '.' );
	return `${ decode( cur.currency_prefix ?? cur.currency_symbol ?? '' ) }${ parts.join( cur.currency_decimal_separator ?? ',' ) }${ decode( cur.currency_suffix ?? '' ) }`;
}

export async function getJson( url, options = {} ) {
	const res = await fetch( url, { credentials: 'same-origin', headers: { Accept: 'application/json' }, ...options } );
	if ( ! res.ok ) {
		throw new Error( `HTTP ${ res.status }` );
	}
	return res.json();
}
