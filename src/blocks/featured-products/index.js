import { __ } from '@wordpress/i18n';
import { RangeControl } from '@wordpress/components';
import { useSelect } from '@wordpress/data';
import { decodeEntities } from '@wordpress/html-entities';
import { registerFandecoBlock, ProductPicker, PanelBody, SelectControl, ToggleControl, TextControl, TextareaControl } from '../shared/index.js';
import metadata from './block.json';

function Controls( { attributes, setAttributes } ) {
	const terms = useSelect( ( select ) => select( 'core' ).getEntityRecords( 'taxonomy', 'product_cat', { per_page: 100 } ), [] );
	return (
		<>
			<PanelBody title={ __( 'Productos', 'fan-deco-studio' ) }>
				<SelectControl
					label={ __( 'Origen', 'fan-deco-studio' ) }
					value={ attributes.source }
					options={ [
						{ value: 'featured', label: __( 'Marcados como destacados', 'fan-deco-studio' ) },
						{ value: 'latest', label: __( 'Más recientes', 'fan-deco-studio' ) },
						{ value: 'sale', label: __( 'En oferta', 'fan-deco-studio' ) },
						{ value: 'category', label: __( 'De una categoría', 'fan-deco-studio' ) },
						{ value: 'manual', label: __( 'Selección manual', 'fan-deco-studio' ) },
					] }
					onChange={ ( source ) => setAttributes( { source } ) }
				/>
				{ attributes.source === 'category' && (
					<SelectControl
						label={ __( 'Categoría', 'fan-deco-studio' ) }
						value={ String( attributes.categoryId ) }
						options={ [ { value: '0', label: __( '— Elegir —', 'fan-deco-studio' ) }, ...( terms || [] ).map( ( t ) => ( { value: String( t.id ), label: decodeEntities( t.name ) } ) ) ] }
						onChange={ ( v ) => setAttributes( { categoryId: Number( v ) } ) }
					/>
				) }
				{ attributes.source === 'manual' && <ProductPicker ids={ attributes.productIds } onChange={ ( productIds ) => setAttributes( { productIds } ) } /> }
				{ attributes.source !== 'manual' && <RangeControl label={ __( 'Cantidad', 'fan-deco-studio' ) } min={ 1 } max={ 12 } value={ attributes.count } onChange={ ( count ) => setAttributes( { count } ) } /> }
			</PanelBody>
			<PanelBody title={ __( 'Diseño', 'fan-deco-studio' ) } initialOpen={ false }>
				<SelectControl label={ __( 'Disposición', 'fan-deco-studio' ) } value={ attributes.layout } options={ [ { value: 'carousel', label: __( 'Carrusel', 'fan-deco-studio' ) }, { value: 'editorial', label: __( 'Editorial: título a la izquierda y carrusel', 'fan-deco-studio' ) }, { value: 'grid', label: __( 'Grilla', 'fan-deco-studio' ) } ] } onChange={ ( layout ) => setAttributes( { layout } ) } />
				{ attributes.layout === 'editorial' && (
					<>
						<TextControl label={ __( 'Título', 'fan-deco-studio' ) } value={ attributes.title } onChange={ ( title ) => setAttributes( { title } ) } />
						<TextareaControl label={ __( 'Descripción', 'fan-deco-studio' ) } value={ attributes.description } onChange={ ( description ) => setAttributes( { description } ) } />
						<TextControl label={ __( 'Texto del enlace', 'fan-deco-studio' ) } value={ attributes.linkText } onChange={ ( linkText ) => setAttributes( { linkText } ) } />
						<TextControl label={ __( 'URL del enlace', 'fan-deco-studio' ) } type="url" value={ attributes.linkUrl } onChange={ ( linkUrl ) => setAttributes( { linkUrl } ) } />
					</>
				) }
				<ToggleControl label={ __( 'Mostrar botón de agregar al carrito', 'fan-deco-studio' ) } checked={ attributes.showAddToCart } onChange={ ( showAddToCart ) => setAttributes( { showAddToCart } ) } />
			</PanelBody>
		</>
	);
}

registerFandecoBlock( metadata, Controls );
