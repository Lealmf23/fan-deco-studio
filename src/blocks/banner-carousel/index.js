import { __ } from '@wordpress/i18n';
import { RangeControl } from '@wordpress/components';
import { registerFandecoBlock, Repeater, ImageField, ButtonFields, TextFields, PanelBody, SelectControl, TextControl, ToggleControl } from '../shared/index.js';
import metadata from './block.json';

registerFandecoBlock( metadata, ( { attributes, setAttributes } ) => (
	<>
		<PanelBody title={ __( 'Texto (opcional)', 'fan-deco-studio' ) }>
			<p className="description">{ __( 'Con texto, en escritorio queda a la izquierda y las fotos a la derecha; en móvil, el texto arriba. Sin texto, solo las fotos centradas.', 'fan-deco-studio' ) }</p>
			<TextFields attributes={ attributes } setAttributes={ setAttributes } />
			<ButtonFields attributes={ attributes } setAttributes={ setAttributes } />
			<SelectControl label={ __( 'Nivel del título', 'fan-deco-studio' ) } value={ String( attributes.level ) } options={ [ { value: '1', label: __( 'H1 (título principal de la página; usalo una sola vez)', 'fan-deco-studio' ) }, { value: '2', label: 'H2' } ] } onChange={ ( v ) => setAttributes( { level: Number( v ) } ) } />
		</PanelBody>
		<PanelBody title={ __( 'Banners', 'fan-deco-studio' ) } initialOpen={ false }>
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
		<PanelBody title={ __( 'Espacios', 'fan-deco-studio' ) } initialOpen={ false }>
			<ToggleControl label={ __( 'Definir el espacio de arriba y de abajo', 'fan-deco-studio' ) } help={ __( 'Apagado, se usa el espacio automático del diseño. El color de fondo se cambia en el panel "Color".', 'fan-deco-studio' ) } checked={ !! attributes.customSpace } onChange={ ( customSpace ) => setAttributes( { customSpace } ) } />
			{ attributes.customSpace && (
				<>
					<RangeControl label={ __( 'Espacio de arriba (px)', 'fan-deco-studio' ) } min={ 0 } max={ 240 } value={ attributes.spaceTop } onChange={ ( spaceTop ) => setAttributes( { spaceTop: spaceTop || 0 } ) } />
					<RangeControl label={ __( 'Espacio de abajo (px)', 'fan-deco-studio' ) } min={ 0 } max={ 240 } value={ attributes.spaceBottom } onChange={ ( spaceBottom ) => setAttributes( { spaceBottom: spaceBottom || 0 } ) } />
				</>
			) }
		</PanelBody>
		<PanelBody title={ __( 'Movimiento', 'fan-deco-studio' ) } initialOpen={ false }>
			<ToggleControl label={ __( 'Avanzar solo', 'fan-deco-studio' ) } help={ __( 'Se pausa al pasar el cursor o enfocar, y no se mueve si el visitante prefiere menos movimiento.', 'fan-deco-studio' ) } checked={ !! attributes.autoplay } onChange={ ( autoplay ) => setAttributes( { autoplay } ) } />
			<SelectControl label={ __( 'Segundos entre banners', 'fan-deco-studio' ) } value={ String( attributes.interval ) } options={ [ '3', '4', '5', '6', '8' ].map( ( v ) => ( { value: v, label: v } ) ) } onChange={ ( v ) => setAttributes( { interval: Number( v ) } ) } />
		</PanelBody>
	</>
) );
