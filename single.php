<?php
/**
 * Entrada individual.
 *
 * @package FanDecoStudio
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>
<main id="main" class="fd-main" tabindex="-1">
	<?php
	while ( have_posts() ) :
		the_post();
		?>
		<article <?php post_class( 'fd-container fd-prose-wrap' ); ?>>
			<header class="fd-page-head">
				<h1 class="fd-page-title"><?php the_title(); ?></h1>
				<p class="fd-page-meta"><time datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>"><?php echo esc_html( get_the_date() ); ?></time></p>
			</header>
			<?php if ( has_post_thumbnail() ) : ?>
				<div class="fd-post-hero"><?php the_post_thumbnail( 'fandeco-wide' ); ?></div>
			<?php endif; ?>
			<div class="fd-prose"><?php the_content(); ?></div>
		</article>
	<?php endwhile; ?>
</main>
<?php
get_footer();
