<?php
/**
 * Footer del sitio. Los datos provienen de menús y del Customizer.
 *
 * @package FanDecoStudio
 */

defined( 'ABSPATH' ) || exit;

$social   = fandeco_social_links();
$email    = (string) fandeco_option( 'email', '' );
$phone    = (string) fandeco_option( 'phone', '' );
$whatsapp = (string) fandeco_option( 'whatsapp', '' );
$address  = (string) fandeco_option( 'address', '' );
$hours    = (string) fandeco_option( 'hours', '' );
$about    = (string) fandeco_option( 'footer_about', '' );
$payments = array_filter( array_map( 'trim', explode( "\n", (string) fandeco_option( 'payment_methods', '' ) ) ) );
$shipping = (string) fandeco_option( 'shipping_info', '' );
$cats     = array();
if ( fandeco_has_woocommerce() ) {
	$terms = get_terms(
		array(
			'taxonomy'   => 'product_cat',
			'parent'     => 0,
			'number'     => 6,
			'hide_empty' => true,
			'exclude'    => (int) get_option( 'default_product_cat' ),
		)
	);
	$cats  = is_wp_error( $terms ) ? array() : $terms;
}
?>
<?php
$cta_title = trim( (string) fandeco_option( 'footer_cta_title', '¿Querés algo a tu medida?' ) );
$cta_text  = trim( (string) fandeco_option( 'footer_cta_text', 'Contanos tu idea y la diseñamos e imprimimos en 3D.' ) );
$cta_wa    = $whatsapp ? 'https://wa.me/' . preg_replace( '/\D+/', '', $whatsapp ) . '?text=' . rawurlencode( (string) fandeco_option( 'whatsapp_message', 'Hola, quiero consultar por un producto' ) ) : '';
$cta_mail  = sanitize_email( $email );
?>
<footer class="fd-footer">
	<?php if ( '' !== $cta_title && ( $cta_wa || $cta_mail ) ) : ?>
		<div class="fd-footer__cta">
			<div class="fd-container fd-footer__cta-inner">
				<div>
					<p class="fd-footer__cta-title"><?php echo esc_html( $cta_title ); ?></p>
					<?php if ( '' !== $cta_text ) : ?><p class="fd-footer__cta-text"><?php echo esc_html( $cta_text ); ?></p><?php endif; ?>
				</div>
				<div class="fd-footer__cta-actions">
					<?php if ( $cta_wa ) : ?><a class="fd-btn fd-btn--light" href="<?php echo esc_url( $cta_wa ); ?>" target="_blank" rel="noopener"><?php echo fandeco_icon( 'chat' ); // phpcs:ignore WordPress.Security.EscapeOutput ?> <?php esc_html_e( 'Escribinos por WhatsApp', 'fan-deco-studio' ); ?></a><?php endif; ?>
					<?php if ( $cta_mail ) : ?><a class="fd-btn fd-btn--ghost-light" href="<?php echo esc_url( 'mailto:' . $cta_mail ); ?>"><?php esc_html_e( 'Enviar un email', 'fan-deco-studio' ); ?></a><?php endif; ?>
				</div>
			</div>
		</div>
	<?php endif; ?>
	<div class="fd-container fd-footer__main">
		<div class="fd-footer__grid">
			<div class="fd-footer__brand">
				<?php echo fandeco_logo( 'light' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
				<?php if ( $about ) : ?>
					<p><?php echo esc_html( $about ); ?></p>
				<?php endif; ?>
				<?php if ( $social ) : ?>
					<ul class="fd-footer__social" aria-label="<?php esc_attr_e( 'Redes sociales', 'fan-deco-studio' ); ?>">
						<?php foreach ( $social as $label => $url ) : ?>
							<li><a href="<?php echo esc_url( $url ); ?>" rel="noopener" target="_blank"><?php echo esc_html( $label ); ?></a></li>
						<?php endforeach; ?>
					</ul>
				<?php endif; ?>
			</div>

			<nav class="fd-footer__col" aria-label="<?php esc_attr_e( 'Navegación del footer', 'fan-deco-studio' ); ?>">
				<h2 class="fd-footer__title"><?php esc_html_e( 'Navegación', 'fan-deco-studio' ); ?></h2>
				<?php
				wp_nav_menu(
					array(
						'theme_location' => 'footer',
						'container'      => false,
						'menu_class'     => 'fd-footer__list',
						'fallback_cb'    => false,
						'depth'          => 1,
					)
				);
				?>
			</nav>

			<?php if ( $cats ) : ?>
				<div class="fd-footer__col">
					<h2 class="fd-footer__title"><?php esc_html_e( 'Categorías', 'fan-deco-studio' ); ?></h2>
					<ul class="fd-footer__list">
						<?php foreach ( $cats as $cat ) : ?>
							<li><a href="<?php echo esc_url( get_term_link( $cat ) ); ?>"><?php echo esc_html( $cat->name ); ?></a></li>
						<?php endforeach; ?>
					</ul>
				</div>
			<?php endif; ?>

			<div class="fd-footer__col">
				<h2 class="fd-footer__title"><?php esc_html_e( 'Contacto', 'fan-deco-studio' ); ?></h2>
				<ul class="fd-footer__list">
					<?php if ( $email ) : ?><li><a href="mailto:<?php echo esc_attr( antispambot( $email ) ); ?>"><?php echo esc_html( antispambot( $email ) ); ?></a></li><?php endif; ?>
					<?php if ( $whatsapp ) : ?><li><a href="<?php echo esc_url( 'https://wa.me/' . $whatsapp ); ?>" rel="noopener" target="_blank">WhatsApp</a></li><?php endif; ?>
					<?php if ( $phone ) : ?><li><?php echo esc_html( $phone ); ?></li><?php endif; ?>
					<?php if ( $address ) : ?><li><?php echo esc_html( $address ); ?></li><?php endif; ?>
					<?php if ( $hours ) : ?><li><?php echo esc_html( $hours ); ?></li><?php endif; ?>
				</ul>
				<?php if ( $shipping ) : ?>
					<h2 class="fd-footer__title fd-footer__title--spaced"><?php esc_html_e( 'Envíos', 'fan-deco-studio' ); ?></h2>
					<p class="fd-footer__text"><?php echo esc_html( $shipping ); ?></p>
				<?php endif; ?>
			</div>
		</div>

		<?php if ( $payments ) : ?>
			<div class="fd-footer__payments">
				<span class="fd-footer__title"><?php esc_html_e( 'Medios de pago', 'fan-deco-studio' ); ?></span>
				<ul>
					<?php foreach ( $payments as $method ) : ?>
						<li><?php echo esc_html( $method ); ?></li>
					<?php endforeach; ?>
				</ul>
			</div>
		<?php endif; ?>

		<div class="fd-footer__bottom">
			<p>
				<?php
				$legal = (string) fandeco_option( 'copyright', '' );
				echo esc_html( $legal ? $legal : sprintf( '© %1$s %2$s', gmdate( 'Y' ), get_bloginfo( 'name' ) ) );
				?>
			</p>
			<?php
			wp_nav_menu(
				array(
					'theme_location' => 'footer_legal',
					'container'      => false,
					'menu_class'     => 'fd-footer__legal',
					'fallback_cb'    => false,
					'depth'          => 1,
				)
			);
			?>
			<!-- Suscripción a novedades: funcionalidad futura (no implementada en el MVP). -->
		</div>
	</div>
<?php if ( ! get_theme_mod( 'custom_logo' ) ) : ?>
	<div class="fd-footer__wordmark" aria-hidden="true"><span class="fd-wm-fan">fan</span><span class="fd-wm-deco">DECO</span></div>
<?php endif; ?>
</footer>
<?php if ( $whatsapp && fandeco_option( 'whatsapp_float', false ) ) : ?>
	<a class="fd-whatsapp" href="<?php echo esc_url( 'https://wa.me/' . $whatsapp . '?text=' . rawurlencode( (string) fandeco_option( 'whatsapp_message', 'Hola, quiero consultar por un producto' ) ) ); ?>" target="_blank" rel="noopener" aria-label="<?php esc_attr_e( 'Escribinos por WhatsApp', 'fan-deco-studio' ); ?>">
		<svg class="fd-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="M21 11.5a8.4 8.4 0 0 1-12.4 7.4L3 20.5l1.7-5.4A8.5 8.5 0 1 1 21 11.5z"/><path d="M9 9.5c0 3 2.5 5.5 5.5 5.5l1.5-1.5-2-1-1 .8a4 4 0 0 1-2-2l.8-1-1-2z"/></svg>
	</a>
<?php endif; ?>
<nav class="fd-tabbar" aria-label="<?php esc_attr_e( 'Navegación rápida', 'fan-deco-studio' ); ?>">
	<?php
	$fd_wc    = fandeco_has_woocommerce();
	$fd_tabs  = array(
		array( 'home', __( 'Inicio', 'fan-deco-studio' ), home_url( '/' ), is_front_page() ),
	);
	if ( $fd_wc ) {
		$fd_tabs[] = array( 'grid', __( 'Tienda', 'fan-deco-studio' ), wc_get_page_permalink( 'shop' ), is_shop() || is_product_taxonomy() );
	}
	foreach ( $fd_tabs as $fd_tab ) {
		printf(
			'<a class="fd-tab" href="%1$s"%2$s>%3$s<span class="fd-tab__label">%4$s</span></a>',
			esc_url( $fd_tab[2] ),
			$fd_tab[3] ? ' aria-current="page"' : '',
			fandeco_icon( $fd_tab[0] ), // phpcs:ignore WordPress.Security.EscapeOutput
			esc_html( $fd_tab[1] )
		);
	}
	if ( $fd_wc ) {
		echo fandeco_tab_cart_html( is_cart() ); // phpcs:ignore WordPress.Security.EscapeOutput -- escapado en la función.
		printf(
			'<a class="fd-tab" href="%1$s"%2$s>%3$s<span class="fd-tab__label">%4$s</span></a>',
			esc_url( wc_get_page_permalink( 'myaccount' ) ),
			is_account_page() ? ' aria-current="page"' : '',
			fandeco_icon( 'user' ), // phpcs:ignore WordPress.Security.EscapeOutput
			esc_html__( 'Cuenta', 'fan-deco-studio' )
		);
	}
	?>
</nav>
<?php wp_footer(); ?>
</body>
</html>
