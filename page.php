<?php
/**
 * Páginas (incluye carrito, checkout y Mi cuenta de WooCommerce, que usan shortcodes/bloques).
 *
 * @package FanDecoStudio
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>
<main id="main" class="fd-main fd-woo-page" tabindex="-1">
	<?php
	while ( have_posts() ) :
		the_post();
		// Páginas hechas con Elementor (durante la migración): ancho completo, sin título ni contenedor del tema.
		// Excepción: carrito/checkout/Mi cuenta (checkout clásico) siempre usan el contenedor del tema, aunque hoy estén hechas con Elementor.
		$fd_wc_page = fandeco_has_woocommerce() && ( is_cart() || is_checkout() || is_account_page() );
		// Páginas armadas con bloques Fan Deco (portada, landings): ancho completo, sin título ni contenedor.
		if ( ! $fd_wc_page && false !== strpos( (string) get_post_field( 'post_content', get_the_ID() ), '<!-- wp:fandeco/' ) ) {
			?>
			<article <?php post_class( 'fd-blocks-page' ); ?>><?php the_content(); ?></article>
			<?php
			continue;
		}
		if ( ! $fd_wc_page && 'builder' === get_post_meta( get_the_ID(), '_elementor_edit_mode', true ) ) {
			?>
			<article <?php post_class( 'fd-elementor-page' ); ?>><?php the_content(); ?></article>
			<?php
			continue;
		}
		?>
		<article <?php post_class( 'fd-container fd-prose-wrap' ); ?>>
			<header class="fd-page-head"><h1 class="fd-page-title"><?php the_title(); ?></h1></header>
			<div class="fd-prose"><?php the_content(); ?></div>
		</article>
	<?php endwhile; ?>
</main>
<?php
get_footer();
