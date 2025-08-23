<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; }

/**
 * AJAX handlers for cart operations.
 */
function quick_variants_ajax_add_to_cart() {
	check_ajax_referer( 'wc_cart_nonce', 'nonce' );
	$product_id = isset( $_POST['product_id'] ) ? intval( $_POST['product_id'] ) : 0;
	$quantity   = isset( $_POST['quantity'] ) ? intval( $_POST['quantity'] ) : 1;
	if ( $product_id > 0 ) {
		// Clear any lingering notices to capture only current attempt.
		wc_clear_notices();
		$added = WC()->cart->add_to_cart( $product_id, $quantity );
		if ( $added ) {
			wp_send_json_success( quick_variants_get_cart_payload() );
		}
		// Collect WooCommerce notices (errors / validation messages).
		$notices      = wc_get_notices();
		$error_msgs   = array();
		$notice_types = array( 'error', 'notice', 'success' ); // prioritize error but gather all for context.
		foreach ( $notice_types as $type ) {
			if ( ! empty( $notices[ $type ] ) ) {
				foreach ( $notices[ $type ] as $notice ) {
					if ( isset( $notice['notice'] ) ) {
						$error_msgs[] = wp_strip_all_tags( $notice['notice'] );
					}
				}
			}
		}
		wc_clear_notices();
		if ( empty( $error_msgs ) ) {
			$error_msgs[] = __( 'Failed to add product to cart.', 'quick-variants' );
		}
		// Create simplified messages (e.g., "Out of stock") when possible.
		$simplified = array();
		foreach ( $error_msgs as $msg ) {
			if ( stripos( $msg, 'out of stock' ) !== false ) {
				$simplified[] = __( 'Out of stock', 'quick-variants' );
			} else {
				$simplified[] = $msg;
			}
		}
		wp_send_json_error(
			array(
				'message'      => implode( ' ', array_unique( $simplified ) ),
				'full_message' => implode( ' ', $error_msgs ),
				'product_id'   => $product_id,
			)
		);
	}
	wp_send_json_error( array( 'message' => __( 'Invalid product.', 'quick-variants' ) ) );
}
add_action( 'wp_ajax_add_to_cart', 'quick_variants_ajax_add_to_cart' );
add_action( 'wp_ajax_nopriv_add_to_cart', 'quick_variants_ajax_add_to_cart' );

function quick_variants_ajax_get_cart() {
	check_ajax_referer( 'wc_cart_nonce', 'nonce' );
	wp_send_json_success( quick_variants_get_cart_payload() );
}
add_action( 'wp_ajax_get_cart', 'quick_variants_ajax_get_cart' );
add_action( 'wp_ajax_nopriv_get_cart', 'quick_variants_ajax_get_cart' );

function quick_variants_ajax_update_cart() {
	check_ajax_referer( 'wc_cart_nonce', 'nonce' );
	$cart_key = isset( $_POST['cart_key'] ) ? sanitize_text_field( $_POST['cart_key'] ) : '';
	$quantity = isset( $_POST['quantity'] ) ? intval( $_POST['quantity'] ) : 0;
	if ( $cart_key && $quantity > 0 ) {
		WC()->cart->set_quantity( $cart_key, $quantity );
		wp_send_json_success( quick_variants_get_cart_totals_only() );
	}
	wp_send_json_error();
}
add_action( 'wp_ajax_update_cart', 'quick_variants_ajax_update_cart' );
add_action( 'wp_ajax_nopriv_update_cart', 'quick_variants_ajax_update_cart' );

function quick_variants_ajax_remove_from_cart() {
	check_ajax_referer( 'wc_cart_nonce', 'nonce' );
	$cart_key = isset( $_POST['cart_key'] ) ? sanitize_text_field( $_POST['cart_key'] ) : '';
	if ( $cart_key ) {
		WC()->cart->remove_cart_item( $cart_key );
		wp_send_json_success( quick_variants_get_cart_totals_only() );
	}
	wp_send_json_error();
}
add_action( 'wp_ajax_remove_from_cart', 'quick_variants_ajax_remove_from_cart' );
add_action( 'wp_ajax_nopriv_remove_from_cart', 'quick_variants_ajax_remove_from_cart' );

/** Build full cart payload */
function quick_variants_get_cart_payload() {
	$payload          = quick_variants_get_cart_totals_only();
	$payload['items'] = array();
	foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
		$product   = $cart_item['data'];
		$parent_id = $product->is_type( 'variation' ) ? $product->get_parent_id() : $product->get_id();
		$image_id  = $product->get_image_id();
		if ( ! $image_id && $product->is_type( 'variation' ) ) {
			$image_id = get_post_thumbnail_id( $parent_id );
		}
		$image_url          = wp_get_attachment_image_src( $image_id, 'thumbnail' );
		$image_url          = $image_url ? $image_url[0] : wc_placeholder_img_src();
		$payload['items'][] = array(
			'key'       => $cart_item_key,
			'name'      => $product->get_name(),
			'quantity'  => $cart_item['quantity'],
			'price'     => quick_variants_format_price( WC()->cart->get_product_price( $product ) ),
			'image'     => $image_url,
			'variation' => isset( $cart_item['variation'] ) ? implode( ', ', $cart_item['variation'] ) : '',
		);
	}
	return $payload;
}

/** Cart totals only */
function quick_variants_get_cart_totals_only() {
	return array(
		'count'    => WC()->cart->get_cart_contents_count(),
		'subtotal' => quick_variants_format_price( WC()->cart->get_cart_subtotal() ),
		'total'    => quick_variants_format_price( WC()->cart->get_cart_total() ),
	);
}
