<?php
/**
 * Ficha de producto ampliada: características, tiempo de producción, ítems de confianza (garantía, cambios,
 * calificación), tarjetas informativas y tabla de especificaciones. Todo editable; NADA viene precargado con
 * datos comerciales (garantías, plazos, calificaciones): solo se muestra lo que se carga.
 *
 * Global: WooCommerce → Ficha de producto. Por producto: pestaña "Ficha Fan Deco".
 *
 * @package FanDecoCore
 */

defined( 'ABSPATH' ) || exit;

final class FanDeco_Product_Info {
	public const OPTION = 'fandeco_core_pinfo';
	public const META   = '_fandeco_pinfo';

	public static function init(): void {
		add_action( 'admin_menu', array( __CLASS__, 'menu' ) );
		add_action( 'admin_init', array( __CLASS__, 'register' ) );
		add_filter( 'woocommerce_product_data_tabs', array( __CLASS__, 'admin_tab' ) );
		add_action( 'woocommerce_product_data_panels', array( __CLASS__, 'admin_panel' ) );
		add_action( 'woocommerce_process_product_meta', array( __CLASS__, 'save_product' ) );
		add_action( 'woocommerce_single_product_summary', array( __CLASS__, 'render_features' ), 25 );
		add_action( 'woocommerce_single_product_summary', array( __CLASS__, 'render_trust' ), 35 );
		add_action( 'woocommerce_single_product_summary', array( __CLASS__, 'render_cards' ), 36 );
		add_filter( 'woocommerce_product_tabs', array( __CLASS__, 'tabs' ), 98 );
	}

	/* ---------- Datos ---------- */

	public static function globals(): array {
		$saved = get_option( self::OPTION, array() );
		return array_merge( array( 'trust' => '', 'cards' => '', 'rating_value' => '', 'rating_label' => 'en reseñas de Google', 'rating_url' => '' ), is_array( $saved ) ? $saved : array() );
	}

	public static function product_data( int $id ): array {
		$d = get_post_meta( $id, self::META, true );
		return array_merge( array( 'production' => '', 'features' => '', 'specs' => '', 'hide_trust' => 0, 'hide_cards' => 0 ), is_array( $d ) ? $d : array() );
	}

	public static function icon( string $name ): string {
		static $p = array(
			'check'  => '<path d="m5 12 5 5 9-10"/>',
			'shield' => '<path d="M20 13c0 5-3.5 7.5-7.66 8.95a1 1 0 0 1-.67-.01C7.5 20.5 4 18 4 13V6a1 1 0 0 1 1-1c2 0 4.5-1.2 6.24-2.72a1.17 1.17 0 0 1 1.52 0C14.51 3.81 17 5 19 5a1 1 0 0 1 1 1z"/><path d="m9 12 2 2 4-4"/>',
			'truck'  => '<path d="M14 18V6a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2v11a1 1 0 0 0 1 1h2"/><path d="M15 18H9"/><path d="M19 18h2a1 1 0 0 0 1-1v-3.65a1 1 0 0 0-.22-.624l-3.48-4.35A1 1 0 0 0 17.52 8H14"/><circle cx="17" cy="18" r="2"/><circle cx="7" cy="18" r="2"/>',
			'return' => '<path d="M3 12a9 9 0 1 0 3-6.7L3 8"/><path d="M3 3v5h5"/>',
			'star'   => '<path d="m12 2 3.1 6.3 6.9 1-5 4.9 1.2 6.8L12 17.8 5.8 21l1.2-6.8-5-4.9 6.9-1z"/>',
			'leaf'   => '<path d="M11 20A7 7 0 0 1 9.8 6.1C15.5 5 17 4.5 19 2c1 2 2 4.2 2 8 0 5.5-4.8 10-10 10z"/><path d="M2 21c0-3 1.9-5.4 5-6.4"/>',
			'card'   => '<rect width="20" height="14" x="2" y="5" rx="2"/><path d="M2 10h20"/>',
			'lock'   => '<rect width="18" height="11" x="3" y="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/>',
			'gift'   => '<rect x="3" y="8" width="18" height="4" rx="1"/><path d="M12 8v13"/><path d="M19 12v7a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2v-7"/><path d="M7.5 8a2.5 2.5 0 0 1 0-5A4.8 8 0 0 1 12 8a4.8 8 0 0 1 4.5-5 2.5 2.5 0 0 1 0 5"/>',
			'tag'    => '<path d="M12.586 2.586A2 2 0 0 0 11.172 2H4a2 2 0 0 0-2 2v7.172a2 2 0 0 0 .586 1.414l8.704 8.704a2.426 2.426 0 0 0 3.42 0l6.58-6.58a2.426 2.426 0 0 0 0-3.42z"/><circle cx="7.5" cy="7.5" r=".5"/>',
			'clock'  => '<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/>',
			'pin'    => '<path d="M20 10c0 6-8 12-8 12S4 16 4 10a8 8 0 0 1 16 0z"/><circle cx="12" cy="10" r="3"/>',
		);
		$path = $p[ $name ] ?? $p['check'];
		return '<svg class="fd-pinfo-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">' . $path . '</svg>'; // phpcs:ignore WordPress.Security.EscapeOutput -- constante interna.
	}

