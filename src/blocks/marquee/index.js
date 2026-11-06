import { __ } from '@wordpress/i18n';
import { RangeControl } from '@wordpress/components';
import { registerFandecoBlock, PanelBody, SelectControl, TextareaControl } from '../shared/index.js';
import metadata from './block.json';

registerFandecoBlock( metadata, ( { attributes, setAttributes } ) => (
	<>
	<PanelBody title={ __( 'Mensajes', 'fan-deco-studio' ) }>
		<TextareaControl label={ __( 'Una palabra o frase por línea', 'fan-deco-studio' ) } value={ attributes.items } onChange={ ( items ) => setAttributes( { items } ) } />
		<SelectControl label={ __( 'Velocidad', 'fan-deco-studio' ) } value={ String( attributes.speed ) } options={ [ { value: '60', label: __( 'Lenta', 'fan-deco-studio' ) }, { value: '30', label: __( 'Media', 'fan-deco-studio' ) }, { value: '18', label: __( 'Rápida', 'fan-deco-studio' ) } ] } onChange={ ( v ) => setAttributes( { speed: Number( v ) } ) } />
	</PanelBody>
	<PanelBody title={ __( 'Tamaño', 'fan-deco-studio' ) } initialOpen={ false }>
		<RangeControl label={ __( 'Alto: espacio arriba y abajo de las letras (px, 0 = automático)', 'fan-deco-studio' ) } min={ 0 } max={ 80 } value={ attributes.height } onChange={ ( height ) => setAttributes( { height: height || 0 } ) } />
		<RangeControl label={ __( 'Tamaño de las letras (px, 0 = automático)', 'fan-deco-studio' ) } min={ 0 } max={ 40 } value={ attributes.textSize } onChange={ ( textSize ) => setAttributes( { textSize: textSize || 0 } ) } />
	</PanelBody>
	</>
) );
