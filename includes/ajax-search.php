<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; }

/** Filter posts_where for first-letter filtering */
function quick_variants_filter_title_first_letter( $where ) {
	global $wpdb;
	$title_filter = get_query_var( 'title_filter' );
	if ( $title_filter ) {
		$where .= $wpdb->prepare(
			" AND (UPPER(SUBSTR($wpdb->posts.post_title,1,1)) = %s OR LOWER(SUBSTR($wpdb->posts.post_title,1,1)) = %s)",
			strtoupper( $title_filter ),
			strtolower( $title_filter )
		);
	}
	return $where;
}

/** Unified AJAX search/filter/pagination endpoint */
function quick_variants_ajax_search_products() {
	check_ajax_referer( 'quicva_filter_nonce', 'nonce' );
	$search_term      = isset( $_POST['search'] ) ? sanitize_text_field( wp_unslash( $_POST['search'] ) ) : '';
	$letter           = isset( $_POST['letter'] ) ? sanitize_text_field( wp_unslash( $_POST['letter'] ) ) : '';
	$category         = isset( $_POST['category'] ) ? sanitize_text_field( wp_unslash( $_POST['category'] ) ) : '';
	$per_page         = isset( $_POST['per_page'] ) ? intval( $_POST['per_page'] ) : 10;
	$page             = isset( $_POST['page'] ) ? intval( $_POST['page'] ) : 1;
	$visible_variants = isset( $_POST['visible_variants'] ) ? array_map( 'intval', (array) $_POST['visible_variants'] ) : array();

	$offset = ( $page - 1 ) * $per_page;

	$args = array(
		'post_type'      => 'product',
		'orderby'        => 'title',
		'order'          => 'ASC',
		'posts_per_page' => $per_page,
		'offset'         => $offset,
		'no_found_rows'  => true, // Skip found_rows calculation for main query
	);
	if ( ! empty( $category ) ) {
		// phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query -- Necessary for category filtering, limited by posts_per_page and offset
		$args['tax_query'] = array(
			array(
				'taxonomy' => 'product_cat',
				'field'    => 'slug',
				'terms'    => explode( ',', $category ),
				'operator' => 'IN',
			),
		);
	}
	if ( ! empty( $letter ) && 'all' !== $letter ) {
		set_query_var( 'title_filter', $letter );
		add_filter( 'posts_where', 'quick_variants_filter_title_first_letter' );
	}
	if ( ! empty( $search_term ) ) {
		$args['s'] = $search_term;
	}

	$count_args                           = $args;
	$count_args['posts_per_page']         = -1;
	$count_args['offset']                 = 0;
	$count_args['fields']                 = 'ids';
	$count_args['no_found_rows']          = false; // We need found_rows for count
	$count_args['update_post_meta_cache'] = false; // Skip post meta cache for count query
	$count_args['update_post_term_cache'] = false; // Skip term cache for count query
	$count_query                          = new WP_Query( $count_args );
	$total_products                       = $count_query->found_posts;
	wp_reset_postdata();

	$loop = new WP_Query( $args );
	ob_start();
	while ( $loop->have_posts() ) :
		$loop->the_post();
		global $product;
		set_query_var( 'visible_variants', $visible_variants );
		include QUICVA_PATH . 'templates/product-row.php';
	endwhile;
	wp_reset_postdata();

	if ( ! empty( $letter ) && 'all' !== $letter ) {
		remove_filter( 'posts_where', 'quick_variants_filter_title_first_letter' );
	}

	$showing_start = $offset + 1;
	$showing_end   = min( $offset + $per_page, $total_products );
	$has_more      = ( $showing_end < $total_products );

	wp_send_json_success(
		array(
			'html'            => ob_get_clean(),
			'count'           => $total_products,
			'show_pagination' => true,
			'total_products'  => $total_products,
			'current_page'    => $page,
			'showing_start'   => $showing_start,
			'showing_end'     => $showing_end,
			'has_more'        => $has_more,
			'category'        => $category,
		)
	);
}
add_action( 'wp_ajax_quicva_search_products', 'quick_variants_ajax_search_products' );
add_action( 'wp_ajax_nopriv_quicva_search_products', 'quick_variants_ajax_search_products' );
