<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; }

/**
 * AJAX handler to return product quick view HTML
 */
function quick_variants_ajax_quick_view() {
	check_ajax_referer( 'quicva_filter_nonce', 'nonce' );
	$product_id = isset( $_POST['product_id'] ) ? intval( $_POST['product_id'] ) : 0;
	if ( ! $product_id ) {
		wp_send_json_error( array( 'message' => __( 'Invalid product ID', 'quick-variants' ) ) );
	}
	$product = wc_get_product( $product_id );
	if ( ! $product ) {
		wp_send_json_error( array( 'message' => __( 'Product not found', 'quick-variants' ) ) );
	}

	// Prepare data for template
	ob_start();
	set_query_var( 'quick_variant_product', $product );
	include QUICVA_PATH . 'templates/quick-view.php';
	$html = ob_get_clean();

	wp_send_json_success( array( 'html' => $html ) );
}
add_action( 'wp_ajax_quicva_quick_view_product', 'quick_variants_ajax_quick_view' );
add_action( 'wp_ajax_nopriv_quicva_quick_view_product', 'quick_variants_ajax_quick_view' );
