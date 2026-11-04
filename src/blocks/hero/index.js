import { __ } from '@wordpress/i18n';
import { registerFandecoBlock, ImageField, ButtonFields, TextFields, PanelBody, SelectControl, ToggleControl } from '../shared/index.js';
import metadata from './block.json';

registerFandecoBlock( metadata, ( { attributes, setAttributes } ) => (
	<>
		<PanelBody title={ __( 'Contenido', 'fan-deco-studio' ) }>
			<TextFields attributes={ attributes } setAttributes={ setAttributes } />
			<ButtonFields attributes={ attributes } setAttributes={ setAttributes } />
		</PanelBody>
		<PanelBody title={ __( 'Imágenes', 'fan-deco-studio' ) } initialOpen={ false }>
			<ImageField label={ __( 'Imagen desktop', 'fan-deco-studio' ) } value={ attributes.desktopImageId } onChange={ ( v ) => setAttributes( { desktopImageId: v } ) } />
			<ImageField label={ __( 'Imagen mobile (opcional)', 'fan-deco-studio' ) } value={ attributes.mobileImageId } onChange={ ( v ) => setAttributes( { mobileImageId: v } ) } />
			<ImageField label={ __( 'Imagen con la luz encendida (opcional)', 'fan-deco-studio' ) } value={ attributes.imageLitId } onChange={ ( v ) => setAttributes( { imageLitId: v } ) } />
			<p className="description">{ __( 'Si la cargás, aparece un interruptor “Luz apagada/encendida” que cambia toda la portada de día a noche. Usá el mismo encuadre que la imagen principal. Sin esta imagen no hay interruptor.', 'fan-deco-studio' ) }</p>
			{ !! attributes.imageLitId && <ToggleControl label={ __( 'Empezar con la luz encendida', 'fan-deco-studio' ) } checked={ attributes.startLit } onChange={ ( startLit ) => setAttributes( { startLit } ) } /> }
		</PanelBody>
		<PanelBody title={ __( 'Diseño', 'fan-deco-studio' ) } initialOpen={ false }>
			<SelectControl
				label={ __( 'Variante', 'fan-deco-studio' ) }
				value={ attributes.variant }
				options={ [
					{ value: 'split', label: __( 'Texto a la izquierda, imagen a la derecha', 'fan-deco-studio' ) },
					{ value: 'split-reverse', label: __( 'Imagen a la izquierda, texto a la derecha', 'fan-deco-studio' ) },
					{ value: 'overlay', label: __( 'Imagen de fondo con texto encima', 'fan-deco-studio' ) },
					{ value: 'immersive', label: __( 'Inmersiva: imagen a pantalla completa', 'fan-deco-studio' ) },
				] }
				onChange={ ( variant ) => setAttributes( { variant } ) }
			/>
			<SelectControl
				label={ __( 'Alineación del texto', 'fan-deco-studio' ) }
				value={ attributes.align }
				options={ [ { value: 'left', label: __( 'Izquierda', 'fan-deco-studio' ) }, { value: 'center', label: __( 'Centrada', 'fan-deco-studio' ) } ] }
				onChange={ ( align ) => setAttributes( { align } ) }
			/>
		</PanelBody>
	</>
) );