	/* ---------- Frontend ---------- */

	public static function render_features(): void {
		global $product;
		if ( ! $product instanceof WC_Product ) {
			return;
		}
		$items = FanDeco_Pinfo_Parse::lines( (string) self::product_data( $product->get_id() )['features'], 8 );
		if ( ! $items ) {
			return;
		}
		echo '<ul class="fd-pinfo-features">';
		foreach ( $items as $item ) {
			echo '<li>' . self::icon( 'check' ) . '<span>' . esc_html( $item ) . '</span></li>'; // phpcs:ignore WordPress.Security.EscapeOutput
		}
		echo '</ul>';
	}

	public static function render_trust(): void {
		global $product;
		if ( ! $product instanceof WC_Product ) {
			return;
		}
		$g    = self::globals();
		$d    = self::product_data( $product->get_id() );
		$rows = array();
		if ( ! $d['hide_trust'] ) {
			foreach ( FanDeco_Pinfo_Parse::trust( (string) $g['trust'] ) as $t ) {
				$rows[] = self::icon( $t['icon'] ) . '<span>' . esc_html( $t['text'] ) . '</span>';
			}
		}
		$prod = trim( (string) $d['production'] );
		if ( '' !== $prod ) {
			$rows[] = self::icon( 'clock' ) . '<span>' . esc_html( mb_substr( wp_strip_all_tags( $prod ), 0, 120 ) ) . '</span>';
		}
		$val = trim( (string) $g['rating_value'] );
		if ( '' !== $val && ! $d['hide_trust'] ) {
			$label = esc_html( (string) $g['rating_label'] );
			$text  = '<strong>' . esc_html( $val ) . '</strong> ' . $label;
			$url   = esc_url( (string) $g['rating_url'] );
			$rows[] = self::icon( 'star' ) . '<span>' . ( $url ? '<a href="' . $url . '" rel="noopener" target="_blank">' . $text . '</a>' : $text ) . '</span>';
		}
		if ( ! $rows ) {
			return;
		}
		echo '<ul class="fd-pinfo-trust"><li>' . implode( '</li><li>', $rows ) . '</li></ul>'; // phpcs:ignore WordPress.Security.EscapeOutput -- cada pieza escapada arriba.
	}

