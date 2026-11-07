import { __ } from '@wordpress/i18n';
import { RangeControl } from '@wordpress/components';
import { registerFandecoBlock, TextFields, PanelBody, SelectControl, TextControl, ToggleControl } from '../shared/index.js';
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
			<SelectControl label={ __( 'Nivel de encabezado', 'fan-deco-studio' ) } value={ String( attributes.level ) } options={ [ { value: '1', label: __( 'H1 (título principal de la página; usalo una sola vez)', 'fan-deco-studio' ) }, { value: '2', label: 'H2' }, { value: '3', label: 'H3' } ] } onChange={ ( v ) => setAttributes( { level: Number( v ) } ) } />
		</PanelBody>
		<PanelBody title={ __( 'Tamaños y espacios', 'fan-deco-studio' ) } initialOpen={ false }>
			<RangeControl label={ __( 'Tamaño del título (px, 0 = automático)', 'fan-deco-studio' ) } min={ 0 } max={ 96 } value={ attributes.titleSize } onChange={ ( titleSize ) => setAttributes( { titleSize: titleSize || 0 } ) } />
			<RangeControl label={ __( 'Tamaño de la descripción (px, 0 = automático)', 'fan-deco-studio' ) } min={ 0 } max={ 40 } value={ attributes.descSize } onChange={ ( descSize ) => setAttributes( { descSize: descSize || 0 } ) } />
			<ToggleControl label={ __( 'Definir el espacio de arriba y de abajo', 'fan-deco-studio' ) } help={ __( 'Apagado, se usa el espacio automático del diseño.', 'fan-deco-studio' ) } checked={ !! attributes.customSpace } onChange={ ( customSpace ) => setAttributes( { customSpace } ) } />
			{ attributes.customSpace && (
				<>
					<RangeControl label={ __( 'Espacio de arriba (px)', 'fan-deco-studio' ) } min={ 0 } max={ 240 } value={ attributes.spaceTop } onChange={ ( spaceTop ) => setAttributes( { spaceTop: spaceTop || 0 } ) } />
					<RangeControl label={ __( 'Espacio de abajo (px)', 'fan-deco-studio' ) } min={ 0 } max={ 240 } value={ attributes.spaceBottom } onChange={ ( spaceBottom ) => setAttributes( { spaceBottom: spaceBottom || 0 } ) } />
				</>
			) }
		</PanelBody>
	</>
) );
