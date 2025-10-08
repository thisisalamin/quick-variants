<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; }

/**
 * Enqueue scripts and styles.
 */
function quick_variants_enqueue_assets() {
	wp_enqueue_style( 'quick-variants-styles', QUICVA_URL . 'assets/css/dist/style.css', array(), QUICVA_VERSION );
	wp_enqueue_style( 'quick-variants-custom', QUICVA_URL . 'assets/css/table.css', array(), QUICVA_VERSION );

	wp_enqueue_script( 'quick-variants-table', QUICVA_URL . 'assets/js/table.js', array( 'jquery' ), QUICVA_VERSION, true );
	wp_localize_script(
		'quick-variants-table',
		'quicvaPagination',
		array(
			'ajaxUrl' => admin_url( 'admin-ajax.php' ),
			'nonce'   => wp_create_nonce( 'quicva_pagination_nonce' ),
		)
	);

	wp_enqueue_script( 'quick-variants-cart', QUICVA_URL . 'assets/js/cart.js', array( 'jquery' ), QUICVA_VERSION, true );
	wp_localize_script(
		'quick-variants-cart',
		'quicvaCart',
		array(
			'ajaxUrl' => admin_url( 'admin-ajax.php' ),
			'nonce'   => wp_create_nonce( 'quicva_cart_nonce' ),
		)
	);

	wp_enqueue_script( 'quick-variants-filter', QUICVA_URL . 'assets/js/filter.js', array( 'jquery' ), QUICVA_VERSION, true );
	wp_localize_script(
		'quick-variants-filter',
		'quicvaFilter',
		array(
			'ajaxUrl' => admin_url( 'admin-ajax.php' ),
			'nonce'   => wp_create_nonce( 'quicva_filter_nonce' ),
		)
	);

	// Quick view
	if ( function_exists( 'quick_variants_get_setting' ) && quick_variants_get_setting( 'enable_quick_view' ) ) {
		wp_enqueue_script( 'quick-variants-quick-view', QUICVA_URL . 'assets/js/quick-view.js', array( 'jquery' ), QUICVA_VERSION, true );
		wp_localize_script(
			'quick-variants-quick-view',
			'quicvaQuickView',
			array(
				'ajaxUrl' => admin_url( 'admin-ajax.php' ),
				'nonce'   => wp_create_nonce( 'quicva_filter_nonce' ),
			)
		);
	}

	// Dynamic button color from settings.
	$color = quick_variants_get_setting( 'button_color' );
	if ( $color ) {
		$dynamic_css = ':root{--qv-btn-color:' . esc_attr( $color ) . ';}'
			. ' .add-to-cart,.toggle-variants,.show-more-button,.alphabet-filter.active,.qv-cart-btn.primary{background-color:var(--qv-btn-color)!important;border-color:var(--qv-btn-color)!important;}'
			. ' .add-to-cart:hover,.toggle-variants:hover,.show-more-button:hover{color:var(--qv-btn-color)!important;background:#fff!important;}'
			. ' .qv-cart-btn.primary:hover{background:var(--qv-btn-color)!important;color:#fff!important;filter:brightness(1.05);}'
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
	wp_enqueue_style( 'quick-variants-admin', QUICVA_URL . 'assets/css/dist/admin.css', array(), QUICVA_VERSION );
	wp_enqueue_script( 'quick-variants-admin', QUICVA_URL . 'assets/js/admin.js', array( 'wp-color-picker' ), QUICVA_VERSION, true );
}
add_action( 'admin_enqueue_scripts', 'quick_variants_admin_assets' );