	public static function render_cards(): void {
		global $product;
		if ( ! $product instanceof WC_Product || self::product_data( $product->get_id() )['hide_cards'] ) {
			return;
		}
		$cards = FanDeco_Pinfo_Parse::cards( (string) self::globals()['cards'] );
		if ( ! $cards ) {
			return;
		}
		echo '<div class="fd-pinfo-cards">';
		foreach ( $cards as $c ) {
			printf(
				'<div class="fd-pinfo-card fd-pinfo-card--%1$s">%2$s<div><strong>%3$s</strong>%4$s</div></div>',
				esc_attr( $c['tone'] ),
				self::icon( $c['icon'] ), // phpcs:ignore WordPress.Security.EscapeOutput
				esc_html( $c['title'] ),
				$c['text'] ? '<span>' . esc_html( $c['text'] ) . '</span>' : '' // phpcs:ignore WordPress.Security.EscapeOutput
			);
		}
		echo '</div>';
	}

	/** Filas de especificaciones: medidas y peso nativos de WooCommerce + atributos visibles + filas propias. */
	public static function spec_rows( WC_Product $product ): array {
		$rows = array();
		if ( $product->has_dimensions() ) {
			$rows[] = array( __( 'Medidas', 'fan-deco-core' ), wc_format_dimensions( $product->get_dimensions( false ) ) );
		}
		if ( $product->has_weight() ) {
			$rows[] = array( __( 'Peso', 'fan-deco-core' ), wc_format_weight( $product->get_weight() ) );
		}
		foreach ( $product->get_attributes() as $attr ) {
			if ( ! $attr->get_visible() || $attr->get_variation() ) {
				continue; // los de variación ya se eligen en los selectores
			}
			$name = wc_attribute_label( $attr->get_name() );
			$vals = $attr->is_taxonomy() ? wc_get_product_terms( $product->get_id(), $attr->get_name(), array( 'fields' => 'names' ) ) : $attr->get_options();
			if ( $vals ) {
				$rows[] = array( $name, implode( ', ', array_map( 'wp_strip_all_tags', (array) $vals ) ) );
			}
		}
		foreach ( FanDeco_Pinfo_Parse::specs( (string) self::product_data( $product->get_id() )['specs'] ) as $r ) {
			$rows[] = $r;
		}
		return $rows;
	}

	public static function tabs( array $tabs ): array {
		global $product;
		if ( ! $product instanceof WC_Product ) {
			return $tabs;
		}
		$rows = self::spec_rows( $product );
		if ( ! $rows ) {
			return $tabs;
		}
		unset( $tabs['additional_information'] ); // sus datos ya están en esta tabla (sin duplicar)
		$tabs['fandeco_specs'] = array(
			'title'    => __( 'Especificaciones', 'fan-deco-core' ),
			'priority' => 20,
			'callback' => static function () use ( $rows ) {
				echo '<h2>' . esc_html__( 'Especificaciones', 'fan-deco-core' ) . '</h2><table class="fd-pinfo-specs"><tbody>';
				foreach ( $rows as $r ) {
					echo '<tr><th scope="row">' . esc_html( $r[0] ) . '</th><td>' . esc_html( $r[1] ) . '</td></tr>';
				}
				echo '</tbody></table>';
			},
		);
		return $tabs;
	}

	/* ---------- Administración: por producto ---------- */

	public static function admin_tab( array $tabs ): array {
		$tabs['fandeco_pinfo'] = array( 'label' => __( 'Ficha Fan Deco', 'fan-deco-core' ), 'target' => 'fandeco_pinfo_panel', 'class' => array(), 'priority' => 75 );
		return $tabs;
	}

