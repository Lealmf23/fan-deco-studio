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
		?>
		<article <?php post_class( 'fd-container fd-prose-wrap' ); ?>>
			<header class="fd-page-head"><h1 class="fd-page-title"><?php the_title(); ?></h1></header>
			<div class="fd-prose"><?php the_content(); ?></div>
		</article>
	<?php endwhile; ?>
</main>
<?php
get_footer();
