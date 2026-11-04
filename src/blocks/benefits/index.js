import { __ } from '@wordpress/i18n';
import { registerFandecoBlock, Repeater, ICON_OPTIONS, PanelBody, SelectControl, TextControl } from '../shared/index.js';
import metadata from './block.json';

registerFandecoBlock( metadata, ( { attributes, setAttributes } ) => (
	<PanelBody title={ __( 'Beneficios', 'fan-deco-studio' ) }>
		<SelectControl label={ __( 'Estilo', 'fan-deco-studio' ) } value={ attributes.variant } options={ [ { value: 'cards', label: __( 'Tarjetas', 'fan-deco-studio' ) }, { value: 'strip', label: __( 'Franja con divisores', 'fan-deco-studio' ) } ] } onChange={ ( variant ) => setAttributes( { variant } ) } />
		<p className="description">{ __( 'Texto editorial: el descuento real se configura en WooCommerce/pasarela de pago. Este bloque no cambia precios.', 'fan-deco-studio' ) }</p>
		<Repeater
			items={ attributes.items }
			onChange={ ( items ) => setAttributes( { items } ) }
			newItem={ { icon: 'sparkles', title: '', text: '' } }
			addLabel={ __( 'Agregar beneficio', 'fan-deco-studio' ) }
			max={ 6 }
			renderItem={ ( item, update ) => (
				<>
					<SelectControl label={ __( 'Icono', 'fan-deco-studio' ) } value={ item.icon } options={ ICON_OPTIONS } onChange={ ( icon ) => update( { icon } ) } />
					<TextControl label={ __( 'Título', 'fan-deco-studio' ) } value={ item.title } onChange={ ( title ) => update( { title } ) } />
					<TextControl label={ __( 'Texto', 'fan-deco-studio' ) } value={ item.text } onChange={ ( text ) => update( { text } ) } />
				</>
			) }
		/>
	</PanelBody>
) );
