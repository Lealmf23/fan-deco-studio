<?php
/**
 * Portada. Si la home es una página estática, se renderiza su contenido de bloques (editable).
 * Si no hay página asignada, se usa el patrón "Fan Deco: Portada" como base visible.
 *
 * @package FanDecoStudio
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>
<main id="main" class="fd-main fd-home" tabindex="-1">
	<?php
	ob_start();
	if ( is_page() && have_posts() ) {
		while ( have_posts() ) {
			the_post();
			the_content();
		}
	} else {
		echo do_blocks( fandeco_default_home_content() ); // phpcs:ignore WordPress.Security.EscapeOutput -- salida de bloques.
	}
	$fandeco_home = ob_get_clean();
	// SEO/accesibilidad: la portada siempre tiene un único <h1> (el hero lo aporta; si se quita, usamos el nombre del sitio).
	if ( ! preg_match( '/<h1[\s>]/i', $fandeco_home ) ) {
		echo '<h1 class="screen-reader-text">' . esc_html( get_bloginfo( 'name' ) ) . '</h1>';
	}
	echo $fandeco_home; // phpcs:ignore WordPress.Security.EscapeOutput -- salida de bloques.
	?>
</main>
<?php
get_footer();
