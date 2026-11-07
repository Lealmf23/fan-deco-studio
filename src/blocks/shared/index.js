import { __ } from '@wordpress/i18n';
import { registerBlockType } from '@wordpress/blocks';
import { InspectorControls, MediaUpload, MediaUploadCheck, useBlockProps } from '@wordpress/block-editor';
import { Button, PanelBody, SelectControl, TextControl, TextareaControl, ToggleControl, Spinner, Placeholder } from '@wordpress/components';
import { useSelect } from '@wordpress/data';
import { useState, useEffect } from '@wordpress/element';
import apiFetch from '@wordpress/api-fetch';
import { addQueryArgs } from '@wordpress/url';
import ServerSideRender from '@wordpress/server-side-render';

export { ICON_OPTIONS } from './icons.js';

const TEXT_DOMAIN = 'fan-deco-studio';

/*
 * Vista previa en el editor: cada bloque pide su dibujo al servidor (una petición por bloque). Una página con 10 o más bloques
 * dispara todas a la vez y, en hostings compartidos, varias fallan o se quedan cargando. Esta cola las hace de a 2, reintenta
 * las que fallan y recuerda las ya dibujadas.
 */
const SSR_MAX = 2;
const ssrQueue = [];
const ssrCache = new Map();
let ssrActive = 0;
const ssrPump = () => {
	while ( ssrActive < SSR_MAX && ssrQueue.length ) {
		const job = ssrQueue.shift();
		ssrActive++;
		job().finally( () => {
			ssrActive--;
			ssrPump();
		} );
	}
};
apiFetch.use( ( options, next ) => {
	const target = String( options.path || options.url || '' );
	if ( ! /block-renderer\/fandeco\//.test( target ) ) {
		return next( options );
	}
	const key = target + '|' + ( options.data ? JSON.stringify( options.data ) : '' );
	if ( ssrCache.has( key ) ) {
		return Promise.resolve( ssrCache.get( key ) );
	}
	return new Promise( ( resolve, reject ) => {
		ssrQueue.push( async () => {
			for ( let attempt = 0; ; attempt++ ) {
				try {
					const res = await next( options );
					ssrCache.set( key, res );
					resolve( res );
					return;
				} catch ( e ) {
					if ( attempt >= 2 ) {
						reject( e );
						return;
					}
					await new Promise( ( r ) => setTimeout( r, 700 * ( attempt + 1 ) ) );
				}
			}
		} );
		ssrPump();
	} );
} );

/**
 * Registra un bloque dinámico: vista previa = el mismo render PHP del frontend (ServerSideRender),
 * controles en la barra lateral. `save` devuelve null (se renderiza en PHP).
 */
/** Si la vista previa del editor falla, el bloque sigue siendo editable y se muestra el detalle real del error (no "[object Object]"). */
function PreviewError( { title, error } ) {
	let detail = '';
	if ( error ) {
		if ( typeof error === 'string' ) {
			detail = error;
		} else {
			try {
				detail = JSON.stringify( error.message && typeof error.message === 'string' ? { code: error.code, message: error.message } : error );
			} catch ( e ) {
				detail = String( error );
			}
		}
	}
	return (
		<Placeholder label={ title } instructions={ __( 'No se pudo mostrar la vista previa en el editor. El bloque se puede editar con normalidad desde el panel de la derecha y en la página publicada se ve igual.', TEXT_DOMAIN ) }>
			{ detail && <code style={ { display: 'block', maxWidth: '100%', overflowWrap: 'anywhere', fontSize: 12 } }>{ detail }</code> }
		</Placeholder>
	);
}

