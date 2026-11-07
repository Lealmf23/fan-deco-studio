<?php
/**
 * Gestor de redirecciones (301/302/307/308). Solo destinos del propio sitio.
 *
 * @package FanDecoCore
 */

defined( 'ABSPATH' ) || exit;

final class FanDeco_Redirects {
	public const OPTION = 'fandeco_core_redirects';

	public static function init(): void {
		add_action( 'template_redirect', array( __CLASS__, 'maybe_redirect' ), 0 );
		add_action( 'admin_post_fdc_save_redirects', array( __CLASS__, 'handle_save' ) );
	}

	/** @return array<int,array{from:string,to:string,code:int}> */
	public static function rules(): array {
		$rules = get_option( self::OPTION, array() );
		return is_array( $rules ) ? $rules : array();
	}

	public static function maybe_redirect(): void {
		if ( is_admin() || wp_doing_ajax() || ( defined( 'REST_REQUEST' ) && REST_REQUEST ) ) {
			return;
		}
		$rules = self::rules();
		if ( ! $rules ) {
			return;
		}
		$uri  = isset( $_SERVER['REQUEST_URI'] ) ? (string) wp_unslash( $_SERVER['REQUEST_URI'] ) : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
		$rule = FanDeco_Redirect_Rules::match( $rules, $uri );
		if ( ! $rule ) {
			return;
		}
		$to = $rule['to'];
		if ( str_starts_with( $to, '/' ) ) {
			$to = home_url( $to );
		}
		// wp_safe_redirect solo admite hosts permitidos (el propio sitio): evita redirecciones abiertas.
		wp_safe_redirect( esc_url_raw( $to ), (int) $rule['code'], 'Fan Deco Core' );
		exit;
	}

	public static function handle_save(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Sin permisos.', 'fan-deco-core' ), 403 );
		}
		check_admin_referer( 'fdc_save_redirects' );
		$text   = isset( $_POST['fdc_redirects'] ) ? sanitize_textarea_field( wp_unslash( $_POST['fdc_redirects'] ) ) : '';
		$parsed = FanDeco_Redirect_Rules::parse( $text );
		update_option( self::OPTION, $parsed['rules'], false );
		set_transient( 'fdc_redirect_errors_' . get_current_user_id(), $parsed['errors'], 60 );
		wp_safe_redirect( admin_url( 'options-general.php?page=fandeco-seo&fdc_saved=1#fdc-redirects' ) );
		exit;
	}

	public static function render_section(): void {
		$errors = get_transient( 'fdc_redirect_errors_' . get_current_user_id() );
		delete_transient( 'fdc_redirect_errors_' . get_current_user_id() );
		?>
		<h2 id="fdc-redirects">Redirecciones</h2>
		<p class="description">Una por línea: <code>/ruta-vieja -&gt; /ruta-nueva 301</code> (el código es opcional; por defecto 301). Solo rutas del propio sitio. Las líneas que empiezan con <code>#</code> se ignoran. <strong>No cambies enlaces permanentes existentes sin una redirección.</strong></p>
		<?php if ( ! empty( $errors ) ) : ?>
			<div class="notice notice-error inline"><ul><?php foreach ( $errors as $e ) { echo '<li>' . esc_html( $e ) . '</li>'; } ?></ul></div>
		<?php endif; ?>
		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
			<input type="hidden" name="action" value="fdc_save_redirects">
			<?php wp_nonce_field( 'fdc_save_redirects' ); ?>
			<textarea class="large-text code" rows="8" name="fdc_redirects"><?php echo esc_textarea( FanDeco_Redirect_Rules::to_text( self::rules() ) ); ?></textarea>
			<?php submit_button( 'Guardar redirecciones' ); ?>
		</form>
		<?php
	}
}
