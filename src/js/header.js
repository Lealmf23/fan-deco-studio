const FOCUSABLE = 'a[href], button:not([disabled]), input:not([disabled]), [tabindex]:not([tabindex="-1"])';

export function initHeader() {
	const header = document.querySelector( '[data-fd-header]' );
	const drawer = document.querySelector( '[data-fd-drawer]' );
	const toggle = document.querySelector( '[data-fd-menu-toggle]' );
	const searchToggle = document.querySelector( '[data-fd-search-toggle]' );
	const searchPanel = document.querySelector( '[data-fd-search-panel]' );

	// Header sticky: sombra al hacer scroll.
	if ( header ) {
		const onScroll = () => header.classList.toggle( 'is-scrolled', window.scrollY > 8 );
		onScroll();
		window.addEventListener( 'scroll', onScroll, { passive: true } );
	}

	// Menú móvil accesible: aria-expanded, inert, Esc, foco atrapado, bloqueo de scroll.
	if ( drawer && toggle ) {
		const setOpen = ( open ) => {
			drawer.dataset.open = String( open );
			drawer.inert = ! open;
			toggle.setAttribute( 'aria-expanded', String( open ) );
			toggle.setAttribute( 'aria-label', open ? 'Cerrar menú' : 'Abrir menú' );
			document.body.classList.toggle( 'fd-no-scroll', open );
			if ( open ) {
				drawer.querySelector( FOCUSABLE )?.focus();
			} else {
				toggle.focus( { preventScroll: true } );
			}
		};
		toggle.addEventListener( 'click', () => setOpen( drawer.dataset.open !== 'true' ) );
		drawer.querySelectorAll( '[data-fd-menu-close]' ).forEach( ( el ) => el.addEventListener( 'click', () => setOpen( false ) ) );
		drawer.addEventListener( 'click', ( e ) => {
			if ( e.target.closest( '.fd-menu--mobile a' ) ) {
				setOpen( false );
			}
		} );
		document.addEventListener( 'keydown', ( e ) => {
			if ( drawer.dataset.open !== 'true' ) {
				return;
			}
			if ( e.key === 'Escape' ) {
				setOpen( false );
			} else if ( e.key === 'Tab' ) {
				const items = [ ...drawer.querySelectorAll( FOCUSABLE ) ].filter( ( el ) => el.offsetParent !== null );
				if ( ! items.length ) {
					return;
				}
				const first = items[ 0 ];
				const last = items[ items.length - 1 ];
				if ( e.shiftKey && document.activeElement === first ) {
					e.preventDefault();
					last.focus();
				} else if ( ! e.shiftKey && document.activeElement === last ) {
					e.preventDefault();
					first.focus();
				}
			}
		} );
		window.matchMedia( '(min-width: 1024px)' ).addEventListener( 'change', ( e ) => e.matches && drawer.dataset.open === 'true' && setOpen( false ) );
	}

	// Buscador desplegable.
	if ( searchToggle && searchPanel ) {
		searchToggle.addEventListener( 'click', () => {
			const open = searchPanel.hidden;
			searchPanel.hidden = ! open;
			searchToggle.setAttribute( 'aria-expanded', String( open ) );
			if ( open ) {
				searchPanel.querySelector( 'input[type="search"]' )?.focus();
			}
		} );
		searchPanel.addEventListener( 'keydown', ( e ) => {
			if ( e.key === 'Escape' ) {
				searchPanel.hidden = true;
				searchToggle.setAttribute( 'aria-expanded', 'false' );
				searchToggle.focus();
			}
		} );
	}
}
