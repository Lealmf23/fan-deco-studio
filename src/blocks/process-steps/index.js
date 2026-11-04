import { __ } from '@wordpress/i18n';
import { registerFandecoBlock, Repeater, ICON_OPTIONS, PanelBody, SelectControl, TextControl, TextareaControl } from '../shared/index.js';
import metadata from './block.json';

registerFandecoBlock( metadata, ( { attributes, setAttributes } ) => (
	<PanelBody title={ __( 'Etapas', 'fan-deco-studio' ) }>
		<Repeater
			items={ attributes.items }
			onChange={ ( items ) => setAttributes( { items } ) }
			newItem={ { icon: 'sparkles', title: '', text: '' } }
			addLabel={ __( 'Agregar etapa', 'fan-deco-studio' ) }
			max={ 6 }
			min={ 2 }
			renderItem={ ( item, update ) => (
				<>
					<SelectControl label={ __( 'Icono', 'fan-deco-studio' ) } value={ item.icon } options={ ICON_OPTIONS } onChange={ ( icon ) => update( { icon } ) } />
					<TextControl label={ __( 'Título', 'fan-deco-studio' ) } value={ item.title } onChange={ ( title ) => update( { title } ) } />
					<TextareaControl label={ __( 'Texto', 'fan-deco-studio' ) } value={ item.text } onChange={ ( text ) => update( { text } ) } />
				</>
			) }
		/>
	</PanelBody>
) );
