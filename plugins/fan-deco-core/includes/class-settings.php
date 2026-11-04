<?php
/**
 * Ajustes globales (Ajustes > Fan Deco SEO).
 *
 * @package FanDecoCore
 */

defined( 'ABSPATH' ) || exit;

final class FanDeco_Settings {
	public const OPTION = 'fandeco_core_settings';

	public static function init(): void {
		add_action( 'admin_menu', array( __CLASS__, 'menu' ) );
		add_action( 'admin_init', array( __CLASS__, 'register' ) );
	}

	public static function defaults(): array {
		return array(
			'sep'              => '|',
			'title_home'       => '%sitename% %sep% %tagline%',
			'title_post'       => '%title% %sep% %sitename%',
			'title_page'       => '%title% %sep% %sitename%',
			'title_product'    => '%title% %sep% %sitename%',
			'title_term'       => '%term% %sep% %sitename%',
			'title_archive'    => '%title% %sep% %sitename%',
			'desc_home'        => '',
			'og_image'         => '',
			'org_name'         => '',
			'org_logo'         => '',
			'org_email'        => '',
			'org_phone'        => '',
			'social'           => '',
			'noindex_tags'     => 1,
			'noindex_author'   => 1,
			'noindex_attach'   => 1,
			'verify_google'    => '',
			'verify_bing'      => '',
			'verify_pinterest' => '',
			'verify_facebook'  => '',
		);
	}

	/** @return mixed */
	public static function get( string $key ) {
		static $cache = null;
		if ( null === $cache ) {
			$saved = get_option( self::OPTION, array() );
			$cache = array_merge( self::defaults(), is_array( $saved ) ? $saved : array() );
		}
		return $cache[ $key ] ?? null;
	}

	public static function flush_cache(): void {
		// El cache estático vive por request; tras guardar no hace falta nada más.
	}

	public static function menu(): void {
		add_options_page( 'Fan Deco SEO', 'Fan Deco SEO', 'manage_options', 'fandeco-seo', array( __CLASS__, 'render' ) );
	}

	public static function register(): void {
		register_setting(
			'fandeco_core',
			self::OPTION,
			array(
				'type'              => 'array',
				'sanitize_callback' => array( __CLASS__, 'sanitize' ),
				'default'           => self::defaults(),
			)
		);
	}

	public static function sanitize( $input ): array {
		$in  = is_array( $input ) ? $input : array();
		$def = self::defaults();
		$out = array();
		foreach ( array( 'sep', 'title_home', 'title_post', 'title_page', 'title_product', 'title_term', 'title_archive' ) as $k ) {
			$out[ $k ] = sanitize_text_field( $in[ $k ] ?? $def[ $k ] ) ?: $def[ $k ];
		}
		$out['desc_home'] = sanitize_textarea_field( $in['desc_home'] ?? '' );
		foreach ( array( 'og_image', 'org_logo' ) as $k ) {
			$out[ $k ] = esc_url_raw( $in[ $k ] ?? '' );
		}
		$out['org_name']  = sanitize_text_field( $in['org_name'] ?? '' );
		$out['org_email'] = sanitize_email( $in['org_email'] ?? '' );
		$out['org_phone'] = sanitize_text_field( $in['org_phone'] ?? '' );
		$urls             = array_filter( array_map( 'esc_url_raw', array_map( 'trim', explode( "\n", (string) ( $in['social'] ?? '' ) ) ) ) );
		$out['social']    = implode( "\n", $urls );
		foreach ( array( 'noindex_tags', 'noindex_author', 'noindex_attach' ) as $k ) {
			$out[ $k ] = empty( $in[ $k ] ) ? 0 : 1;
		}
		// Los códigos de verificación son tokens alfanuméricos.
		foreach ( array( 'verify_google', 'verify_bing', 'verify_pinterest', 'verify_facebook' ) as $k ) {
			$out[ $k ] = preg_replace( '/[^A-Za-z0-9_\-]/', '', (string) ( $in[ $k ] ?? '' ) );
		}
		return $out;
	}

	private static function text( string $key, string $label, string $help = '', string $type = 'text' ): void {
		printf(
			'<tr><th scope="row"><label for="fdc_%1$s">%2$s</label></th><td><input class="regular-text" type="%6$s" id="fdc_%1$s" name="%3$s[%1$s]" value="%4$s">%5$s</td></tr>',
			esc_attr( $key ),
			esc_html( $label ),
			esc_attr( self::OPTION ),
			esc_attr( (string) self::get( $key ) ),
			$help ? '<p class="description">' . esc_html( $help ) . '</p>' : '',
			esc_attr( $type )
		);
	}

