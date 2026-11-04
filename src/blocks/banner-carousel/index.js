import { __ } from '@wordpress/i18n';
import { registerFandecoBlock, Repeater, ImageField, PanelBody, SelectControl, TextControl, ToggleControl } from '../shared/index.js';
import metadata from './block.json';

registerFandecoBlock( metadata, ( { attributes, setAttributes } ) => (
	<>
		<PanelBody title={ __( 'Banners', 'fan-deco-studio' ) }>
			<p className="description">{ __( 'Cada banner es una imagen. Si el texto ya está dentro de la imagen, escribilo también en "Descripción" para lectores de pantalla y buscadores.', 'fan-deco-studio' ) }</p>
			<Repeater
				items={ attributes.slides }
				onChange={ ( slides ) => setAttributes( { slides } ) }
				newItem={ { imageId: 0, alt: '', url: '' } }
				addLabel={ __( 'Agregar banner', 'fan-deco-studio' ) }
				max={ 8 }
				min={ 0 }
				renderItem={ ( item, update ) => (
					<>
						<ImageField label={ __( 'Imagen', 'fan-deco-studio' ) } value={ item.imageId } onChange={ ( imageId ) => update( { imageId } ) } />
						<TextControl label={ __( 'Descripción de la imagen', 'fan-deco-studio' ) } value={ item.alt || '' } onChange={ ( alt ) => update( { alt } ) } />
						<TextControl label={ __( 'Enlace (opcional)', 'fan-deco-studio' ) } type="url" value={ item.url || '' } onChange={ ( url ) => update( { url } ) } />
					</>
				) }
			/>
		</PanelBody>
		<PanelBody title={ __( 'Movimiento', 'fan-deco-studio' ) } initialOpen={ false }>
			<ToggleControl label={ __( 'Avanzar solo', 'fan-deco-studio' ) } help={ __( 'Se pausa al pasar el cursor o enfocar, y no se mueve si el visitante prefiere menos movimiento.', 'fan-deco-studio' ) } checked={ !! attributes.autoplay } onChange={ ( autoplay ) => setAttributes( { autoplay } ) } />
			<SelectControl label={ __( 'Segundos entre banners', 'fan-deco-studio' ) } value={ String( attributes.interval ) } options={ [ '3', '4', '5', '6', '8' ].map( ( v ) => ( { value: v, label: v } ) ) } onChange={ ( v ) => setAttributes( { interval: Number( v ) } ) } />
		</PanelBody>
	</>
) );
