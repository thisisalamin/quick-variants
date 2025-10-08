<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; }

/**
 * Render product table shortcode.
 */
function quick_variants_shortcode( $atts ) {
	$default_per_page = (int) quick_variants_get_setting( 'default_per_page' );
	$atts             = shortcode_atts(
		array(
			'category' => '',
			'per_page' => $default_per_page,
		),
		$atts
	);

	$args_for_count = array(
		'post_type'              => 'product',
		'posts_per_page'         => -1,
		'orderby'                => 'title',
		'order'                  => 'ASC',
		'no_found_rows'          => false, // We need found_rows for pagination
		'update_post_meta_cache' => false, // Skip post meta cache for count query
		'update_post_term_cache' => false, // Skip term cache for count query
	);
	if ( ! empty( $atts['category'] ) ) {
		// phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query -- Necessary for category filtering, optimized with cache flags
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
		'no_found_rows'  => true, // Skip found_rows calculation for display query
	);
	if ( ! empty( $atts['category'] ) ) {
		// phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query -- Necessary for category filtering, limited by posts_per_page
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
		'quicvaFilter',
		array(
			'ajaxUrl'        => admin_url( 'admin-ajax.php' ),
			'nonce'          => wp_create_nonce( 'quicva_filter_nonce' ),
			'category'       => $atts['category'],
			'total_products' => $total_products,
			'per_page'       => $atts['per_page'],
		)
	);

	ob_start();
	$enable_alpha    = quick_variants_get_setting( 'enable_alphabet_filter' );
	$button_color    = quick_variants_get_setting( 'button_color' );
	$table_max_width = quick_variants_get_setting( 'table_max_width' );
	include QUICVA_PATH . 'templates/table-wrapper.php';
	return ob_get_clean();
}
add_shortcode( 'quick_variants', 'quick_variants_shortcode' );
