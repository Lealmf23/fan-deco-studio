/**
 * Vista previa en vivo de los ajustes de diseño (Personalizar > Fan Deco Studio).
 * Replica fandeco_design_build_css() de inc/design.php: mismo orden, mismos redondeos, misma validación.
 * Hay una prueba de paridad (scripts/design-parity.mjs) que compara ambas salidas.
 */
( function ( root ) {
	'use strict';

	function num( n ) {
		var s = n.toFixed( 4 ).replace( /0+$/, '' ).replace( /\.$/, '' );
		return s === '-0' || s === '' ? '0' : s;
	}

	function normalize( f, raw ) {
		var v;
		if ( f.type === 'color' ) {
			v = String( raw === undefined || raw === null ? '' : raw ).trim();
			if ( /^#([0-9a-f]{3}|[0-9a-f]{6})$/i.test( v ) ) {
				return v.toLowerCase();
			}
			return v === '' ? '' : null;
		}
		if ( f.type === 'range' ) {
			v = parseFloat( raw );
			if ( isNaN( v ) || ! isFinite( raw ) ) {
				return null;
			}
			return String( Math.max( f.min, Math.min( f.max, v ) ) );
		}
		if ( f.type === 'select' ) {
			return f.choices.indexOf( String( raw ) ) !== -1 ? String( raw ) : null;
		}
		if ( f.type === 'toggle' ) {
			return ! raw || String( raw ) === '0' ? '0' : '1';
		}
		return null;
	}

	function effective( f, raw ) {
		if ( raw === undefined || raw === null ) {
			raw = f.default; // sin valor guardado: el de por defecto (igual que en PHP)
		}
		var n = normalize( f, raw );
		return n === null ? normalize( f, f.default ) : n;
	}

	function buildCss( schema, get ) {
		var desktop = [];
		var mobile = [];
		Object.keys( schema ).forEach( function ( key ) {
			var f = schema[ key ];
			if ( f.type === 'toggle' ) {
				return;
			}
			var val = effective( f, get( key ) );
			var def = normalize( f, f.default );
			if ( val === def || val === '' ) {
				return;
			}
			var out;
			if ( f.type === 'range' ) {
				var n = parseFloat( val );
				if ( f.emit === 'ratio' ) {
					n = n / 100;
				}
				out = num( n );
			} else if ( f.type === 'select' && f.map ) {
				out = f.map[ val ];
			} else {
				out = val;
			}
			if ( key === 'l_gutter' && parseFloat( val ) <= 0 ) {
				return;
			}
			Object.keys( f.vars ).forEach( function ( v ) {
				var decl = v + ':' + f.vars[ v ].replace( '{v}', out );
				( f.media === 'mobile' ? mobile : desktop ).push( decl );
			} );
		} );
		var css = '';
		if ( desktop.length ) {
			css += ':root{' + desktop.join( ';' ) + '}';
		}
		if ( mobile.length ) {
			css += '@media (max-width:767px){:root{' + mobile.join( ';' ) + '}}';
		}
		return css;
	}

	function bodyClasses( schema, get ) {
		var out = [];
		Object.keys( schema ).forEach( function ( key ) {
			var f = schema[ key ];
			if ( f.type === 'toggle' && effective( f, get( key ) ) === '0' ) {
				out.push( f.class_off );
			}
		} );
		return out;
	}

	if ( typeof module !== 'undefined' && module.exports ) {
		module.exports = { buildCss: buildCss, bodyClasses: bodyClasses };
		return;
	}

	// Navegador: vista previa del Personalizador.
	var wp = root.wp;
	var data = root.fandecoDesign;
	if ( ! wp || ! wp.customize || ! data ) {
		return;
	}
	var schema = data.schema;
	var get = function ( key ) {
		var s = wp.customize( 'fandeco_d_' + key );
		return s ? s() : undefined;
	};
	var offClasses = Object.keys( schema ).filter( function ( k ) { return schema[ k ].type === 'toggle'; } ).map( function ( k ) { return schema[ k ].class_off; } );
	var apply = function () {
		var el = document.getElementById( 'fd-design' );
		if ( ! el ) {
			el = document.createElement( 'style' );
			el.id = 'fd-design';
			document.head.appendChild( el );
		}
		el.textContent = buildCss( schema, get );
		var on = bodyClasses( schema, get );
		offClasses.forEach( function ( c ) { document.body.classList.toggle( c, on.indexOf( c ) !== -1 ); } );
	};
	wp.customize.bind( 'preview-ready', function () {
		Object.keys( schema ).forEach( function ( key ) {
			wp.customize( 'fandeco_d_' + key, function ( setting ) {
				setting.bind( apply );
			} );
		} );
	} );
}( typeof window !== 'undefined' ? window : globalThis ) );
