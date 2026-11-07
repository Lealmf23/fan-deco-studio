// Mantener sincronizado con fandeco_icon_choices() en inc/helpers.php
export const ICON_OPTIONS = [
	[ 'truck', 'Envío' ],
	[ 'card', 'Tarjeta' ],
	[ 'bank', 'Transferencia' ],
	[ 'shield', 'Garantía' ],
	[ 'design', 'Diseño' ],
	[ 'test', 'Prueba' ],
	[ 'print', 'Fabricación' ],
	[ 'package', 'Paquete' ],
	[ 'sparkles', 'Destacado' ],
	[ 'heart', 'Favorito' ],
	[ 'palette', 'Colores' ],
	[ 'bulb', 'Idea' ],
	[ 'tag', 'Oferta' ],
	[ 'gift', 'Regalo' ],
].map( ( [ value, label ] ) => ( { value, label } ) );
