<?php
/**
 * Customizer: datos de marca, contacto, redes y footer (todo editable sin tocar PHP).
 *
 * @package FanDecoStudio
 */

defined( 'ABSPATH' ) || exit;

add_action(
	'customize_register',
	static function ( WP_Customize_Manager $wp_customize ) {
		$wp_customize->add_panel(
			'fandeco',
			array(
				'title'    => __( 'Fan Deco Studio', 'fan-deco-studio' ),
				'priority' => 30,
			)
		);

		$text = static function ( string $section, string $id, string $label, string $type = 'text', string $sanitize = 'sanitize_text_field', string $default = '' ) use ( $wp_customize ) {
			$wp_customize->add_setting(
				"fandeco_{$id}",
				array(
					'default'           => $default,
					'sanitize_callback' => $sanitize,
				)
			);
			$wp_customize->add_control(
				"fandeco_{$id}",
				array(
					'section' => $section,
					'label'   => $label,
					'type'    => $type,
				)
			);
		};

		// Marca / header.
		$wp_customize->add_section( 'fandeco_brand', array( 'title' => __( 'Marca y header', 'fan-deco-studio' ), 'panel' => 'fandeco' ) );
		$wp_customize->add_setting( 'fandeco_logo_light', array( 'default' => 0, 'sanitize_callback' => 'absint' ) );
		$wp_customize->add_control(
			new WP_Customize_Media_Control(
				$wp_customize,
				'fandeco_logo_light',
				array(
					'section'   => 'fandeco_brand',
					'label'     => __( 'Logo para fondos oscuros (variante clara)', 'fan-deco-studio' ),
					'mime_type' => 'image',
				)
			)
		);
		$wp_customize->add_setting( 'fandeco_sticky_header', array( 'default' => true, 'sanitize_callback' => 'rest_sanitize_boolean' ) );
		$wp_customize->add_control( 'fandeco_sticky_header', array( 'section' => 'fandeco_brand', 'label' => __( 'Header fijo al hacer scroll', 'fan-deco-studio' ), 'type' => 'checkbox' ) );
		$text( 'fandeco_brand', 'announcement', __( 'Barra superior: mensajes (uno por línea; si hay varios, rotan)', 'fan-deco-studio' ), 'textarea', 'sanitize_textarea_field' );
		$text( 'fandeco_brand', 'announcement_url', __( 'Barra superior: enlace (opcional)', 'fan-deco-studio' ), 'url', 'esc_url_raw' );

		// Contacto.
		$wp_customize->add_section( 'fandeco_contact', array( 'title' => __( 'Contacto', 'fan-deco-studio' ), 'panel' => 'fandeco' ) );
		$text( 'fandeco_contact', 'email', __( 'Email', 'fan-deco-studio' ), 'email', 'sanitize_email' );
		$text( 'fandeco_contact', 'phone', __( 'Teléfono', 'fan-deco-studio' ) );
		$wp_customize->add_setting( 'fandeco_whatsapp_float', array( 'default' => false, 'sanitize_callback' => 'rest_sanitize_boolean' ) );
		$wp_customize->add_control( 'fandeco_whatsapp_float', array( 'section' => 'fandeco_contact', 'label' => __( 'Mostrar botón flotante de WhatsApp (requiere el número de abajo)', 'fan-deco-studio' ), 'type' => 'checkbox' ) );
		$text( 'fandeco_contact', 'whatsapp_message', __( 'Mensaje inicial de WhatsApp', 'fan-deco-studio' ), 'text', 'sanitize_text_field', 'Hola, quiero consultar por un producto' );
		$text( 'fandeco_contact', 'whatsapp', __( 'WhatsApp (solo números, con código de país)', 'fan-deco-studio' ), 'text', static fn( $v ) => preg_replace( '/\D+/', '', (string) $v ) );
		$text( 'fandeco_contact', 'address', __( 'Ubicación', 'fan-deco-studio' ), 'text', 'sanitize_text_field', 'Palermo, Buenos Aires, Argentina' );
		$text( 'fandeco_contact', 'hours', __( 'Horarios de atención', 'fan-deco-studio' ) );

		// Redes.
		$wp_customize->add_section( 'fandeco_social', array( 'title' => __( 'Redes sociales', 'fan-deco-studio' ), 'panel' => 'fandeco' ) );
		foreach ( array( 'instagram' => 'Instagram', 'facebook' => 'Facebook', 'tiktok' => 'TikTok', 'pinterest' => 'Pinterest', 'youtube' => 'YouTube' ) as $key => $label ) {
			$text( 'fandeco_social', 'social_' . $key, $label . ' (URL)', 'url', 'esc_url_raw' );
		}

		// Footer.
		$wp_customize->add_section( 'fandeco_footer', array( 'title' => __( 'Footer', 'fan-deco-studio' ), 'panel' => 'fandeco' ) );
		$text( 'fandeco_footer', 'footer_about', __( 'Texto de presentación', 'fan-deco-studio' ), 'textarea', 'sanitize_textarea_field', 'Diseñamos y fabricamos objetos decorativos con impresión 3D.' );
		$text( 'fandeco_footer', 'payment_methods', __( 'Métodos de pago (uno por línea)', 'fan-deco-studio' ), 'textarea', 'sanitize_textarea_field' );
		$text( 'fandeco_footer', 'shipping_info', __( 'Información de envíos', 'fan-deco-studio' ), 'textarea', 'sanitize_textarea_field' );
		$text( 'fandeco_footer', 'copyright', __( 'Texto legal inferior (opcional)', 'fan-deco-studio' ) );
	}
);
