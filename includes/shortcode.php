<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; }

/**
 * Render product table shortcode.
 */
function quick_variants_shortcode( $atts ) {
	$atts = shortcode_atts(
		array(
			'category' => '',
			'per_page' => 10,
		),
		$atts
	);

	$args_for_count = array(
		'post_type'      => 'product',
		'posts_per_page' => -1,
		'orderby'        => 'title',
		'order'          => 'ASC',
	);
	if ( ! empty( $atts['category'] ) ) {
		$args_for_count['tax_query'] = array(
			array(
				'taxonomy' => 'product_cat',
				'field'    => 'slug',
				'terms'    => explode( ',', $atts['category'] ),
			),
		);
	}
	$count_query    = new WP_Query( $args_for_count );
	$total_products = $count_query->found_posts;
	wp_reset_postdata();

	$args_for_display = array(
		'post_type'      => 'product',
		'orderby'        => 'title',
		'order'          => 'ASC',
		'posts_per_page' => $atts['per_page'],
	);
	if ( ! empty( $atts['category'] ) ) {
		$args_for_display['tax_query'] = array(
			array(
				'taxonomy' => 'product_cat',
				'field'    => 'slug',
				'terms'    => explode( ',', $atts['category'] ),
			),
		);
	}

	// Enrich filter script after initial load with dynamic values.
	wp_localize_script(
		'quick-variants-filter',
		'wcFilter',
		array(
			'ajaxUrl'        => admin_url( 'admin-ajax.php' ),
			'nonce'          => wp_create_nonce( 'wc_filter_nonce' ),
			'category'       => $atts['category'],
			'total_products' => $total_products,
			'per_page'       => $atts['per_page'],
		)
	);

	ob_start();
	include QUICK_VARIANTS_PATH . 'templates/table-wrapper.php';
	return ob_get_clean();
}
add_shortcode( 'quick_variants', 'quick_variants_shortcode' );
