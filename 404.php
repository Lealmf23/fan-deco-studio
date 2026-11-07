<?php
/**
 * Error 404: estado vacío con buscador y atajos a categorías.
 *
 * @package FanDecoStudio
 */

defined( 'ABSPATH' ) || exit;

get_header();
?>
<main id="main" class="fd-main fd-404-page" tabindex="-1">
	<div class="fd-container fd-404">
		<p class="fd-404__code" aria-hidden="true">404</p>
		<h1 class="fd-404__title"><?php esc_html_e( 'Esta página no existe', 'fan-deco-studio' ); ?></h1>
		<p class="fd-404__text"><?php esc_html_e( 'Quizás el enlace cambió. Buscá lo que necesitás o volvé al inicio.', 'fan-deco-studio' ); ?></p>
		<form role="search" method="get" class="fd-404__search" action="<?php echo esc_url( home_url( '/' ) ); ?>">
			<label class="screen-reader-text" for="fd-404-search"><?php esc_html_e( 'Buscar productos', 'fan-deco-studio' ); ?></label>
			<input type="search" id="fd-404-search" name="s" placeholder="<?php esc_attr_e( 'Buscar lámparas, mascotas, regalos…', 'fan-deco-studio' ); ?>" autocomplete="off">
			<?php if ( fandeco_has_woocommerce() ) : ?><input type="hidden" name="post_type" value="product"><?php endif; ?>
			<button type="submit" class="fd-btn fd-btn--primary"><?php esc_html_e( 'Buscar', 'fan-deco-studio' ); ?></button>
		</form>
		<?php echo fandeco_category_chips( 'fd-chips fd-chips--center' ); // phpcs:ignore WordPress.Security.EscapeOutput -- escapado en la función. ?>
		<p><?php echo fandeco_button( __( 'Ir al inicio', 'fan-deco-studio' ), home_url( '/' ), 'outline' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></p>
	</div>
</main>
<?php
get_footer();