	public static function admin_panel(): void {
		global $post;
		$d = self::product_data( (int) $post->ID );
		wp_nonce_field( 'fandeco_pinfo', 'fandeco_pinfo_nonce' );
		?>
		<div id="fandeco_pinfo_panel" class="panel woocommerce_options_panel hidden">
			<div class="options_group">
				<p class="form-field"><label for="fdp_features"><?php esc_html_e( 'Características destacadas', 'fan-deco-core' ); ?></label>
					<textarea id="fdp_features" name="fdp[features]" rows="4" style="width:60%" placeholder="Luz cálida&#10;Cable de 1,5 m incluido"><?php echo esc_textarea( (string) $d['features'] ); ?></textarea>
					<span class="description" style="display:block;clear:both;margin-left:0"><?php esc_html_e( 'Una por línea (máx. 8). Se muestran con tilde debajo de la descripción corta.', 'fan-deco-core' ); ?></span></p>
				<p class="form-field"><label for="fdp_production"><?php esc_html_e( 'Tiempo de producción / entrega', 'fan-deco-core' ); ?></label>
					<input type="text" id="fdp_production" name="fdp[production]" value="<?php echo esc_attr( (string) $d['production'] ); ?>" placeholder="Se fabrica a pedido: 3 a 5 días hábiles"></p>
			</div>
			<div class="options_group">
				<p class="form-field"><label for="fdp_specs"><?php esc_html_e( 'Especificaciones propias', 'fan-deco-core' ); ?></label>
					<textarea id="fdp_specs" name="fdp[specs]" rows="5" style="width:60%" placeholder="Material | Bioplástico PLA&#10;Garantía | 6 meses"><?php echo esc_textarea( (string) $d['specs'] ); ?></textarea>
					<span class="description" style="display:block;clear:both;margin-left:0"><?php esc_html_e( 'Una por línea: “Etiqueta | Valor”. Medidas y peso salen de la pestaña Envío de WooCommerce; los atributos visibles también se agregan solos.', 'fan-deco-core' ); ?></span></p>
			</div>
			<div class="options_group">
				<p class="form-field"><label><?php esc_html_e( 'Ocultar en este producto', 'fan-deco-core' ); ?></label>
					<label style="width:auto;float:none;margin:0"><input type="checkbox" name="fdp[hide_trust]" value="1" <?php checked( (int) $d['hide_trust'], 1 ); ?>> <?php esc_html_e( 'Ítems de confianza globales (garantía, cambios, calificación)', 'fan-deco-core' ); ?></label><br>
					<label style="width:auto;float:none;margin:0"><input type="checkbox" name="fdp[hide_cards]" value="1" <?php checked( (int) $d['hide_cards'], 1 ); ?>> <?php esc_html_e( 'Tarjetas informativas globales', 'fan-deco-core' ); ?></label></p>
			</div>
		</div>
		<?php
	}

