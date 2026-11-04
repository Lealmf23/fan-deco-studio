import { __ } from '@wordpress/i18n';
import { FormTokenField } from '@wordpress/components';
import { useSelect } from '@wordpress/data';
import { decodeEntities } from '@wordpress/html-entities';
import { registerFandecoBlock, PanelBody, SelectControl, ToggleControl } from '../shared/index.js';
import metadata from './block.json';

function Controls( { attributes, setAttributes } ) {
	const terms = useSelect( ( select ) => select( 'core' ).getEntityRecords( 'taxonomy', 'product_cat', { per_page: 100, hide_empty: false } ), [] );
	const byName = new Map( ( terms || [] ).map( ( t ) => [ decodeEntities( t.name ), t.id ] ) );
	const byId = new Map( ( terms || [] ).map( ( t ) => [ t.id, decodeEntities( t.name ) ] ) );
	return (
		<PanelBody title={ __( 'Categorías', 'fan-deco-studio' ) }>
			{ terms === null ? <p>{ __( 'Cargando categorías…', 'fan-deco-studio' ) }</p> : terms.length === 0 ? (
				<p>{ __( 'No hay categorías de producto (¿WooCommerce está activo?).', 'fan-deco-studio' ) }</p>
			) : (
				<FormTokenField
					label={ __( 'Categorías a mostrar (en este orden)', 'fan-deco-studio' ) }
					value={ attributes.categoryIds.map( ( id ) => byId.get( id ) ).filter( Boolean ) }
					suggestions={ [ ...byName.keys() ] }
					onChange={ ( names ) => setAttributes( { categoryIds: names.map( ( n ) => byName.get( n ) ).filter( Boolean ) } ) }
					__experimentalExpandOnFocus
				/>
			) }
			<p className="description">{ __( 'Si no elegís ninguna, se muestran las primeras categorías principales con productos.', 'fan-deco-studio' ) }</p>
			<SelectControl label={ __( 'Columnas (desktop)', 'fan-deco-studio' ) } value={ String( attributes.columns ) } options={ [ '2', '3', '4' ].map( ( v ) => ( { value: v, label: v } ) ) } onChange={ ( v ) => setAttributes( { columns: Number( v ) } ) } />
			<ToggleControl label={ __( 'Mostrar descripción', 'fan-deco-studio' ) } checked={ attributes.showDescription } onChange={ ( showDescription ) => setAttributes( { showDescription } ) } />
			<ToggleControl label={ __( 'Mostrar cantidad de productos', 'fan-deco-studio' ) } checked={ attributes.showCount } onChange={ ( showCount ) => setAttributes( { showCount } ) } />
			<p className="description">{ __( 'La imagen, el nombre y la descripción se editan en Productos > Categorías.', 'fan-deco-studio' ) }</p>
		</PanelBody>
	);
}

registerFandecoBlock( metadata, Controls );