export function registerFandecoBlock( metadata, Controls ) {
	function Edit( { attributes, setAttributes } ) {
		const blockProps = useBlockProps();
		return (
			<>
				<InspectorControls>
					<Controls attributes={ attributes } setAttributes={ setAttributes } />
					{ 'animate' in metadata.attributes && (
						<PanelBody title={ __( 'Animación', TEXT_DOMAIN ) } initialOpen={ false }>
							<ToggleControl
								label={ __( 'Animar al entrar en pantalla', TEXT_DOMAIN ) }
								help={ __( 'Se desactiva automáticamente si el visitante prefiere menos movimiento.', TEXT_DOMAIN ) }
								checked={ !! attributes.animate }
								onChange={ ( animate ) => setAttributes( { animate } ) }
							/>
						</PanelBody>
					) }
				</InspectorControls>
				<div { ...blockProps }>
					<ServerSideRender
						block={ metadata.name }
						attributes={ attributes }
						LoadingResponsePlaceholder={ () => <Spinner /> }
						ErrorResponsePlaceholder={ ( { response } ) => <PreviewError title={ metadata.title } error={ response && response.errorMsg } /> }
					/>
				</div>
			</>
		);
	}
	registerBlockType( metadata.name, { edit: Edit, save: () => null } );
}

export function ImageField( { label, value, onChange } ) {
	const media = useSelect( ( select ) => ( value ? select( 'core' ).getMedia( value ) : null ), [ value ] );
	return (
		<div className="fd-editor-field">
			<p style={ { margin: '0 0 4px', fontWeight: 600 } }>{ label }</p>
			<MediaUploadCheck>
				<MediaUpload
					allowedTypes={ [ 'image' ] }
					value={ value || undefined }
					onSelect={ ( m ) => onChange( m.id ) }
					render={ ( { open } ) => (
						<>
							{ media?.source_url && (
								<img src={ media.media_details?.sizes?.medium?.source_url || media.source_url } alt="" style={ { maxWidth: '100%', borderRadius: 4, marginBottom: 8, display: 'block' } } />
							) }
							<Button variant="secondary" onClick={ open }>
								{ value ? __( 'Cambiar imagen', TEXT_DOMAIN ) : __( 'Elegir imagen', TEXT_DOMAIN ) }
							</Button>{ ' ' }
							{ !! value && (
								<Button variant="link" isDestructive onClick={ () => onChange( 0 ) }>
									{ __( 'Quitar', TEXT_DOMAIN ) }
								</Button>
							) }
						</>
					) }
				/>
			</MediaUploadCheck>
		</div>
	);
}

/** Campos comunes de enlace/botón. */
export function ButtonFields( { textKey = 'buttonText', urlKey = 'buttonUrl', attributes, setAttributes } ) {
	return (
		<>
			<TextControl label={ __( 'Texto del botón', TEXT_DOMAIN ) } value={ attributes[ textKey ] } onChange={ ( v ) => setAttributes( { [ textKey ]: v } ) } />
			<TextControl label={ __( 'URL del botón', TEXT_DOMAIN ) } type="url" value={ attributes[ urlKey ] } onChange={ ( v ) => setAttributes( { [ urlKey ]: v } ) } />
		</>
	);
}

/** Repetidor genérico de ítems (beneficios, pasos). */
export function Repeater( { items, onChange, newItem, renderItem, addLabel, max = 8, min = 1 } ) {
	const update = ( index, patch ) => onChange( items.map( ( it, i ) => ( i === index ? { ...it, ...patch } : it ) ) );
	const move = ( from, to ) => {
		if ( to < 0 || to >= items.length ) {
			return;
		}
		const next = [ ...items ];
		next.splice( to, 0, next.splice( from, 1 )[ 0 ] );
		onChange( next );
	};
	return (
		<>
			{ items.map( ( item, index ) => (
				<div key={ index } style={ { border: '1px solid #ddd', borderRadius: 4, padding: 12, marginBottom: 12 } }>
					{ renderItem( item, ( patch ) => update( index, patch ), index ) }
					<div style={ { display: 'flex', gap: 4 } }>
						<Button size="small" variant="tertiary" disabled={ index === 0 } onClick={ () => move( index, index - 1 ) }>↑</Button>
						<Button size="small" variant="tertiary" disabled={ index === items.length - 1 } onClick={ () => move( index, index + 1 ) }>↓</Button>
						<Button size="small" variant="tertiary" isDestructive disabled={ items.length <= min } onClick={ () => onChange( items.filter( ( _, i ) => i !== index ) ) }>
							{ __( 'Eliminar', TEXT_DOMAIN ) }
						</Button>
					</div>
				</div>
			) ) }
			{ items.length < max && (
				<Button variant="secondary" onClick={ () => onChange( [ ...items, newItem ] ) }>{ addLabel }</Button>
			) }
		</>
	);
}