	private static function check( string $key, string $label ): void {
		printf(
			'<tr><th scope="row">%2$s</th><td><label><input type="checkbox" name="%3$s[%1$s]" value="1" %4$s> %5$s</label></td></tr>',
			esc_attr( $key ),
			esc_html( $label ),
			esc_attr( self::OPTION ),
			checked( (int) self::get( $key ), 1, false ),
			esc_html__( 'Activado', 'fan-deco-core' )
		);
	}

	public static function render(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		$vars = '%title% %sitename% %tagline% %sep% %term% %page%';
		?>
		<div class="wrap">
			<h1>Fan Deco SEO</h1>
			<?php if ( defined( 'AIOSEO_VERSION' ) || defined( 'WPSEO_VERSION' ) || defined( 'RANK_MATH_VERSION' ) ) : ?>
				<div class="notice notice-warning"><p><strong>Hay otro plugin SEO activo.</strong> Fan Deco Core se desactiva automáticamente en el frontend mientras exista, para no duplicar metadatos. Importá los datos y luego desactivá el otro plugin.</p></div>
			<?php endif; ?>
			<form method="post" action="options.php">
				<?php settings_fields( 'fandeco_core' ); ?>
				<h2>Títulos y descripciones</h2>
				<p class="description">Variables: <code><?php echo esc_html( $vars ); ?></code>. Cada contenido puede sobrescribir título y descripción en su propio panel “Fan Deco SEO”.</p>
				<table class="form-table" role="presentation">
					<?php
					self::text( 'sep', 'Separador' );
					self::text( 'title_home', 'Título: portada' );
					self::text( 'title_post', 'Título: entradas' );
					self::text( 'title_page', 'Título: páginas' );
					self::text( 'title_product', 'Título: productos' );
					self::text( 'title_term', 'Título: categorías/etiquetas' );
					self::text( 'title_archive', 'Título: otros archivos' );
					?>
					<tr><th scope="row"><label for="fdc_desc_home">Descripción de la portada</label></th><td><textarea class="large-text" rows="3" id="fdc_desc_home" name="<?php echo esc_attr( self::OPTION ); ?>[desc_home]"><?php echo esc_textarea( (string) self::get( 'desc_home' ) ); ?></textarea></td></tr>
					<?php self::text( 'og_image', 'Imagen social por defecto (URL)', 'Se usa en Facebook/WhatsApp/X cuando el contenido no tiene imagen. Recomendado 1200×630.', 'url' ); ?>
				</table>
				<h2>Organización y redes</h2>
				<table class="form-table" role="presentation">
					<?php
					self::text( 'org_name', 'Nombre de la organización', 'Si lo dejás vacío se usa el nombre del sitio.' );
					self::text( 'org_logo', 'Logo (URL)', 'Si lo dejás vacío se usa el logo del tema.', 'url' );
					self::text( 'org_email', 'Email de contacto', '', 'email' );
					self::text( 'org_phone', 'Teléfono' );
					?>
					<tr><th scope="row"><label for="fdc_social">Perfiles sociales</label></th><td><textarea class="large-text" rows="4" id="fdc_social" name="<?php echo esc_attr( self::OPTION ); ?>[social]" placeholder="https://www.instagram.com/…"><?php echo esc_textarea( (string) self::get( 'social' ) ); ?></textarea><p class="description">Una URL por línea (se publican como <code>sameAs</code>).</p></td></tr>
				</table>
				<h2>Indexación</h2>
				<table class="form-table" role="presentation">
					<?php
					self::check( 'noindex_tags', 'No indexar etiquetas de producto y de entrada' );
					self::check( 'noindex_author', 'No indexar archivos de autor' );
					self::check( 'noindex_attach', 'No indexar páginas de adjuntos' );
					?>
				</table>
				<h2>Verificación de propiedad</h2>
				<p class="description">Solo el código (contenido del atributo <code>content</code> de la metaetiqueta).</p>
				<table class="form-table" role="presentation">
					<?php
					self::text( 'verify_google', 'Google Search Console' );
					self::text( 'verify_bing', 'Bing Webmaster' );
					self::text( 'verify_pinterest', 'Pinterest' );
					self::text( 'verify_facebook', 'Facebook (dominio)' );
					?>
				</table>
				<?php submit_button(); ?>
			</form>
			<hr>
			<?php FanDeco_Redirects::render_section(); ?>
			<hr>
			<?php FanDeco_Import_AIOSEO::render_section(); ?>
		</div>
		<?php
	}
}
