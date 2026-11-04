/**
 * Vista rápida de producto y panel "Agregado al carrito".
 * Ambos usan <dialog> nativo (foco, Esc y fondo gestionados por el navegador) y los datos de la Store API de
 * WooCommerce. Nunca se inserta HTML de la API: todo se escribe con textContent. Si la API falla, el enlace al
 * producto sigue funcionando y no se rompe nada.
 */
import { apiUrl, decode, plainText, formatMoney, getJson } from './store-api.js';

const el = ( tag, cls, text ) => {
	const n = document.createElement( tag );
	if ( cls ) {
		n.className = cls;
	}
	if ( text !== undefined ) {
		n.textContent = text;
	}
	return n;
};

function openDialog( dlg ) {
	if ( typeof dlg.showModal === 'function' ) {
		if ( ! dlg.open ) {
			dlg.showModal();
		}
	} else {
		dlg.setAttribute( 'open', '' );
	}
}

function wireDialog( dlg ) {
	dlg.addEventListener( 'click', ( e ) => {
		// Clic en el fondo (fuera del contenido) o en un control de cierre.
		if ( e.target === dlg || e.target.closest( '[data-fd-dialog-close]' ) ) {
			dlg.close();
		}
	} );
}

/* ---------------- Vista rápida ---------------- */
function initQuickView() {
	const dlg = document.querySelector( '[data-fd-qv]' );
	if ( ! dlg ) {
		return;
	}
	wireDialog( dlg );
	const body = dlg.querySelector( '[data-fd-qv-body]' );
	let opener = null;
	let controller = null;

	dlg.addEventListener( 'close', () => {
		if ( controller ) {
			controller.abort();
		}
		if ( opener && document.contains( opener ) ) {
			opener.focus();
		}
	} );

	const render = ( p ) => {
		body.replaceChildren();
		const media = el( 'div', 'fd-qv__media' );
		const img = p.images && p.images[ 0 ];
		if ( img ) {
			const im = el( 'img' );
			im.src = img.src || img.thumbnail;
			im.alt = decode( img.alt || p.name );
			media.append( im );
		}
		const info = el( 'div', 'fd-qv__info' );
		info.append( el( 'h2', 'fd-qv__title', decode( p.name ) ) );
		const pr = p.prices || {};
		const price = el( 'p', 'fd-qv__price' );
		if ( pr.sale_price && pr.regular_price && pr.sale_price !== pr.regular_price ) {
			price.append( el( 'del', '', formatMoney( pr.regular_price, pr ) ), ' ', el( 'strong', '', formatMoney( pr.sale_price, pr ) ) );
		} else {
			price.append( el( 'strong', '', formatMoney( pr.price, pr ) ) );
		}
		info.append( price );
		const desc = plainText( p.short_description );
		if ( desc ) {
			info.append( el( 'p', 'fd-qv__desc', desc ) );
		}
		const actions = el( 'div', 'fd-qv__actions' );
		const simple = p.type === 'simple' && p.is_purchasable && p.is_in_stock;
		if ( simple ) {
			// El botón estándar de WooCommerce: su script lo maneja por delegación y dispara "added_to_cart".
			const add = el( 'a', 'fd-btn fd-btn--primary button add_to_cart_button ajax_add_to_cart', 'Agregar al carrito' );
			add.href = ( p.add_to_cart && p.add_to_cart.url ) || p.permalink;
			add.dataset.productId = String( p.id );
			add.dataset.quantity = '1';
			add.setAttribute( 'rel', 'nofollow' );
			add.addEventListener( 'click', () => dlg.close() );
			actions.append( add );
		} else if ( ! p.is_in_stock ) {
			actions.append( el( 'p', 'fd-qv__note', 'Sin stock por el momento.' ) );
		}
		const more = el( 'a', simple ? 'fd-qv__link' : 'fd-btn fd-btn--primary', simple ? 'Ver todos los detalles' : 'Elegir opciones' );
		more.href = p.permalink;
		actions.append( more );
		info.append( actions );
		body.append( media, info );
	};

	document.addEventListener( 'click', async ( e ) => {
		const btn = e.target.closest( '[data-fd-quickview]' );
		if ( ! btn ) {
			return;
		}
		e.preventDefault();
		opener = btn;
		if ( controller ) {
			controller.abort();
		}
		controller = new AbortController();
		body.replaceChildren( el( 'p', 'fd-qv__loading', 'Cargando…' ) );
		openDialog( dlg );
		try {
			const data = await getJson( apiUrl( 'products/' + encodeURIComponent( btn.dataset.fdQuickview ) ), { signal: controller.signal } );
			render( data );
		} catch ( err ) {
			if ( err.name === 'AbortError' ) {
				return;
			}
			// Sin API: ir a la ficha.
			const href = btn.dataset.href;
			if ( href ) {
				window.location.href = href;
			}
		}
	} );
}

