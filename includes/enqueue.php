<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; }

/**
 * Enqueue scripts and styles.
 */
function quick_variants_enqueue_assets() {
	wp_enqueue_style( 'quick-variants-styles', QUICK_VARIANTS_URL . 'assets/css/dist/style.css', array(), QUICK_VARIANTS_VERSION );
	wp_enqueue_style( 'quick-variants-custom', QUICK_VARIANTS_URL . 'assets/css/table.css', array(), QUICK_VARIANTS_VERSION );

	wp_enqueue_script( 'quick-variants-table', QUICK_VARIANTS_URL . 'assets/js/table.js', array( 'jquery' ), QUICK_VARIANTS_VERSION, true );
	wp_localize_script(
		'quick-variants-table',
		'wcPagination',
		array(
			'ajaxUrl' => admin_url( 'admin-ajax.php' ),
			'nonce'   => wp_create_nonce( 'wc_pagination_nonce' ),
		)
	);

	wp_enqueue_script( 'quick-variants-cart', QUICK_VARIANTS_URL . 'assets/js/cart.js', array( 'jquery' ), QUICK_VARIANTS_VERSION, true );
	wp_localize_script(
		'quick-variants-cart',
		'wcCart',
		array(
			'ajaxUrl' => admin_url( 'admin-ajax.php' ),
			'nonce'   => wp_create_nonce( 'wc_cart_nonce' ),
		)
	);

	wp_enqueue_script( 'quick-variants-filter', QUICK_VARIANTS_URL . 'assets/js/filter.js', array( 'jquery' ), QUICK_VARIANTS_VERSION, true );
	wp_localize_script(
		'quick-variants-filter',
		'wcFilter',
		array(
			'ajaxUrl' => admin_url( 'admin-ajax.php' ),
			'nonce'   => wp_create_nonce( 'wc_filter_nonce' ),
		)
	);

	// Dynamic button color from settings.
	$color = quick_variants_get_setting( 'button_color' );
	if ( $color ) {
		$dynamic_css = ':root{--qv-btn-color:' . esc_attr( $color ) . ';}'
			. ' .add-to-cart,.toggle-variants,.show-more-button,.alphabet-filter.active,.qv-cart-btn.primary{background-color:var(--qv-btn-color)!important;border-color:var(--qv-btn-color)!important;}'
			. ' .add-to-cart:hover,.toggle-variants:hover,.show-more-button:hover,.qv-cart-btn.primary:hover{color:var(--qv-btn-color)!important;}'
			. ' .pagination-total-item{background-color:var(--qv-btn-color)!important;}';
		wp_add_inline_style( 'quick-variants-custom', $dynamic_css );
	}
}
add_action( 'wp_enqueue_scripts', 'quick_variants_enqueue_assets' );

/** Admin assets (color picker) */
function quick_variants_admin_assets( $hook ) {
	if ( $hook !== 'toplevel_page_quick-variants-settings' ) {
		return; }
	wp_enqueue_style( 'wp-color-picker' );
	wp_enqueue_style( 'quick-variants-admin', QUICK_VARIANTS_URL . 'assets/css/dist/admin.css', array(), QUICK_VARIANTS_VERSION );
	wp_enqueue_script( 'quick-variants-admin', QUICK_VARIANTS_URL . 'assets/js/admin.js', array( 'wp-color-picker' ), QUICK_VARIANTS_VERSION, true );
}
add_action( 'admin_enqueue_scripts', 'quick_variants_admin_assets' );
