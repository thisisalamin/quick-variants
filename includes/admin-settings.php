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
			// Labels
			'label_images'           => __( 'Images', 'quick-variants' ),
			'label_product'          => __( 'Product', 'quick-variants' ),
			'label_price'            => __( 'Price', 'quick-variants' ),
			'label_qty'              => __( 'Qty', 'quick-variants' ),
			'label_options'          => __( 'Options', 'quick-variants' ),
			'label_add_to_cart'      => __( 'Add to cart', 'quick-variants' ),
			'enable_quick_view'      => 1,
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
		$out['enable_quick_view']      = empty( $input['enable_quick_view'] ) ? 0 : 1;
		$out['show_slide_cart']        = empty( $input['show_slide_cart'] ) ? 0 : 1;
		// Labels
		$defaults                 = self::get_defaults();
		$out['label_images']      = sanitize_text_field( $input['label_images'] ?? $defaults['label_images'] );
		$out['label_product']     = sanitize_text_field( $input['label_product'] ?? $defaults['label_product'] );
		$out['label_price']       = sanitize_text_field( $input['label_price'] ?? $defaults['label_price'] );
		$out['label_qty']         = sanitize_text_field( $input['label_qty'] ?? $defaults['label_qty'] );
		$out['label_options']     = sanitize_text_field( $input['label_options'] ?? $defaults['label_options'] );
		$out['label_add_to_cart'] = sanitize_text_field( $input['label_add_to_cart'] ?? $defaults['label_add_to_cart'] );
		$out['button_color']      = sanitize_text_field( $input['button_color'] ?? '' );
		$out['table_max_width']   = sanitize_text_field( $input['table_max_width'] ?? '' );
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
		<!-- Enhanced Header -->
		<div class="bg-gradient-to-r from-indigo-600 via-purple-600 to-indigo-800 shadow-xl rounded-2xl p-8 mb-8 text-white">
			<div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
				<div class="flex items-center gap-4">
					<div class="bg-white/20 backdrop-blur-sm p-3 rounded-xl">
						<svg class="w-8 h-8" fill="currentColor" viewBox="0 0 20 20">
							<path fill-rule="evenodd" d="M3 4a1 1 0 011-1h12a1 1 0 011 1v2a1 1 0 01-1 1H4a1 1 0 01-1-1V4zM3 10a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H4a1 1 0 01-1-1v-6zM14 9a1 1 0 00-1 1v6a1 1 0 001 1h2a1 1 0 001-1v-6a1 1 0 00-1-1h-2z" clip-rule="evenodd"/>
						</svg>
					</div>
					<div>
						<h1 class="text-3xl font-bold tracking-tight"><?php esc_html_e( 'Quick Variants', 'quick-variants' ); ?></h1>
						<p class="text-indigo-100 text-sm mt-1"><?php esc_html_e( 'WooCommerce Product Table Settings', 'quick-variants' ); ?></p>
					</div>
				</div>
				<div class="flex items-center gap-3">
					<span class="bg-white/20 backdrop-blur-sm text-xs font-semibold px-3 py-1.5 rounded-full">
						v<?php echo esc_html( QUICK_VARIANTS_VERSION ); ?>
					</span>
					<a href="<?php echo esc_url( $reset_url ); ?>"
						onclick="return confirm('<?php echo esc_js( __( 'Reset all settings to defaults? This cannot be undone.', 'quick-variants' ) ); ?>');"
						class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-red-300 bg-red-500/20 border border-red-400/30 rounded-lg hover:bg-red-500/30 hover:text-red-200 transition-all duration-200 backdrop-blur-sm">
						<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
							<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
						</svg>
						<?php esc_html_e( 'Reset', 'quick-variants' ); ?>
					</a>
				</div>
			</div>
		</div>

		<form method="post" action="options.php" class="space-y-8" id="qv-settings-form">
			<?php settings_fields( 'quick_variants_settings_group' ); ?>

			<!-- Tab Navigation -->
			<div class="bg-white border border-gray-200 overflow-hidden">
				<div class="bg-gray-50">
					<nav class="flex px-6 py-2">
						<button type="button" class="qv-tab-btn active relative flex items-center gap-2 px-6 py-3 text-sm font-medium text-indigo-600 transition-all duration-200" data-tab="general">
							<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
								<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
								<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
							</svg>
							<span><?php esc_html_e( 'General', 'quick-variants' ); ?></span>
						</button>
						<button type="button" class="qv-tab-btn relative flex items-center gap-2 px-6 py-3 text-sm font-medium text-gray-500 hover:text-gray-700 transition-all duration-200" data-tab="labels">
							<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
								<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
							</svg>
							<span><?php esc_html_e( 'Labels', 'quick-variants' ); ?></span>
						</button>
					</nav>
				</div>

				<!-- Tab Content -->
				<div class="p-8">
					<!-- General Tab -->
					<div id="general-tab" class="qv-tab-content">
						<div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
							<div class="lg:col-span-2 space-y-8">
								<!-- Display Options -->
								<div class="bg-gradient-to-br from-blue-50 to-indigo-50 border border-blue-200 rounded-xl p-6">
									<div class="flex items-center gap-3 mb-6">
										<div class="bg-blue-100 p-2 rounded-lg">
											<svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
												<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
											</svg>
										</div>
										<h2 class="text-xl font-bold text-gray-800"><?php esc_html_e( 'Display Options', 'quick-variants' ); ?></h2>
									</div>
									<div class="grid grid-cols-1 md:grid-cols-2 gap-6">
										<div class="space-y-4">
											<div class="flex items-center justify-between p-4 bg-white rounded-lg border border-gray-200">
												<div>
													<label class="font-semibold text-gray-800"><?php esc_html_e( 'Alphabet Filter', 'quick-variants' ); ?></label>
													<p class="text-sm text-gray-500"><?php esc_html_e( 'Show A–Z filter bar', 'quick-variants' ); ?></p>
												</div>
												<?php $this->field_checkbox_enhanced( array( 'key' => 'enable_alphabet_filter' ) ); ?>
											</div>
											<div class="flex items-center justify-between p-4 bg-white rounded-lg border border-gray-200">
												<div>
													<label class="font-semibold text-gray-800"><?php esc_html_e( 'Slide Cart Drawer', 'quick-variants' ); ?></label>
													<p class="text-sm text-gray-500"><?php esc_html_e( 'Open slide cart after add to cart', 'quick-variants' ); ?></p>
												</div>
												<?php $this->field_checkbox_enhanced( array( 'key' => 'show_slide_cart' ) ); ?>
											</div>
											<div class="flex items-center justify-between p-4 bg-white rounded-lg border border-gray-200">
												<div>
													<label class="font-semibold text-gray-800"><?php esc_html_e( 'Quick View', 'quick-variants' ); ?></label>
													<p class="text-sm text-gray-500"><?php esc_html_e( 'Enable Quick View eye icon', 'quick-variants' ); ?></p>
												</div>
												<?php $this->field_checkbox_enhanced( array( 'key' => 'enable_quick_view' ) ); ?>
											</div>
										</div>
										<div class="space-y-4">
											<div class="p-4 bg-white rounded-lg border border-gray-200">
												<label class="block font-semibold text-gray-800 mb-2"><?php esc_html_e( 'Default Products Per Page', 'quick-variants' ); ?></label>
												<?php
												$this->field_number_enhanced(
													array(
														'key'   => 'default_per_page',
														'attrs' => array(
															'min' => 1,
															'max' => 100,
														),
														'help'  => __( 'Rows initially shown (higher may slow first load)', 'quick-variants' ),
													)
												);
												?>
											</div>
											<div class="p-4 bg-white rounded-lg border border-gray-200">
												<label class="block font-semibold text-gray-800 mb-2"><?php esc_html_e( 'Table Max Width', 'quick-variants' ); ?></label>
												<?php
												$this->field_text_enhanced(
													array(
														'key'         => 'table_max_width',
														'placeholder' => '1200px',
														'help'        => __( '1200px, 90%, 70rem or blank for full width', 'quick-variants' ),
													)
												);
												?>
											</div>
										</div>
									</div>
								</div>

								<!-- Branding & Colors -->
								<div class="bg-gradient-to-br from-indigo-50 to-blue-50 border border-indigo-200 rounded-xl p-6">
									<div class="flex items-center gap-3 mb-6">
										<div class="bg-indigo-100 p-2 rounded-lg">
											<svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
												<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zM21 5a2 2 0 00-2-2h-4a2 2 0 00-2 2v12a4 4 0 004 4h4a2 2 0 002-2V5z"/>
											</svg>
										</div>
										<h2 class="text-xl font-bold text-gray-800"><?php esc_html_e( 'Branding & Colors', 'quick-variants' ); ?></h2>
									</div>
									<div class="space-y-6">
										<div>
											<label class="block font-semibold text-gray-800 mb-3"><?php esc_html_e( 'Primary Button Color', 'quick-variants' ); ?></label>
											<div class="flex items-center gap-4">
												<?php
												$this->field_text_enhanced(
													array(
														'key'         => 'button_color',
														'placeholder' => '#006DB5',
														'help'        => __( 'Used for primary buttons & progress bar', 'quick-variants' ),
													)
												);
												?>
												<div id="qv-color-preview" class="w-12 h-12 rounded-lg border-4 border-white shadow-lg" style="background:<?php echo esc_attr( $settings['button_color'] ); ?>;"></div>
											</div>
										</div>
										<div>
											<label class="block font-semibold text-gray-800 mb-3"><?php esc_html_e( 'Preview', 'quick-variants' ); ?></label>
											<div class="flex gap-3">
												<button type="button" id="qv-preview-button"
													class="px-6 py-3 rounded-lg text-white font-semibold shadow-lg hover:shadow-xl transition-all duration-200 transform hover:-translate-y-0.5"
													style="background:<?php echo esc_attr( $settings['button_color'] ); ?>;border-color:<?php echo esc_attr( $settings['button_color'] ); ?>">
													<?php esc_html_e( 'Sample Button', 'quick-variants' ); ?>
												</button>
												<button type="button" class="px-6 py-3 rounded-lg border-2 font-semibold text-gray-700 bg-white border-gray-300 hover:bg-gray-50 transition-colors">
													<?php esc_html_e( 'Secondary Button', 'quick-variants' ); ?>
												</button>
											</div>
										</div>
									</div>
								</div>
							</div>

							<!-- Sidebar -->
							<div class="space-y-6">
								<!-- Shortcode Generator -->
								<div class="bg-gradient-to-br from-indigo-50 to-blue-50 border border-indigo-200 rounded-xl p-6">
									<div class="flex items-center gap-3 mb-6">
										<div class="bg-indigo-100 p-2 rounded-lg">
											<svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
												<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"/>
											</svg>
										</div>
										<h3 class="text-lg font-bold text-gray-800"><?php esc_html_e( 'Shortcode Generator', 'quick-variants' ); ?></h3>
									</div>
									<p class="text-sm text-gray-600 mb-4"><?php esc_html_e( 'Compose a shortcode quickly.', 'quick-variants' ); ?></p>

									<div class="space-y-4">
										<div>
											<label for="qv-gen-per-page" class="block font-medium text-gray-700 text-sm mb-2">
												<?php esc_html_e( 'Products Per Page', 'quick-variants' ); ?>
											</label>
											<input type="number" min="1" max="100" id="qv-gen-per-page" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm" placeholder="<?php echo esc_attr( $default_per_page ); ?>" />
											<p class="text-xs text-gray-500 mt-1"><?php printf( esc_html__( 'Blank = default (%d)', 'quick-variants' ), $default_per_page ); ?></p>
										</div>

										<div>
											<label for="qv-cat-search" class="block font-medium text-gray-700 text-sm mb-2">
												<?php esc_html_e( 'Limit to Categories', 'quick-variants' ); ?>
											</label>
											<input type="text" id="qv-cat-search" placeholder="<?php esc_attr_e( 'Search categories…', 'quick-variants' ); ?>" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm mb-3" />

											<?php if ( ! empty( $product_cats ) && ! is_wp_error( $product_cats ) ) : ?>
												<div class="flex gap-2 mb-3">
													<button type="button" class="px-3 py-1.5 text-xs bg-indigo-100 border border-indigo-200 rounded-lg hover:bg-indigo-200 transition-colors" id="qv-cat-select-all">
														<?php esc_html_e( 'All', 'quick-variants' ); ?>
													</button>
													<button type="button" class="px-3 py-1.5 text-xs bg-gray-100 border border-gray-200 rounded-lg hover:bg-gray-200 transition-colors" id="qv-cat-clear">
														<?php esc_html_e( 'Clear', 'quick-variants' ); ?>
													</button>
												</div>
												<div class="max-h-40 overflow-auto border border-gray-200 rounded-lg p-3 bg-gray-50 space-y-2 qv-cat-grid">
													<?php foreach ( $product_cats as $cat ) : ?>
														<label class="flex items-center gap-2 text-sm hover:bg-white p-1 rounded cursor-pointer" data-name="<?php echo esc_attr( strtolower( $cat->name ) ); ?>">
															<input type="checkbox" class="qv-gen-cat w-4 h-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500" value="<?php echo esc_attr( $cat->slug ); ?>" />
															<span><?php echo esc_html( $cat->name ); ?></span>
														</label>
													<?php endforeach; ?>
												</div>
												<p class="text-xs text-gray-500 mt-2"><?php esc_html_e( 'Select none for all products.', 'quick-variants' ); ?></p>
											<?php else : ?>
												<p class="text-xs text-gray-500"><?php esc_html_e( 'No product categories found.', 'quick-variants' ); ?></p>
											<?php endif; ?>
										</div>

										<div>
											<label class="block font-medium text-gray-700 text-sm mb-2"><?php esc_html_e( 'Generated Shortcode', 'quick-variants' ); ?></label>
											<div class="flex items-center gap-2">
												<input type="text" readonly id="qv-generated-shortcode" class="flex-1 px-3 py-2 border border-gray-300 rounded-lg bg-gray-50 font-mono text-sm" value="[quick_variants]" />
												<button type="button" class="px-4 py-2 bg-indigo-600 text-white text-sm rounded-lg hover:bg-indigo-700 transition-colors" id="qv-copy-shortcode" data-copied-text="<?php esc_attr_e( 'Copied!', 'quick-variants' ); ?>">
													<?php esc_html_e( 'Copy', 'quick-variants' ); ?>
												</button>
											</div>
										</div>
									</div>
								</div>

								<!-- Resources -->
								<div class="bg-gradient-to-br from-blue-50 to-indigo-50 border border-blue-200 rounded-xl p-6">
									<div class="flex items-center gap-3 mb-4">
										<div class="bg-blue-100 p-2 rounded-lg">
											<svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
												<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
											</svg>
										</div>
										<h3 class="text-lg font-bold text-gray-800"><?php esc_html_e( 'Resources', 'quick-variants' ); ?></h3>
									</div>
									<ul class="space-y-3">
										<li>
											<a href="https://github.com/thisisalamin/quick-variants" target="_blank" class="flex items-center gap-2 text-indigo-600 hover:text-indigo-700 font-medium transition-colors">
												<svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
													<path d="M12 0c-6.626 0-12 5.373-12 12 0 5.302 3.438 9.8 8.207 11.387.599.111.793-.261.793-.577v-2.234c-3.338.726-4.033-1.416-4.033-1.416-.546-1.387-1.333-1.756-1.333-1.756-1.089-.745.083-.729.083-.729 1.205.084 1.839 1.237 1.839 1.237 1.07 1.834 2.807 1.304 3.492.997.107-.775.418-1.305.762-1.604-2.665-.305-5.467-1.334-5.467-5.931 0-1.311.469-2.381 1.236-3.221-.124-.303-.535-1.524.117-3.176 0 0 1.008-.322 3.301 1.23.957-.266 1.983-.399 3.003-.404 1.02.005 2.047.138 3.006.404 2.291-1.552 3.297-1.23 3.297-1.23.653 1.653.242 2.874.118 3.176.77.84 1.235 1.911 1.235 3.221 0 4.609-2.807 5.624-5.479 5.921.43.372.823 1.102.823 2.222v3.293c0 .319.192.694.801.576 4.765-1.589 8.199-6.086 8.199-11.386 0-6.627-5.373-12-12-12z"/>
												</svg>
												<?php esc_html_e( 'Documentation', 'quick-variants' ); ?>
											</a>
										</li>
										<li>
											<a href="mailto:support@crafely.com" class="flex items-center gap-2 text-indigo-600 hover:text-indigo-700 font-medium transition-colors">
												<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
													<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 4.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
												</svg>
												<?php esc_html_e( 'Support', 'quick-variants' ); ?>
											</a>
										</li>
									</ul>
									<p class="text-xs text-gray-500 mt-4"><?php esc_html_e( 'Need custom tweaks? Reach out via support.', 'quick-variants' ); ?></p>
								</div>
							</div>
						</div>
					</div>

					<!-- Labels Tab -->
					<div id="labels-tab" class="qv-tab-content hidden">
						<div class="w-full">
							<div class="bg-gradient-to-br from-indigo-50 to-blue-50 border border-indigo-200 rounded-xl p-6">
								<div class="flex items-center gap-3 mb-6">
									<div class="bg-indigo-100 p-2 rounded-lg">
										<svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
											<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
										</svg>
									</div>
									<h2 class="text-xl font-bold text-gray-800"><?php esc_html_e( 'Table Labels', 'quick-variants' ); ?></h2>
								</div>
								<p class="text-gray-600 mb-6"><?php esc_html_e( 'Customize the column headers and button labels in your product table.', 'quick-variants' ); ?></p>
								<div class="grid grid-cols-1 md:grid-cols-2 gap-6">
									<?php
									$labels = array(
										'label_images'  => __( 'Images column header', 'quick-variants' ),
										'label_product' => __( 'Product name column header', 'quick-variants' ),
										'label_price'   => __( 'Price column header', 'quick-variants' ),
										'label_qty'     => __( 'Quantity column header', 'quick-variants' ),
										'label_options' => __( 'Options column header', 'quick-variants' ),
										'label_add_to_cart' => __( 'Add to cart button label', 'quick-variants' ),
									);
									foreach ( $labels as $key => $description ) {
										?>
										<div class="p-4 bg-white rounded-lg border border-gray-200">
											<label class="block font-semibold text-gray-800 mb-2"><?php echo esc_html( ucfirst( str_replace( 'label_', '', $key ) ) ); ?></label>
											<?php
											$this->field_text_enhanced(
												array(
													'key'  => $key,
													'help' => $description,
												)
											);
											?>
										</div>
										<?php
									}
									?>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>

			<!-- Enhanced Save Button -->
			<div class="flex justify-center">
				<button type="submit" class="inline-flex items-center gap-3 px-8 py-4 bg-gradient-to-r from-indigo-600 to-purple-600 text-white font-bold text-lg rounded-xl shadow-lg hover:shadow-xl transition-all duration-200 transform hover:-translate-y-1">
					<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
						<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
					</svg>
					<?php esc_html_e( 'Save All Settings', 'quick-variants' ); ?>
				</button>
			</div>
		</form>
	</div>

		<?php
	}


	public function field_checkbox_enhanced( $args ) {
		$settings = self::get_settings();
		$key      = $args['key'];
		$checked  = ! empty( $settings[ $key ] ) ? 'checked' : '';
		echo '<label class="qv-toggle">
			<input type="checkbox" id="' . esc_attr( $key ) . '" name="' . esc_attr( self::OPTION_KEY ) . '[' . esc_attr( $key ) . ']" value="1" ' . $checked . '>
			<span class="toggle-slider"></span>
		</label>';
	}

	public function field_number_enhanced( $args ) {
		$settings = self::get_settings();
		$key      = $args['key'];
		$value    = isset( $settings[ $key ] ) ? intval( $settings[ $key ] ) : '';
		$attrs    = '';
		if ( ! empty( $args['attrs'] ) ) {
			foreach ( $args['attrs'] as $attr => $val ) {
				$attrs .= sprintf( ' %s="%s"', esc_attr( $attr ), esc_attr( $val ) );
			}
		}
		echo '<input type="number" id="' . esc_attr( $key ) . '" name="' . esc_attr( self::OPTION_KEY ) . '[' . esc_attr( $key ) . ']" value="' . esc_attr( $value ) . '" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm" ' . $attrs . ' />';
		if ( ! empty( $args['help'] ) ) {
			echo '<p class="text-xs text-gray-500 mt-1">' . esc_html( $args['help'] ) . '</p>';
		}
	}

	public function field_text_enhanced( $args ) {
		$settings    = self::get_settings();
		$key         = $args['key'];
		$value       = isset( $settings[ $key ] ) ? esc_attr( $settings[ $key ] ) : '';
		$placeholder = isset( $args['placeholder'] ) ? esc_attr( $args['placeholder'] ) : '';
		$extra_class = ( 'button_color' === $key ) ? ' quick-variants-color-field' : '';
		echo '<input type="text" id="' . esc_attr( $key ) . '" name="' . esc_attr( self::OPTION_KEY ) . '[' . esc_attr( $key ) . ']" value="' . $value . '" placeholder="' . $placeholder . '" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm' . esc_attr( $extra_class ) . '" />';
		if ( ! empty( $args['help'] ) ) {
			echo '<p class="text-xs text-gray-500 mt-1">' . esc_html( $args['help'] ) . '</p>';
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
