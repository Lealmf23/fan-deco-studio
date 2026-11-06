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
			</PanelBody>
			<PanelBody title={ __( 'Tarjetas', 'fan-deco-studio' ) } initialOpen={ false }>
				<ToggleControl label={ __( 'Botón "Ver producto" (lleva a la ficha)', 'fan-deco-studio' ) } checked={ attributes.showAddToCart } onChange={ ( showAddToCart ) => setAttributes( { showAddToCart } ) } />
				<ToggleControl label={ __( 'Puntos de color (solo si tiene variantes de color)', 'fan-deco-studio' ) } checked={ attributes.showSwatches } onChange={ ( showSwatches ) => setAttributes( { showSwatches } ) } />
				<ToggleControl label={ __( 'Cambiar de foto al pasar el mouse', 'fan-deco-studio' ) } checked={ attributes.hoverSwap } onChange={ ( hoverSwap ) => setAttributes( { hoverSwap } ) } />
				<ToggleControl label={ __( 'Mostrar también el precio con transferencia', 'fan-deco-studio' ) } help={ __( 'Se ve solo si el descuento por transferencia está activo en Fan Deco Core. Las cuotas no se muestran en las tarjetas.', 'fan-deco-studio' ) } checked={ attributes.showTransfer } onChange={ ( showTransfer ) => setAttributes( { showTransfer } ) } />
				<SelectControl label={ __( 'Proporción de la foto', 'fan-deco-studio' ) } value={ attributes.ratio } options={ [ { value: '4/5', label: __( 'Vertical 4:5', 'fan-deco-studio' ) }, { value: '3/4', label: __( 'Vertical 3:4', 'fan-deco-studio' ) }, { value: '1/1', label: __( 'Cuadrada', 'fan-deco-studio' ) } ] } onChange={ ( ratio ) => setAttributes( { ratio } ) } />
				<SelectControl label={ __( 'Letra del título', 'fan-deco-studio' ) } value={ attributes.titleFont } options={ [ { value: 'default', label: __( 'La del sitio', 'fan-deco-studio' ) }, { value: 'sans', label: __( 'DM Sans', 'fan-deco-studio' ) }, { value: 'serif', label: __( 'Georgia cursiva', 'fan-deco-studio' ) } ] } onChange={ ( titleFont ) => setAttributes( { titleFont } ) } />
				<RangeControl label={ __( 'Tamaño del título (px, 0 = automático)', 'fan-deco-studio' ) } min={ 0 } max={ 48 } value={ attributes.titleSize } onChange={ ( titleSize ) => setAttributes( { titleSize: titleSize || 0 } ) } />
				<RangeControl label={ __( 'Tamaño del precio (px, 0 = automático)', 'fan-deco-studio' ) } min={ 0 } max={ 40 } value={ attributes.priceSize } onChange={ ( priceSize ) => setAttributes( { priceSize: priceSize || 0 } ) } />
			</PanelBody>
		</>
	);
}

registerFandecoBlock( metadata, Controls );
