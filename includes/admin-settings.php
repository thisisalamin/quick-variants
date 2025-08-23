<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; }

/**
 * Admin settings page for Quick Variants.
 */
class Quick_Variants_Settings {
	const OPTION_KEY = 'quick_variants_settings';
	const NONCE_KEY  = 'quick_variants_settings_nonce';

	private static $instance = null;

	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		add_action( 'admin_menu', array( $this, 'add_menu' ) );
		add_action( 'admin_init', array( $this, 'register_settings' ) );
	}

	public function add_menu() {
		add_menu_page(
			__( 'Quick Variants', 'quick-variants' ),
			__( 'Quick Variants', 'quick-variants' ),
			'manage_options',
			'quick-variants-settings',
			array( $this, 'render_page' ),
			'dashicons-screenoptions',
			56
		);
	}

	public function register_settings() {
		register_setting( 'quick_variants_settings_group', self::OPTION_KEY, array( $this, 'sanitize_settings' ) );

		add_settings_section(
			'quick_variants_main_section',
			__( 'Display Settings', 'quick-variants' ),
			'__return_false',
			'quick-variants-settings'
		);

		add_settings_field(
			'default_per_page',
			__( 'Default Products Per Page', 'quick-variants' ),
			array( $this, 'field_number' ),
			'quick-variants-settings',
			'quick_variants_main_section',
			array(
				'label_for' => 'default_per_page',
				'key'       => 'default_per_page',
				'help'      => __( 'Number of products (rows) to load initially.', 'quick-variants' ),
				'attrs'     => array(
					'min' => 1,
					'max' => 100,
				),
			)
		);

		add_settings_field(
			'enable_alphabet_filter',
			__( 'Enable Alphabet Filter', 'quick-variants' ),
			array( $this, 'field_checkbox' ),
			'quick-variants-settings',
			'quick_variants_main_section',
			array(
				'label_for' => 'enable_alphabet_filter',
				'key'       => 'enable_alphabet_filter',
				'help'      => __( 'Show the A-Z filter row above the table.', 'quick-variants' ),
			)
		);

		add_settings_field(
			'show_slide_cart',
			__( 'Enable Slide Cart', 'quick-variants' ),
			array( $this, 'field_checkbox' ),
			'quick-variants-settings',
			'quick_variants_main_section',
			array(
				'label_for' => 'show_slide_cart',
				'key'       => 'show_slide_cart',
				'help'      => __( 'Load and display the slide-out cart template.', 'quick-variants' ),
			)
		);

		add_settings_field(
			'button_color',
			__( 'Primary Button Hex Color', 'quick-variants' ),
			array( $this, 'field_text' ),
			'quick-variants-settings',
			'quick_variants_main_section',
			array(
				'label_for'   => 'button_color',
				'key'         => 'button_color',
				'help'        => __( 'Hex color used for Add to Cart / Show More buttons (#006DB5 by default).', 'quick-variants' ),
				'placeholder' => '#006DB5',
			)
		);
	}

	public function sanitize_settings( $input ) {
		$defaults                         = $this->get_defaults();
		$output                           = array();
		$output['default_per_page']       = isset( $input['default_per_page'] ) ? max( 1, min( 100, intval( $input['default_per_page'] ) ) ) : $defaults['default_per_page'];
		$output['enable_alphabet_filter'] = ! empty( $input['enable_alphabet_filter'] ) ? 1 : 0;
		$output['show_slide_cart']        = ! empty( $input['show_slide_cart'] ) ? 1 : 0;
		$color                            = isset( $input['button_color'] ) ? sanitize_text_field( $input['button_color'] ) : '';
		if ( $color && ! preg_match( '/^#?[0-9a-fA-F]{6}$/', $color ) ) {
			$color = $defaults['button_color'];
		}
		if ( $color && $color[0] !== '#' ) {
			$color = '#' . $color;
		}
		$output['button_color'] = $color ?: $defaults['button_color'];
		return $output;
	}

	private function get_defaults() {
		return array(
			'default_per_page'       => 10,
			'enable_alphabet_filter' => 1,
			'show_slide_cart'        => 1,
			'button_color'           => '#006DB5',
		);
	}

	public static function get_settings() {
		$defaults = self::instance()->get_defaults();
		$current  = get_option( self::OPTION_KEY, array() );
		return wp_parse_args( $current, $defaults );
	}

	public function render_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return; }
		$settings = self::get_settings();
		?>
		<div class="wrap qv-admin">
			<div class="qv-card">
				<h1 class="text-2xl font-bold mb-2 flex items-center gap-2">
					<span class="qv-bg-gradient bg-clip-text text-transparent"><?php esc_html_e( 'Quick Variants', 'quick-variants' ); ?></span>
					<span class="text-xs font-medium px-2 py-0.5 rounded bg-indigo-100 text-indigo-700 align-middle">v<?php echo esc_html( QUICK_VARIANTS_VERSION ); ?></span>
				</h1>
				<p class="text-sm text-gray-600 mb-6"><?php esc_html_e( 'Customize how the product variants table displays on the front-end.', 'quick-variants' ); ?></p>
				<form method="post" action="options.php" class="space-y-8">
					<?php settings_fields( 'quick_variants_settings_group' ); ?>
					<div class="grid grid-cols-1 md:grid-cols-2 gap-8">
						<div>
							<h2 class="qv-section-title mb-4"><?php esc_html_e( 'Display Options', 'quick-variants' ); ?></h2>
							<div class="qv-field">
								<label for="default_per_page" class="qv-label"><?php esc_html_e( 'Default Products Per Page', 'quick-variants' ); ?></label>
								<?php
								$this->field_number(
									array(
										'key'   => 'default_per_page',
										'attrs' => array(
											'min' => 1,
											'max' => 100,
										),
										'help'  => __( 'Number of rows to load initially.', 'quick-variants' ),
									)
								);
								?>
							</div>
							<div class="qv-field">
								<label for="enable_alphabet_filter" class="qv-label"><?php esc_html_e( 'Alphabet Filter', 'quick-variants' ); ?></label>
								<?php
								$this->field_checkbox(
									array(
										'key'  => 'enable_alphabet_filter',
										'help' => __( 'Toggle A-Z filter bar.', 'quick-variants' ),
									)
								);
								?>
							</div>
							<div class="qv-field">
								<label for="show_slide_cart" class="qv-label"><?php esc_html_e( 'Slide Cart', 'quick-variants' ); ?></label>
								<?php
								$this->field_checkbox(
									array(
										'key'  => 'show_slide_cart',
										'help' => __( 'Enable the slide-out cart drawer.', 'quick-variants' ),
									)
								);
								?>
							</div>
						</div>
						<div>
							<h2 class="qv-section-title mb-4"><?php esc_html_e( 'Branding', 'quick-variants' ); ?></h2>
							<div class="qv-field">
								<label for="button_color" class="qv-label"><?php esc_html_e( 'Primary Button Color', 'quick-variants' ); ?></label>
								<div class="qv-color-wrapper">
									<?php
									$this->field_text(
										array(
											'key'         => 'button_color',
											'placeholder' => '#006DB5',
											'help'        => __( 'Used for buttons & progress bar.', 'quick-variants' ),
										)
									);
									?>
								</div>
							</div>
							<div class="mt-8 pt-6 border-t border-gray-200">
								<h2 class="qv-section-title mb-2"><?php esc_html_e( 'Shortcode', 'quick-variants' ); ?></h2>
								<p class="text-sm font-mono bg-gray-50 border border-gray-200 rounded px-3 py-2 inline-block">[quick_variants]</p>
								<p class="text-xs text-gray-500 mt-2"><?php esc_html_e( 'Override defaults:', 'quick-variants' ); ?> <code>[quick_variants per_page="15" category="hoodies"]</code></p>
							</div>
						</div>
					</div>
					<div class="qv-submit-wrapper">
						<?php submit_button( __( 'Save Settings', 'quick-variants' ), 'primary large', 'submit', false, array( 'class' => 'qv-save-button button button-primary' ) ); ?>
					</div>
				</form>
			</div>
		</div>
		<?php
	}

	/* ===== Field Callbacks ===== */
	public function field_number( $args ) {
		$settings = self::get_settings();
		$key      = $args['key'];
		$value    = isset( $settings[ $key ] ) ? intval( $settings[ $key ] ) : '';
		$attrs    = '';
		if ( ! empty( $args['attrs'] ) ) {
			foreach ( $args['attrs'] as $attr => $val ) {
				$attrs .= sprintf( ' %s="%s"', esc_attr( $attr ), esc_attr( $val ) );
			}
		}
		printf( '<input type="number" id="%1$s" name="%2$s[%1$s]" value="%3$s" class="small-text" %4$s />', esc_attr( $key ), esc_attr( self::OPTION_KEY ), esc_attr( $value ), $attrs );
		if ( ! empty( $args['help'] ) ) {
			printf( '<p class="description">%s</p>', esc_html( $args['help'] ) );
		}
	}

	public function field_checkbox( $args ) {
		$settings = self::get_settings();
		$key      = $args['key'];
		$checked  = ! empty( $settings[ $key ] ) ? 'checked' : '';
		printf( '<label><input type="checkbox" id="%1$s" name="%2$s[%1$s]" value="1" %3$s /> %4$s</label>', esc_attr( $key ), esc_attr( self::OPTION_KEY ), $checked, esc_html__( 'Enable', 'quick-variants' ) );
		if ( ! empty( $args['help'] ) ) {
			printf( '<p class="description">%s</p>', esc_html( $args['help'] ) );
		}
	}

	public function field_text( $args ) {
		$settings    = self::get_settings();
		$key         = $args['key'];
		$value       = isset( $settings[ $key ] ) ? esc_attr( $settings[ $key ] ) : '';
		$placeholder = isset( $args['placeholder'] ) ? esc_attr( $args['placeholder'] ) : '';
		$extra_class = ( 'button_color' === $key ) ? ' quick-variants-color-field' : '';
		printf( '<input type="text" id="%1$s" name="%2$s[%1$s]" value="%3$s" placeholder="%5$s" class="regular-text%6$s" />', esc_attr( $key ), esc_attr( self::OPTION_KEY ), $value, esc_attr( $key ), $placeholder, esc_attr( $extra_class ) );
		if ( ! empty( $args['help'] ) ) {
			printf( '<p class="description">%s</p>', esc_html( $args['help'] ) );
		}
	}
}

Quick_Variants_Settings::instance();

/**
 * Helper to fetch a single setting easily.
 */
function quick_variants_get_setting( $key ) {
	$settings = Quick_Variants_Settings::get_settings();
	return isset( $settings[ $key ] ) ? $settings[ $key ] : null;
}
