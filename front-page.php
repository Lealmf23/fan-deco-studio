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
	if ( is_page() && have_posts() ) {
		while ( have_posts() ) {
			the_post();
			the_content();
		}
	} else {
		echo do_blocks( fandeco_default_home_content() ); // phpcs:ignore WordPress.Security.EscapeOutput -- salida de bloques.
	}
	?>
</main>
<?php
get_footer();
