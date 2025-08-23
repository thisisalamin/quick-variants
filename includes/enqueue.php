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
}
add_action( 'wp_enqueue_scripts', 'quick_variants_enqueue_assets' );
