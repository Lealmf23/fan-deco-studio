<?php
/**
 * Página de Contacto (se aplica sola a la página con slug "contacto").
 * Muestra los medios de contacto de Personalizar > Fan Deco > Contacto y, debajo, el contenido de la página
 * (el formulario que ya exista: WPForms, Contact Form 7, etc.). El tema no procesa formularios propios.
 *
 * @package FanDecoStudio
 */

defined( 'ABSPATH' ) || exit;

get_header();
$fd_methods = fandeco_contact_methods();
$fd_social  = fandeco_social_links();
?>
<main id="main" class="fd-main fd-contact" tabindex="-1">
	<?php
	while ( have_posts() ) :
		the_post();
		$fd_content = trim( (string) get_the_content() );
		?>
		<section class="fd-contact__hero fd-night" data-fd-reveal>
			<div class="fd-container">
				<p class="fd-eyebrow"><?php esc_html_e( 'Hablemos', 'fan-deco-studio' ); ?></p>
				<h1 class="fd-contact__title"><?php the_title(); ?></h1>
				<p class="fd-contact__lead"><?php esc_html_e( '¿Una duda, un regalo a medida o un pedido especial? Escribinos y lo resolvemos juntos.', 'fan-deco-studio' ); ?></p>
			</div>
		</section>

		<div class="fd-container fd-contact__body">
			<?php if ( $fd_methods ) : ?>
				<ul class="fd-contact__list" data-fd-reveal>
					<?php foreach ( $fd_methods as $m ) : ?>
						<li class="fd-contact__item">
							<span class="fd-contact__icon"><?php echo fandeco_icon( $m['icon'] ); // phpcs:ignore WordPress.Security.EscapeOutput ?></span>
							<span class="fd-contact__label"><?php echo esc_html( $m['label'] ); ?></span>
							<?php if ( $m['url'] ) : ?>
								<a class="fd-contact__value" href="<?php echo esc_url( $m['url'] ); ?>"<?php echo 0 === strpos( $m['url'], 'http' ) ? ' target="_blank" rel="noopener"' : ''; ?>><?php echo esc_html( $m['value'] ); ?></a>
							<?php else : ?>
								<span class="fd-contact__value"><?php echo esc_html( $m['value'] ); ?></span>
							<?php endif; ?>
						</li>
					<?php endforeach; ?>
				</ul>
			<?php endif; ?>

			<?php if ( '' !== $fd_content ) : ?>
				<div class="fd-contact__form fd-prose" data-fd-reveal>
					<h2><?php esc_html_e( 'Escribinos', 'fan-deco-studio' ); ?></h2>
					<?php the_content(); ?>
				</div>
			<?php endif; ?>

			<?php if ( $fd_social ) : ?>
				<p class="fd-contact__social">
					<span><?php esc_html_e( 'Seguinos', 'fan-deco-studio' ); ?></span>
					<?php foreach ( $fd_social as $label => $url ) : ?>
						<a href="<?php echo esc_url( $url ); ?>" target="_blank" rel="noopener"><?php echo esc_html( $label ); ?></a>
					<?php endforeach; ?>
				</p>
			<?php endif; ?>
		</div>
	<?php endwhile; ?>
</main>
<?php
get_footer();
