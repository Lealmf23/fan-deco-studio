// Extrae del CSS del tema solo las reglas cuyos selectores usan ciertas clases (con postcss).
import { readFileSync } from 'node:fs';
import postcss from 'postcss';

export function makeFilter( { want, deny, strip = [], fontFamily = 'DM Sans' } ) {
	const keepSelector = ( sel ) => ( sel.trim() === ':root' ? true : want.test( sel ) && ! deny.test( sel ) );
	const clean = ( sel ) => strip.reduce( ( s, re ) => s.replace( re, '' ), sel );
	function filterRoot( root ) {
		const out = postcss.root();
		root.each( ( node ) => {
			if ( node.type === 'rule' ) {
				const sels = node.selectors.filter( keepSelector );
				if ( sels.length ) {
					const r = node.clone();
					r.selectors = sels.map( clean );
					out.append( r );
				}
			} else if ( node.type === 'atrule' ) {
				if ( node.name === 'font-face' ) {
					if ( fontFamily && node.toString().includes( fontFamily ) ) out.append( node.clone() );
				} else if ( node.name === 'keyframes' ) {
					if ( /^fd-/.test( node.params ) ) out.append( node.clone() );
				} else if ( [ 'media', 'supports', 'layer' ].includes( node.name ) && node.nodes ) {
					const inner = filterRoot( node );
					if ( inner.nodes.length ) {
						if ( node.name === 'layer' ) {
							inner.each( ( n ) => out.append( n.clone() ) ); // sin capa: las reglas valen igual en cualquier tema
						} else {
							const a = node.clone( { nodes: [] } );
							inner.each( ( n ) => a.append( n.clone() ) );
							out.append( a );
						}
					}
				}
			}
		} );
		return out;
	}
	return ( file ) => filterRoot( postcss.parse( readFileSync( file, 'utf8' ) ) ).toString();
}

/** Tokens del tema (@theme static { ... }) como :root normal. */
export function tokensAsRoot() {
	return readFileSync( 'src/css/tokens.css', 'utf8' ).replace( /@theme static\s*\{/, ':root {' );
}
