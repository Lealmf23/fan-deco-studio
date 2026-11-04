import { __ } from '@wordpress/i18n';
import { registerFandecoBlock, TextFields, PanelBody, SelectControl, TextControl } from '../shared/index.js';
import metadata from './block.json';

registerFandecoBlock( metadata, ( { attributes, setAttributes } ) => (
	<>
		<PanelBody title={ __( 'Contenido', 'fan-deco-studio' ) }>
			<TextFields attributes={ attributes } setAttributes={ setAttributes } />
			<TextControl label={ __( 'Texto del enlace', 'fan-deco-studio' ) } value={ attributes.linkText } onChange={ ( linkText ) => setAttributes( { linkText } ) } />
			<TextControl label={ __( 'URL del enlace', 'fan-deco-studio' ) } type="url" value={ attributes.linkUrl } onChange={ ( linkUrl ) => setAttributes( { linkUrl } ) } />
		</PanelBody>
		<PanelBody title={ __( 'Diseño', 'fan-deco-studio' ) } initialOpen={ false }>
			<SelectControl label={ __( 'Alineación', 'fan-deco-studio' ) } value={ attributes.align } options={ [ { value: 'left', label: __( 'Izquierda', 'fan-deco-studio' ) }, { value: 'center', label: __( 'Centrada', 'fan-deco-studio' ) } ] } onChange={ ( align ) => setAttributes( { align } ) } />
			<SelectControl label={ __( 'Nivel de encabezado', 'fan-deco-studio' ) } value={ String( attributes.level ) } options={ [ { value: '2', label: 'H2' }, { value: '3', label: 'H3' } ] } onChange={ ( v ) => setAttributes( { level: Number( v ) } ) } />
		</PanelBody>
	</>
) );
