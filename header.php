<?php
/**
 * Header del sitio.
 *
 * @package FanDecoStudio
 */

defined( 'ABSPATH' ) || exit;

$announcement = (string) fandeco_option( 'announcement', '' );
$ann_url      = (string) fandeco_option( 'announcement_url', '' );
$sticky       = (bool) fandeco_option( 'sticky_header', true );
?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<?php wp_head(); ?>
</head>
<body <?php body_class( 'fd-body' ); ?>>
<?php wp_body_open(); ?>
<a class="fd-skip-link" href="#main"><?php esc_html_e( 'Saltar al contenido', 'fan-deco-studio' ); ?></a>

<?php if ( '' !== $announcement ) : ?>
	<div class="fd-announcement">
		<?php if ( $ann_url ) : ?>
			<a href="<?php echo esc_url( $ann_url ); ?>"><?php echo esc_html( $announcement ); ?></a>
		<?php else : ?>
			<p><?php echo esc_html( $announcement ); ?></p>
		<?php endif; ?>
	</div>
<?php endif; ?>

<header class="fd-header<?php echo $sticky ? ' is-sticky' : ''; ?>" data-fd-header>
	<div class="fd-container fd-header__inner">
		<a class="fd-header__brand" href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home" aria-label="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>">
			<?php echo fandeco_logo( 'dark' ); // phpcs:ignore WordPress.Security.EscapeOutput -- escapado dentro de la función. ?>
		</a>

		<nav class="fd-nav" aria-label="<?php esc_attr_e( 'Principal', 'fan-deco-studio' ); ?>">
			<?php
			wp_nav_menu(
				array(
					'theme_location' => 'primary',
					'container'      => false,
					'menu_class'     => 'fd-menu',
					'fallback_cb'    => 'fandeco_menu_fallback',
					'depth'          => 2,
				)
			);
			?>
		</nav>

		<div class="fd-header__actions">
			<button type="button" class="fd-icon-btn" data-fd-search-toggle aria-expanded="false" aria-controls="fd-search-panel" aria-label="<?php esc_attr_e( 'Buscar', 'fan-deco-studio' ); ?>">
				<?php echo fandeco_icon( 'search' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
			</button>
			<?php if ( fandeco_has_woocommerce() ) : ?>
				<a class="fd-icon-btn fd-account-link" href="<?php echo esc_url( wc_get_page_permalink( 'myaccount' ) ); ?>" aria-label="<?php esc_attr_e( 'Mi cuenta', 'fan-deco-studio' ); ?>">
					<?php echo fandeco_icon( 'user' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
				</a>
				<?php echo fandeco_cart_link_html(); // phpcs:ignore WordPress.Security.EscapeOutput -- escapado dentro de la función. ?>
			<?php endif; ?>
			<button type="button" class="fd-icon-btn fd-menu-toggle" data-fd-menu-toggle aria-expanded="false" aria-controls="fd-drawer" aria-label="<?php esc_attr_e( 'Abrir menú', 'fan-deco-studio' ); ?>">
				<?php echo fandeco_icon( 'menu' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
			</button>
		</div>
	</div>

	<div class="fd-search-panel" id="fd-search-panel" data-fd-search-panel hidden>
		<div class="fd-container">
			<form role="search" method="get" class="fd-search-form" action="<?php echo esc_url( home_url( '/' ) ); ?>">
				<label class="screen-reader-text" for="fd-search-input"><?php esc_html_e( 'Buscar productos', 'fan-deco-studio' ); ?></label>
				<input type="search" id="fd-search-input" name="s" placeholder="<?php esc_attr_e( 'Buscar lámparas, mascotas, regalos…', 'fan-deco-studio' ); ?>" value="<?php echo esc_attr( get_search_query() ); ?>" autocomplete="off">
				<?php if ( fandeco_has_woocommerce() ) : ?>
					<input type="hidden" name="post_type" value="product">
				<?php endif; ?>
				<button type="submit" class="fd-btn fd-btn--primary"><?php esc_html_e( 'Buscar', 'fan-deco-studio' ); ?></button>
			</form>
		</div>
	</div>
</header>

<form role="search" method="get" class="fd-mobile-search" action="<?php echo esc_url( home_url( '/' ) ); ?>">
	<div class="fd-container">
		<label class="fd-mobile-search__field">
			<?php echo fandeco_icon( 'search' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
			<span class="screen-reader-text"><?php esc_html_e( 'Buscar productos', 'fan-deco-studio' ); ?></span>
			<input type="search" name="s" placeholder="<?php esc_attr_e( 'Buscar lámparas, mascotas, regalos…', 'fan-deco-studio' ); ?>" autocomplete="off">
		</label>
		<?php if ( fandeco_has_woocommerce() ) : ?><input type="hidden" name="post_type" value="product"><?php endif; ?>
	</div>
</form>

<div class="fd-drawer" id="fd-drawer" data-fd-drawer inert aria-label="<?php esc_attr_e( 'Menú', 'fan-deco-studio' ); ?>" role="dialog" aria-modal="true">
	<div class="fd-drawer__panel">
		<div class="fd-drawer__head">
			<?php echo fandeco_logo( 'light' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
			<button type="button" class="fd-icon-btn" data-fd-menu-close aria-label="<?php esc_attr_e( 'Cerrar menú', 'fan-deco-studio' ); ?>">
				<?php echo fandeco_icon( 'close' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
			</button>
		</div>
		<nav class="fd-drawer__nav" aria-label="<?php esc_attr_e( 'Menú móvil', 'fan-deco-studio' ); ?>">
			<?php
			wp_nav_menu(
				array(
					'theme_location' => 'primary',
					'container'      => false,
					'menu_class'     => 'fd-menu fd-menu--mobile',
					'fallback_cb'    => 'fandeco_menu_fallback',
					'depth'          => 2,
				)
			);
			?>
		</nav>
	</div>
	<div class="fd-drawer__backdrop" data-fd-menu-close></div>
</div>
