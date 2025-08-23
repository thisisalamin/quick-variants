<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; }
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
		add_action( 'admin_init', array( $this, 'maybe_handle_reset' ) );
	}

	/** Reset handler */
	public function maybe_handle_reset() {
		if ( ! is_admin() || ! current_user_can( 'manage_options' ) ) {
			return; }
		$qv_reset = filter_input( INPUT_GET, 'qv_reset', FILTER_SANITIZE_NUMBER_INT );
		$nonce    = filter_input( INPUT_GET, '_qvnonce', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
		if ( empty( $qv_reset ) || empty( $nonce ) ) {
			return; }
		if ( ! wp_verify_nonce( $nonce, 'qv_reset_defaults' ) ) {
			return; }
		delete_option( self::OPTION_KEY );
		wp_safe_redirect( admin_url( 'admin.php?page=quick-variants-settings&reset=1' ) );
		exit;
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

	public static function get_defaults() {
		return array(
			'default_per_page'       => 10,
			'enable_alphabet_filter' => 1,
			'show_slide_cart'        => 1,
			'button_color'           => '#006DB5',
			'table_max_width'        => '',
		);
	}

	public static function get_settings() {
		$saved    = get_option( self::OPTION_KEY, array() );
		$defaults = self::get_defaults();
		return wp_parse_args( $saved, $defaults );
	}

	public function register_settings() {
		register_setting( 'quick_variants_settings_group', self::OPTION_KEY, array( $this, 'sanitize_settings' ) );
	}

	public function sanitize_settings( $input ) {
		$out                           = array();
		$out['default_per_page']       = max( 1, min( 100, intval( $input['default_per_page'] ?? self::get_defaults()['default_per_page'] ) ) );
		$out['enable_alphabet_filter'] = empty( $input['enable_alphabet_filter'] ) ? 0 : 1;
		$out['show_slide_cart']        = empty( $input['show_slide_cart'] ) ? 0 : 1;
		$out['button_color']           = sanitize_text_field( $input['button_color'] ?? '' );
		$out['table_max_width']        = sanitize_text_field( $input['table_max_width'] ?? '' );
		return $out;
	}

public function render_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	$settings         = self::get_settings();
	$default_per_page = (int) $settings['default_per_page'];
	$product_cats     = get_terms(
		array(
			'taxonomy'   => 'product_cat',
			'hide_empty' => false,
		)
	);

	$reset_url = wp_nonce_url(
		add_query_arg(
			array(
				'page'     => 'quick-variants-settings',
				'qv_reset' => 1,
			)
		),
		'qv_reset_defaults',
		'_qvnonce'
	);
	?>

	<div class="wrap qv-admin">
		<!-- Header -->
		<div class="flex items-center justify-between bg-white shadow p-6 rounded-lg mb-8">
			<div>
				<h1 class="text-2xl font-semibold text-gray-800 flex items-center gap-2">
					<?php esc_html_e( 'Quick Variants', 'quick-variants' ); ?>
					<span class="text-sm text-gray-500 bg-gray-100 px-2 py-0.5 rounded">
						v<?php echo esc_html( QUICK_VARIANTS_VERSION ); ?>
					</span>
				</h1>
				<p class="text-gray-500 text-sm">
					<?php esc_html_e( 'Configuration for product table & cart behaviors.', 'quick-variants' ); ?>
				</p>
			</div>
			<div>
				<a href="<?php echo esc_url( $reset_url ); ?>"
				   onclick="return confirm('<?php echo esc_js( __( 'Reset all settings to defaults? This cannot be undone.', 'quick-variants' ) ); ?>');"
				   class="inline-flex items-center gap-2 px-3 py-2 text-sm font-medium text-red-600 bg-red-50 border border-red-200 rounded-lg hover:bg-red-100">
					&#8635; <?php esc_html_e( 'Reset', 'quick-variants' ); ?>
				</a>
			</div>
		</div>

		<form method="post" action="options.php" class="grid grid-cols-1 lg:grid-cols-3 gap-6" id="qv-settings-form">
			<?php settings_fields( 'quick_variants_settings_group' ); ?>

			<!-- Main settings -->
			<div class="lg:col-span-2 space-y-6">
				<!-- General -->
				<div class="bg-white shadow rounded-lg p-6">
					<h2 class="text-lg font-medium text-gray-800 mb-4"><?php esc_html_e( 'General', 'quick-variants' ); ?></h2>
					<div class="space-y-4">
						<div>
							<label for="default_per_page" class="block font-medium text-gray-700">
								<?php esc_html_e( 'Default Products Per Page', 'quick-variants' ); ?>
							</label>
							<?php $this->field_number([
								'key'   => 'default_per_page',
								'attrs' => ['min' => 1, 'max' => 100],
								'help'  => __( 'Rows initially shown (higher may slow first load).', 'quick-variants' ),
							]); ?>
						</div>
					</div>
				</div>

				<!-- Display -->
				<div class="bg-white shadow rounded-lg p-6">
					<h2 class="text-lg font-medium text-gray-800 mb-4"><?php esc_html_e( 'Display Options', 'quick-variants' ); ?></h2>
					<div class="space-y-4">
						<div>
							<label class="block font-medium text-gray-700">
								<?php esc_html_e( 'Alphabet Filter', 'quick-variants' ); ?>
							</label>
							<?php $this->field_checkbox([
								'key'  => 'enable_alphabet_filter',
								'help' => __( 'Show A–Z filter bar.', 'quick-variants' ),
							]); ?>
						</div>
						<div>
							<label class="block font-medium text-gray-700">
								<?php esc_html_e( 'Slide Cart Drawer', 'quick-variants' ); ?>
							</label>
							<?php $this->field_checkbox([
								'key'  => 'show_slide_cart',
								'help' => __( 'Open slide cart after add to cart.', 'quick-variants' ),
							]); ?>
						</div>
						<div>
							<label class="block font-medium text-gray-700">
								<?php esc_html_e( 'Table Max Width', 'quick-variants' ); ?>
							</label>
							<?php $this->field_text([
								'key'         => 'table_max_width',
								'placeholder' => '1200px',
								'help'        => __( '1200px, 90%, 70rem or blank for full width.', 'quick-variants' ),
							]); ?>
						</div>
					</div>
				</div>

				<!-- Branding -->
				<div class="bg-white shadow rounded-lg p-6">
					<h2 class="text-lg font-medium text-gray-800 mb-4"><?php esc_html_e( 'Branding', 'quick-variants' ); ?></h2>
					<div class="space-y-4">
						<div>
							<label class="block font-medium text-gray-700">
								<?php esc_html_e( 'Primary Button Color', 'quick-variants' ); ?>
							</label>
							<div class="flex items-center gap-3">
								<?php $this->field_text([
									'key'         => 'button_color',
									'placeholder' => '#006DB5',
									'help'        => __( 'Used for primary buttons & progress bar.', 'quick-variants' ),
								]); ?>
								<span class="inline-block w-10 h-10 rounded border" style="background:<?php echo esc_attr( $settings['button_color'] ); ?>"></span>
							</div>
						</div>
						<div>
							<button type="button" id="qv-preview-button"
								class="px-4 py-2 rounded text-white font-medium"
								style="background:<?php echo esc_attr( $settings['button_color'] ); ?>;border-color:<?php echo esc_attr( $settings['button_color'] ); ?>">
								<?php esc_html_e( 'Sample Button', 'quick-variants' ); ?>
							</button>
						</div>
					</div>
				</div>
			</div>

			<!-- Sidebar -->
			<div class="space-y-6">
				<!-- Shortcode Generator -->
				<div class="bg-white shadow rounded-lg p-6">
					<h2 class="text-lg font-medium text-gray-800 mb-4"><?php esc_html_e( 'Shortcode Generator', 'quick-variants' ); ?></h2>
					<p class="text-sm text-gray-500 mb-4"><?php esc_html_e( 'Compose a shortcode quickly.', 'quick-variants' ); ?></p>

					<div class="space-y-4">
						<div>
							<label for="qv-gen-per-page" class="block font-medium text-gray-700">
								<?php esc_html_e( 'Products Per Page (override)', 'quick-variants' ); ?>
							</label>
							<input type="number" min="1" max="100" id="qv-gen-per-page" class="w-24 border rounded px-2 py-1" placeholder="<?php echo esc_attr( $default_per_page ); ?>" />
							<p class="text-xs text-gray-500"><?php printf( esc_html__( 'Blank = default (%d).', 'quick-variants' ), $default_per_page ); ?></p>
						</div>

						<div>
							<label for="qv-cat-search" class="block font-medium text-gray-700">
								<?php esc_html_e( 'Limit to Categories', 'quick-variants' ); ?>
							</label>
							<input type="text" id="qv-cat-search" placeholder="<?php esc_attr_e( 'Search categories…', 'quick-variants' ); ?>" class="w-full border rounded px-2 py-1 mb-2" />

							<?php if ( ! empty( $product_cats ) && ! is_wp_error( $product_cats ) ) : ?>
								<div class="flex gap-2 mb-2">
									<button type="button" class="px-2 py-1 text-xs bg-gray-100 border rounded hover:bg-gray-200" id="qv-cat-select-all">&check; <?php esc_html_e( 'All', 'quick-variants' ); ?></button>
									<button type="button" class="px-2 py-1 text-xs bg-gray-100 border rounded hover:bg-gray-200" id="qv-cat-clear">&times; <?php esc_html_e( 'Clear', 'quick-variants' ); ?></button>
								</div>
								<div class="max-h-40 overflow-auto border rounded p-2 bg-gray-50 space-y-1">
									<?php foreach ( $product_cats as $cat ) : ?>
										<label class="flex items-center gap-2 text-sm">
											<input type="checkbox" class="qv-gen-cat" value="<?php echo esc_attr( $cat->slug ); ?>" />
											<span><?php echo esc_html( $cat->name ); ?></span>
										</label>
									<?php endforeach; ?>
								</div>
								<p class="text-xs text-gray-500 mt-1"><?php esc_html_e( 'Select none for all products.', 'quick-variants' ); ?></p>
							<?php else : ?>
								<p class="text-xs text-gray-500"><?php esc_html_e( 'No product categories found.', 'quick-variants' ); ?></p>
							<?php endif; ?>
						</div>

						<div>
							<label class="block font-medium text-gray-700"><?php esc_html_e( 'Generated Shortcode', 'quick-variants' ); ?></label>
							<div class="flex items-center gap-2">
								<input type="text" readonly id="qv-generated-shortcode" class="flex-1 border rounded px-2 py-1 font-mono text-sm bg-gray-50" value="[quick_variants]" />
								<button type="button" class="px-3 py-1 bg-gray-800 text-white text-sm rounded" id="qv-copy-shortcode" data-copied-text="<?php esc_attr_e( 'Copied!', 'quick-variants' ); ?>">
									<?php esc_html_e( 'Copy', 'quick-variants' ); ?>
								</button>
							</div>
						</div>
					</div>
				</div>

				<!-- Resources -->
				<div class="bg-white shadow rounded-lg p-6">
					<h2 class="text-lg font-medium text-gray-800 mb-4"><?php esc_html_e( 'Resources', 'quick-variants' ); ?></h2>
					<ul class="space-y-2">
						<li><a href="https://example.com/docs" target="_blank" class="text-blue-600 hover:underline"><?php esc_html_e( 'Documentation', 'quick-variants' ); ?></a></li>
						<li><a href="mailto:support@example.com" class="text-blue-600 hover:underline"><?php esc_html_e( 'Support', 'quick-variants' ); ?></a></li>
						<li><a href="https://example.com/changelog" target="_blank" class="text-blue-600 hover:underline"><?php esc_html_e( 'Changelog', 'quick-variants' ); ?></a></li>
					</ul>
					<p class="text-xs text-gray-500 mt-3"><?php esc_html_e( 'Need custom tweaks? Reach out via support.', 'quick-variants' ); ?></p>
				</div>
			</div>

			<!-- Sticky Save -->
			<div class="lg:col-span-3 flex justify-end mt-6">
				<?php submit_button( __( 'Save Settings', 'quick-variants' ), 'primary large', 'submit', false, [ 'class' => 'px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg shadow' ] ); ?>
			</div>
		</form>
	</div>

	<?php
}


	/* Field helpers */
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
