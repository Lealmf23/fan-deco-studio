<?php
/**
 * Error 404.
 *
 * @package FanDecoStudio
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>
<main id="main" class="fd-main" tabindex="-1">
	<div class="fd-container fd-404">
		<p class="fd-eyebrow">404</p>
		<h1 class="fd-page-title"><?php esc_html_e( 'Esta página no existe', 'fan-deco-studio' ); ?></h1>
		<p><?php esc_html_e( 'Quizás el enlace cambió. Volvé al inicio o explorá la tienda.', 'fan-deco-studio' ); ?></p>
		<p><?php echo fandeco_button( __( 'Ir al inicio', 'fan-deco-studio' ), home_url( '/' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?></p>
	</div>
</main>
<?php
get_footer();