	public static function save_product( int $post_id ): void {
		if ( ! isset( $_POST['fandeco_pinfo_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['fandeco_pinfo_nonce'] ) ), 'fandeco_pinfo' ) || ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}
		$in   = isset( $_POST['fdp'] ) && is_array( $_POST['fdp'] ) ? wp_unslash( $_POST['fdp'] ) : array(); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
		$data = array(
			'features'   => implode( "\n", FanDeco_Pinfo_Parse::lines( (string) ( $in['features'] ?? '' ), 8 ) ),
			'production' => mb_substr( sanitize_text_field( (string) ( $in['production'] ?? '' ) ), 0, 120 ),
			'specs'      => implode( "\n", array_map( static fn( $r ) => $r[0] . ' | ' . $r[1], FanDeco_Pinfo_Parse::specs( (string) ( $in['specs'] ?? '' ) ) ) ),
			'hide_trust' => empty( $in['hide_trust'] ) ? 0 : 1,
			'hide_cards' => empty( $in['hide_cards'] ) ? 0 : 1,
		);
		$empty = '' === $data['features'] && '' === $data['production'] && '' === $data['specs'] && ! $data['hide_trust'] && ! $data['hide_cards'];
		$empty ? delete_post_meta( $post_id, self::META ) : update_post_meta( $post_id, self::META, $data );
	}

	/* ---------- Administración: global ---------- */

	public static function menu(): void {
		add_submenu_page( 'woocommerce', 'Ficha de producto', 'Ficha de producto', 'manage_woocommerce', 'fandeco-pinfo', array( __CLASS__, 'render' ) );
	}

	public static function register(): void {
		register_setting( 'fandeco_pinfo', self::OPTION, array( 'type' => 'array', 'sanitize_callback' => array( __CLASS__, 'sanitize' ), 'default' => array() ) );
	}

	public static function sanitize( $input ): array {
		$in = is_array( $input ) ? $input : array();
		$tr = array_map( static fn( $t ) => $t['icon'] . ' | ' . $t['text'], FanDeco_Pinfo_Parse::trust( (string) ( $in['trust'] ?? '' ) ) );
		$cd = array_map( static fn( $c ) => implode( ' | ', array( $c['icon'], $c['title'], $c['text'], $c['tone'] ) ), FanDeco_Pinfo_Parse::cards( (string) ( $in['cards'] ?? '' ) ) );
		return array(
			'trust'        => implode( "\n", $tr ),
			'cards'        => implode( "\n", $cd ),
			'rating_value' => mb_substr( sanitize_text_field( (string) ( $in['rating_value'] ?? '' ) ), 0, 8 ),
			'rating_label' => mb_substr( sanitize_text_field( (string) ( $in['rating_label'] ?? '' ) ), 0, 60 ),
			'rating_url'   => esc_url_raw( (string) ( $in['rating_url'] ?? '' ) ),
		);
	}

	public static function render(): void {
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			return;
		}
		$g = self::globals();
		$n = self::OPTION;
		?>
		<div class="wrap">
			<h1>Ficha de producto</h1>
			<p>Contenido que se repite en todas las fichas. <strong>Nada viene precargado</strong>: solo se muestra lo que cargues acá (garantías, plazos y calificaciones deben ser reales). Cada producto puede ocultarlos o sumar los suyos en su pestaña “Ficha Fan Deco”.</p>
			<p class="description">Iconos disponibles: <code><?php echo esc_html( implode( ' ', FanDeco_Pinfo_Parse::ICONS ) ); ?></code></p>
			<form method="post" action="options.php">
				<?php settings_fields( 'fandeco_pinfo' ); ?>
				<table class="form-table" role="presentation">
					<tr><th scope="row"><label for="fdg_trust">Ítems de confianza</label></th><td>
						<textarea id="fdg_trust" class="large-text code" rows="5" name="<?php echo esc_attr( $n ); ?>[trust]" placeholder="shield | 6 meses de garantía&#10;return | 30 días para devolverlo"><?php echo esc_textarea( (string) $g['trust'] ); ?></textarea>
						<p class="description">Una por línea: <code>icono | texto</code> (máx. 6). Aparecen debajo del botón de compra.</p></td></tr>
					<tr><th scope="row"><label for="fdg_cards">Tarjetas informativas</label></th><td>
						<textarea id="fdg_cards" class="large-text code" rows="5" name="<?php echo esc_attr( $n ); ?>[cards]" placeholder="lock | Compra protegida | Tus datos están protegidos | neutral&#10;leaf | Material sustentable | Fabricado con bioplástico PLA | green"><?php echo esc_textarea( (string) $g['cards'] ); ?></textarea>
						<p class="description">Una por línea: <code>icono | título | texto | tono</code> (tono: neutral, green o wine; máx. 4).</p></td></tr>
					<tr><th scope="row">Calificación (opcional)</th><td>
						<input type="text" class="small-text" name="<?php echo esc_attr( $n ); ?>[rating_value]" value="<?php echo esc_attr( (string) $g['rating_value'] ); ?>" placeholder="5,0">
						<input type="text" class="regular-text" name="<?php echo esc_attr( $n ); ?>[rating_label]" value="<?php echo esc_attr( (string) $g['rating_label'] ); ?>">
						<input type="url" class="regular-text" name="<?php echo esc_attr( $n ); ?>[rating_url]" value="<?php echo esc_attr( (string) $g['rating_url'] ); ?>" placeholder="https://… (opcional)">
						<p class="description">Se muestra solo si completás el valor, y <strong>debe ser tu calificación real</strong>. No genera datos estructurados (los sitios no pueden autoevaluarse en Google).</p></td></tr>
				</table>
				<?php submit_button(); ?>
			</form>
		</div>
		<?php
	}
}
