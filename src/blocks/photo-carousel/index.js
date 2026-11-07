import { __ } from '@wordpress/i18n';
import { registerFandecoBlock, Repeater, ImageField, PanelBody, SelectControl, TextControl, ToggleControl } from '../shared/index.js';
import metadata from './block.json';

registerFandecoBlock( metadata, ( { attributes, setAttributes } ) => (
	<>
		<PanelBody title={ __( 'Fotos', 'fan-deco-studio' ) }>
			<Repeater
				items={ attributes.photos }
				onChange={ ( photos ) => setAttributes( { photos } ) }
				newItem={ { imageId: 0, alt: '', caption: '', url: '' } }
				addLabel={ __( 'Agregar foto', 'fan-deco-studio' ) }
				max={ 16 }
				min={ 0 }
				renderItem={ ( item, update ) => (
					<>
						<ImageField label={ __( 'Imagen', 'fan-deco-studio' ) } value={ item.imageId } onChange={ ( imageId ) => update( { imageId } ) } />
						<TextControl label={ __( 'Descripción de la imagen (accesibilidad)', 'fan-deco-studio' ) } value={ item.alt || '' } onChange={ ( alt ) => update( { alt } ) } />
						<TextControl label={ __( 'Texto sobre la foto (opcional)', 'fan-deco-studio' ) } value={ item.caption || '' } onChange={ ( caption ) => update( { caption } ) } />
						<TextControl label={ __( 'Enlace (opcional)', 'fan-deco-studio' ) } type="url" value={ item.url || '' } onChange={ ( url ) => update( { url } ) } />
					</>
				) }
			/>
		</PanelBody>
		<PanelBody title={ __( 'Diseño', 'fan-deco-studio' ) } initialOpen={ false }>
			<SelectControl label={ __( 'Fotos visibles en escritorio', 'fan-deco-studio' ) } value={ String( attributes.perView ) } options={ [ '2', '3', '4', '5' ].map( ( v ) => ( { value: v, label: v } ) ) } onChange={ ( v ) => setAttributes( { perView: Number( v ) } ) } />
			<SelectControl label={ __( 'Proporción de la foto', 'fan-deco-studio' ) } value={ attributes.ratio } options={ [ { value: '3/4', label: __( 'Vertical 3:4', 'fan-deco-studio' ) }, { value: '4/5', label: __( 'Vertical 4:5', 'fan-deco-studio' ) }, { value: '1/1', label: __( 'Cuadrada', 'fan-deco-studio' ) }, { value: '16/9', label: __( 'Horizontal 16:9', 'fan-deco-studio' ) } ] } onChange={ ( ratio ) => setAttributes( { ratio } ) } />
			<ToggleControl label={ __( 'Mostrar flechas', 'fan-deco-studio' ) } checked={ !! attributes.showArrows } onChange={ ( showArrows ) => setAttributes( { showArrows } ) } />
			<ToggleControl label={ __( 'Mostrar texto sobre las fotos', 'fan-deco-studio' ) } checked={ !! attributes.showCaptions } onChange={ ( showCaptions ) => setAttributes( { showCaptions } ) } />
			<ToggleControl label={ __( 'Zoom suave y elevación al pasar el mouse', 'fan-deco-studio' ) } checked={ !! attributes.hoverEffect } onChange={ ( hoverEffect ) => setAttributes( { hoverEffect } ) } />
		</PanelBody>
	</>
) );
