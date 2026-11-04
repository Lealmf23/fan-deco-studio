// Contadores de longitud (título/descripción) en el panel SEO. Sin dependencias.
document.querySelectorAll( '[data-fdc-seo]' ).forEach( ( box ) => {
	const pairs = [
		[ '[data-fdc-title]', '[data-fdc-title-count]', 60 ],
		[ '[data-fdc-desc]', '[data-fdc-desc-count]', 160 ],
	];
	pairs.forEach( ( [ inputSel, countSel, max ] ) => {
		const input = box.querySelector( inputSel );
		const count = box.querySelector( countSel );
		if ( ! input || ! count ) {
			return;
		}
		const update = () => {
			count.textContent = String( input.value.length );
			count.style.color = input.value.length > max ? '#b32d2e' : '';
		};
		input.addEventListener( 'input', update );
		update();
	} );
} );