/** Campos título + descripción reutilizables. */
export function TextFields( { attributes, setAttributes, description = true, eyebrow = true } ) {
	return (
		<>
			{ eyebrow && <TextControl label={ __( 'Sobretítulo (eyebrow)', TEXT_DOMAIN ) } value={ attributes.eyebrow } onChange={ ( v ) => setAttributes( { eyebrow: v } ) } /> }
			<TextControl label={ __( 'Título', TEXT_DOMAIN ) } help={ __( 'Estilo: **negrita** y *cursiva* (serif en oliva). Ej.: **Hacemos** *tus ideas* **realidad.**', TEXT_DOMAIN ) } value={ attributes.title } onChange={ ( v ) => setAttributes( { title: v } ) } />
			{ description && <TextareaControl label={ __( 'Descripción', TEXT_DOMAIN ) } value={ attributes.description } onChange={ ( v ) => setAttributes( { description: v } ) } /> }
		</>
	);
}

/** Selector de productos manual (búsqueda por REST, sin tocar datos). */
export function ProductPicker( { ids, onChange } ) {
	const [ query, setQuery ] = useState( '' );
	const [ results, setResults ] = useState( [] );
	const [ names, setNames ] = useState( {} );

	useEffect( () => {
		if ( ! ids.length ) {
			return;
		}
		apiFetch( { path: addQueryArgs( '/wp/v2/product', { include: ids.join( ',' ), per_page: 50, _fields: 'id,title' } ) } )
			.then( ( rows ) => setNames( Object.fromEntries( rows.map( ( r ) => [ r.id, r.title.rendered ] ) ) ) )
			.catch( () => {} );
	}, [ ids.join( ',' ) ] ); // eslint-disable-line react-hooks/exhaustive-deps

	useEffect( () => {
		if ( query.length < 2 ) {
			setResults( [] );
			return undefined;
		}
		const t = setTimeout( () => {
			apiFetch( { path: addQueryArgs( '/wp/v2/product', { search: query, per_page: 10, _fields: 'id,title' } ) } )
				.then( setResults )
				.catch( () => setResults( [] ) );
		}, 300 );
		return () => clearTimeout( t );
	}, [ query ] );

	return (
		<>
			<ul style={ { margin: '0 0 8px', paddingLeft: 16 } }>
				{ ids.map( ( id ) => (
					<li key={ id }>
						{ names[ id ] || `#${ id }` }{ ' ' }
						<Button variant="link" isDestructive onClick={ () => onChange( ids.filter( ( i ) => i !== id ) ) }>×</Button>
					</li>
				) ) }
			</ul>
			<TextControl label={ __( 'Buscar producto para agregar', TEXT_DOMAIN ) } value={ query } onChange={ setQuery } />
			{ results.filter( ( r ) => ! ids.includes( r.id ) ).map( ( r ) => (
				<Button key={ r.id } variant="tertiary" onClick={ () => { onChange( [ ...ids, r.id ] ); setQuery( '' ); } } style={ { display: 'block' } }>
					+ { r.title.rendered }
				</Button>
			) ) }
		</>
	);
}

export { PanelBody, SelectControl, TextControl, TextareaControl, ToggleControl };
