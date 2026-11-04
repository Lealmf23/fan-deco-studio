import { gsap } from 'gsap';
import { ScrollTrigger } from 'gsap/ScrollTrigger';

/**
 * Reveal progresivo. Solo se importa si la página tiene [data-fd-reveal] y no hay reduced-motion.
 * Animamos únicamente opacity/transform (barato en móvil).
 */
export function initAnimations() {
	gsap.registerPlugin( ScrollTrigger );
	const root = document.documentElement;

	const targets = gsap.utils.toArray( '[data-fd-animate="true"] [data-fd-reveal]' );
	if ( ! targets.length ) {
		root.classList.remove( 'fd-anim' );
		return;
	}

	ScrollTrigger.batch( targets, {
		start: 'top 92%',
		once: true,
		onEnter: ( batch ) => {
			gsap.to( batch, {
				opacity: 1,
				y: 0,
				scale: 1,
				duration: 0.9,
				ease: 'power3.out',
				stagger: 0.09,
				overwrite: true,
				onComplete: () => {
					// Estado final: se limpian los estilos de GSAP y se marca el elemento, para que la regla CSS
					// del estado inicial (opacity 0 + desplazamiento) deje de aplicarse.
					batch.forEach( ( el ) => {
						el.setAttribute( 'data-fd-revealed', '' );
						gsap.set( el, { clearProps: 'opacity,transform' } );
					} );
				},
			} );
		},
	} );

	// Reveal del hero a la carga (ya visible en pantalla): sin esperar al scroll.
	ScrollTrigger.refresh();
}