/* ---------------- Panel "Agregado al carrito" ---------------- */
function initCartPanel() {
	const dlg = document.querySelector( '[data-fd-cartpanel]' );
	const $ = window.jQuery;
	if ( ! dlg || ! $ ) {
		return;
	}
	wireDialog( dlg );
	const list = dlg.querySelector( '[data-fd-cartpanel-items]' );
	const total = dlg.querySelector( '[data-fd-cartpanel-total]' );
	let controller = null;

	$( document.body ).on( 'added_to_cart', async ( _e, _fragments, _hash, $button ) => {
		const pid = $button && $button.length ? Number( $button.data( 'product_id' ) ) : 0;
		if ( controller ) {
			controller.abort();
		}
		controller = new AbortController();
		try {
			const cart = await getJson( apiUrl( 'cart' ), { signal: controller.signal } );
			const items = Array.isArray( cart.items ) ? cart.items : [];
			const item = items.find( ( i ) => i.id === pid ) || items[ items.length - 1 ];
			if ( ! item ) {
				return;
			}
			list.replaceChildren();
			const row = el( 'div', 'fd-cartpanel__item' );
			const im = item.images && item.images[ 0 ];
			if ( im ) {
				const img = el( 'img' );
				img.src = im.thumbnail || im.src;
				img.alt = '';
				img.width = 64;
				img.height = 64;
				row.append( img );
			}
			const txt = el( 'div', 'fd-cartpanel__text' );
			txt.append( el( 'strong', '', decode( item.name ) ) );
			( item.variation || [] ).forEach( ( v ) => txt.append( el( 'span', 'fd-cartpanel__var', `${ decode( v.attribute ) }: ${ decode( v.value ) }` ) ) );
			txt.append( el( 'span', 'fd-cartpanel__qty', `${ item.quantity } × ${ formatMoney( item.prices && item.prices.price, item.prices ) }` ) );
			row.append( txt );
			list.append( row );
			const t = cart.totals || {};
			total.textContent = formatMoney( t.total_items, t );
			openDialog( dlg );
		} catch ( err ) {
			// Sin API: queda el aviso estándar de WooCommerce y la animación del carrito.
		}
	} );
}

/* ---------------- Compartir: copiar enlace ---------------- */
function initShare() {
	document.addEventListener( 'click', async ( e ) => {
		const btn = e.target.closest( '[data-fd-share-copy]' );
		if ( ! btn ) {
			return;
		}
		const box = btn.closest( '[data-fd-share]' );
		const url = box ? box.dataset.url : window.location.href;
		const label = btn.querySelector( 'span' );
		const original = label.textContent;
		try {
			if ( navigator.share && window.matchMedia( '(pointer: coarse)' ).matches ) {
				await navigator.share( { url, title: box ? box.dataset.title : document.title } );
				return;
			}
			await navigator.clipboard.writeText( url );
			label.textContent = '¡Enlace copiado!';
		} catch ( err ) {
			if ( err && err.name === 'AbortError' ) {
				return; // el usuario cerró el menú de compartir
			}
			window.prompt( 'Copiá el enlace:', url ); // respaldo si el navegador bloquea el portapapeles
			return;
		}
		setTimeout( () => { label.textContent = original; }, 2000 );
	} );
}

export function initShopUi() {
	initShare();
	initQuickView();
	initCartPanel();
}
