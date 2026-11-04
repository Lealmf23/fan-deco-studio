import { __ } from '@wordpress/i18n';
import { registerFandecoBlock, ImageField, ButtonFields, TextFields, PanelBody, SelectControl, TextControl } from '../shared/index.js';
import metadata from './block.json';

registerFandecoBlock( metadata, ( { attributes, setAttributes } ) => (
	<>
		<PanelBody title={ __( 'Contenido', 'fan-deco-studio' ) }>
			<TextFields attributes={ attributes } setAttributes={ setAttributes } />
			<TextControl label={ __( 'Etiqueta (ej. “Próximamente”)', 'fan-deco-studio' ) } value={ attributes.badge } onChange={ ( badge ) => setAttributes( { badge } ) } />
			<ButtonFields attributes={ attributes } setAttributes={ setAttributes } />
		</PanelBody>
		<PanelBody title={ __( 'Imagen y diseño', 'fan-deco-studio' ) } initialOpen={ false }>
			<ImageField label={ __( 'Imagen', 'fan-deco-studio' ) } value={ attributes.imageId } onChange={ ( imageId ) => setAttributes( { imageId } ) } />
			<SelectControl label={ __( 'Posición de la imagen', 'fan-deco-studio' ) } value={ attributes.imagePosition } options={ [ { value: 'right', label: __( 'Derecha', 'fan-deco-studio' ) }, { value: 'left', label: __( 'Izquierda', 'fan-deco-studio' ) } ] } onChange={ ( imagePosition ) => setAttributes( { imagePosition } ) } />
			<SelectControl label={ __( 'Estilo', 'fan-deco-studio' ) } value={ attributes.variant } options={ [ { value: 'light', label: __( 'Claro', 'fan-deco-studio' ) }, { value: 'dark', label: __( 'Azul profundo', 'fan-deco-studio' ) }, { value: 'wine', label: __( 'Bordó', 'fan-deco-studio' ) }, { value: 'immersive', label: __( 'Inmersivo: imagen de fondo con texto encima', 'fan-deco-studio' ) } ] } onChange={ ( variant ) => setAttributes( { variant } ) } />
		</PanelBody>
		<PanelBody title={ __( 'Punto de montaje (avanzado)', 'fan-deco-studio' ) } initialOpen={ false }>
			<SelectControl
				label={ __( 'Reservar espacio para un componente React', 'fan-deco-studio' ) }
				help={ __( 'Solo prepara el contenedor [data-fd-mount]. El configurador 3D aún no existe: sin imagen, se muestra una etiqueta "Próximamente".', 'fan-deco-studio' ) }
				value={ attributes.mount }
				options={ [ { value: '', label: __( 'Ninguno', 'fan-deco-studio' ) }, { value: 'configurator', label: __( 'Configurador 3D (futuro)', 'fan-deco-studio' ) } ] }
				onChange={ ( mount ) => setAttributes( { mount } ) }
			/>
		</PanelBody>
	</>
) );
