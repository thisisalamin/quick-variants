<?php
/**
 * Plugin Name: Quick Variants - WooCommerce Variant Product Table
 * Description: Display WooCommerce products in a table format with expandable variants.
 * Version: 1.0
 * Author: Mohamed Alamin
 * Author URI: https://www.crafely.com
 * License: GPL2
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: quick-variants
 * Domain Path: /languages
 * Requires at least: 5.0
 * Tested up to: 6.4
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Enqueue Scripts and Styles
 */
function quick_variants_enqueue_scripts() {
	// Tailwind + custom CSS
	wp_enqueue_style( 'quick-variants-styles', plugins_url( 'assets/css/dist/style.css', __FILE__ ) );
	wp_enqueue_style( 'quick-variants-custom', plugins_url( 'assets/css/table.css', __FILE__ ) );

	wp_enqueue_script( 'quick-variants-table', plugins_url( 'assets/js/table.js', __FILE__ ), array( 'jquery' ), '1.0', true );
	wp_localize_script(
		'quick-variants-table',
		'wcPagination',
		array(
			'ajaxUrl' => admin_url( 'admin-ajax.php' ),
			'nonce'   => wp_create_nonce( 'wc_pagination_nonce' ),
		)
	);

	// Cart script
	wp_enqueue_script( 'quick-variants-cart', plugins_url( 'assets/js/cart.js', __FILE__ ), array( 'jquery' ), '1.0', true );
	wp_localize_script(
		'quick-variants-cart',
		'wcCart',
		array(
			'ajaxUrl' => admin_url( 'admin-ajax.php' ),
			'nonce'   => wp_create_nonce( 'wc_cart_nonce' ),
		)
	);

	// Filter + Search + “Show More” logic
	wp_enqueue_script( 'quick-variants-filter', plugins_url( 'assets/js/filter.js', __FILE__ ), array( 'jquery' ), '1.0', true );
	wp_localize_script(
		'quick-variants-filter',
		'wcFilter',
		array(
			'ajaxUrl' => admin_url( 'admin-ajax.php' ),
			'nonce'   => wp_create_nonce( 'wc_filter_nonce' ),
			// Category + per_page will be set dynamically within the shortcode; rest localized later.
		)
	);
}
add_action( 'wp_enqueue_scripts', 'quick_variants_enqueue_scripts' );

/**
 * Shortcode to display the product table
 */
function quick_variants_shortcode( $atts ) {
	$atts = shortcode_atts(
		array(
			'category' => '',   // Comma-separated category slugs
			'per_page' => 10,
		),
		$atts
	);

	// Count total products in these categories
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

	// For initial display, fetch first page
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

	// Localize final data for the JS script
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
	?>
	<!-- Search + Filter Section -->
	<div class="search-filter-container">
		<div class="search-filter-wrapper">
			<div class="search-section">
				<div class="search-input-wrapper">
					<input type="text" id="product-search" placeholder="Search products..." class="search-input">
				</div>
				<div class="alphabet-filter-container">
					<button class="alphabet-filter active" data-letter="all">All</button>
					<?php foreach ( range( 'A', 'Z' ) as $letter ) : ?>
						<button class="alphabet-filter" data-letter="<?php echo $letter; ?>"><?php echo $letter; ?></button>
					<?php endforeach; ?>
				</div>
			</div>
		</div>
	</div>

	<div class="overflow-x-auto">
		<table id="product-table"
				class="min-w-full border-collapse !border !border-gray-200"
				data-total="<?php echo $total_products; ?>"
				data-per-page="<?php echo $atts['per_page']; ?>">

			<thead>
				<tr class="!border-b !border-gray-200 bg-[#FAFAFA]">
					<th style="width:120px !important" class="p-4 align-middle text-center font-semibold !border !border-gray-200">IMAGES</th>
					<th style="width:50% !important" class="p-4 align-middle text-left font-semibold !border !border-gray-200 w-[400px]">PRODUCT</th>
					<th style="width:30% !important" class="p-4 align-middle text-center font-semibold !border !border-gray-200">PRICE</th>
					<th style="width:100px !important" class="p-4 align-middle text-center font-semibold !border !border-gray-200">QTY</th>
					<th style="width:180px !important" class="p-4 align-middle text-center font-semibold !border !border-gray-200">OPTIONS</th>
				</tr>
			</thead>

			<tbody>
				<?php
				// Show the first batch of products
				$display_query = new WP_Query( $args_for_display );
				while ( $display_query->have_posts() ) :
					$display_query->the_post();
					global $product;
					include plugin_dir_path( __FILE__ ) . 'templates/product-row.php';
				endwhile;
				wp_reset_postdata();
				?>
			</tbody>
		</table>
	</div>

	<div class="pagination-wrapper text-center" <?php echo ( $total_products <= $atts['per_page'] ) ? 'style="display:none;"' : ''; ?>>
		<nav class="pagination style--1 text-center" role="navigation" aria-label="Pagination">
			<div class="pagination-page-item pagination-page-total">
				<div class="flex items-center justify-center gap-1 text-gray-600 mb-2">
					<span>Showing</span>
					<span data-total-start="1" class="font-medium">1</span>
					<span>-</span>
					<span data-total-end="<?php echo min( $atts['per_page'], $total_products ); ?>" class="font-medium">
						<?php echo min( $atts['per_page'], $total_products ); ?>
					</span>
					<span>of</span>
					<span class="font-medium"><?php echo $total_products; ?></span>
					<span>total</span>
				</div>
				<div class="pagination-total-progress">
					<?php
					$initial_progress = $total_products > 0 ? ( $atts['per_page'] / $total_products ) * 100 : 0;
					?>
					<span style="width: <?php echo $initial_progress; ?>%" class="pagination-total-item"></span>
				</div>
			</div>
			<div class="pagination-button">
				<a href="#" class="show-more-button"
					data-page="1"
					data-per-page="<?php echo $atts['per_page']; ?>"
					data-total="<?php echo $total_products; ?>">
					<div class="button-content">
						<span class="loader"></span>
						<span class="button-text">SHOW MORE</span>
					</div>
				</a>
			</div>
		</nav>
	</div>
	<?php

	return ob_get_clean();
}
add_shortcode( 'quick_variants', 'quick_variants_shortcode' );

/**
 * Renders the hidden slide cart template in the footer
 */
function wc_cart_template() {
	?>
	<div id="slide-cart">
		<div class="flex flex-col h-full">
			<div class="flex justify-between items-center p-4 border-b">
				<div>
					<h2 class="text-xl font-medium">Shopping Cart</h2>
					<p class="text-gray-500 text-sm"><span id="cart-count">0</span> items</p>
				</div>
				<button id="close-cart" class="text-gray-400 hover:text-gray-500">
					<svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none"
						viewBox="0 0 24 24" stroke="currentColor">
						<path stroke-linecap="round"
								stroke-linejoin="round"
								stroke-width="2"
								d="M6 18L18 6M6 6l12 12"/>
					</svg>
				</button>
			</div>

			<div class="flex-1 overflow-y-auto p-4">
				<div id="cart-items" class="space-y-4">
					<!-- Cart items will be dynamically inserted here -->
				</div>
			</div>

			<div class="border-t p-3">
				<div class="flex justify-between mb-1">
					<span class="font-medium">Subtotal:</span>
					<span id="cart-subtotal" class="font-medium">0.00</span>
				</div>
				<div class="flex justify-between mb-3">
					<span class="font-medium">Total:</span>
					<span id="cart-total" class="font-medium">0.00</span>
				</div>

				<a href="<?php echo wc_get_checkout_url(); ?>"
					class="block w-full text-sm bg-[#232323] text-white py-2.5 mb-2 hover:bg-black/80 transition-all duration-300 text-center font-bold">
					CHECKOUT
				</a>
				<a href="<?php echo wc_get_cart_url(); ?>"
					class="block w-full text-sm border border-gray-300 text-gray-700 py-2.5 hover:bg-gray-50 transition-all duration-300 text-center font-bold">
					VIEW CART
				</a>
			</div>
		</div>
	</div>
	<div id="cart-overlay"></div>
	<?php
}
add_action( 'wp_footer', 'wc_cart_template' );

/**
 * Format price helper
 */
function format_price( $price_html ) {
	// Convert HTML entities to their actual characters and strip tags
	return html_entity_decode( strip_tags( $price_html ) );
}

/**
 * AJAX: Add to Cart
 */
function wc_ajax_add_to_cart() {
	check_ajax_referer( 'wc_cart_nonce', 'nonce' );

	$product_id = isset( $_POST['product_id'] ) ? intval( $_POST['product_id'] ) : 0;
	$quantity   = isset( $_POST['quantity'] ) ? intval( $_POST['quantity'] ) : 1;

	if ( $product_id > 0 ) {
		$added = WC()->cart->add_to_cart( $product_id, $quantity );
		if ( $added ) {
			$cart_data = array(
				'items'    => array(),
				'count'    => WC()->cart->get_cart_contents_count(),
				'subtotal' => format_price( WC()->cart->get_cart_subtotal() ),
				'total'    => format_price( WC()->cart->get_cart_total() ),
			);

			foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
				$product   = $cart_item['data'];
				$parent_id = $product->is_type( 'variation' ) ? $product->get_parent_id() : $product->get_id();

				// Variation image or fallback to parent product image
				$image_id = $product->get_image_id();
				if ( ! $image_id && $product->is_type( 'variation' ) ) {
					$image_id = get_post_thumbnail_id( $parent_id );
				}

				$image_url = wp_get_attachment_image_src( $image_id, 'thumbnail' );
				$image_url = $image_url ? $image_url[0] : wc_placeholder_img_src();

				$cart_data['items'][] = array(
					'key'       => $cart_item_key,
					'name'      => $product->get_name(),
					'quantity'  => $cart_item['quantity'],
					'price'     => format_price( WC()->cart->get_product_price( $product ) ),
					'image'     => $image_url,
					'variation' => isset( $cart_item['variation'] ) ? implode( ', ', $cart_item['variation'] ) : '',
				);
			}

			wp_send_json_success( $cart_data );
		}
	}

	wp_send_json_error( array( 'message' => 'Failed to add product' ) );
}
add_action( 'wp_ajax_add_to_cart', 'wc_ajax_add_to_cart' );
add_action( 'wp_ajax_nopriv_add_to_cart', 'wc_ajax_add_to_cart' );

/**
 * AJAX: Get Cart Contents
 */
function wc_ajax_get_cart() {
	check_ajax_referer( 'wc_cart_nonce', 'nonce' );

	$cart_data = array(
		'items'    => array(),
		'count'    => WC()->cart->get_cart_contents_count(),
		'subtotal' => format_price( WC()->cart->get_cart_subtotal() ),
		'total'    => format_price( WC()->cart->get_cart_total() ),
	);

	foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
		$product   = $cart_item['data'];
		$parent_id = $product->is_type( 'variation' ) ? $product->get_parent_id() : $product->get_id();

		// Variation image or fallback to parent product image
		$image_id = $product->get_image_id();
		if ( ! $image_id && $product->is_type( 'variation' ) ) {
			$image_id = get_post_thumbnail_id( $parent_id );
		}

		$image_url = wp_get_attachment_image_src( $image_id, 'thumbnail' );
		$image_url = $image_url ? $image_url[0] : wc_placeholder_img_src();

		$cart_data['items'][] = array(
			'key'       => $cart_item_key,
			'name'      => $product->get_name(),
			'quantity'  => $cart_item['quantity'],
			'price'     => format_price( WC()->cart->get_product_price( $product ) ),
			'image'     => $image_url,
			'variation' => isset( $cart_item['variation'] ) ? implode( ', ', $cart_item['variation'] ) : '',
		);
	}

	wp_send_json_success( $cart_data );
}
add_action( 'wp_ajax_get_cart', 'wc_ajax_get_cart' );
add_action( 'wp_ajax_nopriv_get_cart', 'wc_ajax_get_cart' );

/**
 * AJAX: Update Cart Quantity
 */
function wc_ajax_update_cart() {
	check_ajax_referer( 'wc_cart_nonce', 'nonce' );

	$cart_key = isset( $_POST['cart_key'] ) ? sanitize_text_field( $_POST['cart_key'] ) : '';
	$quantity = isset( $_POST['quantity'] ) ? intval( $_POST['quantity'] ) : 0;

	if ( $cart_key && $quantity > 0 ) {
		WC()->cart->set_quantity( $cart_key, $quantity );

		wp_send_json_success(
			array(
				'count'    => WC()->cart->get_cart_contents_count(),
				'subtotal' => format_price( WC()->cart->get_cart_subtotal() ),
				'total'    => format_price( WC()->cart->get_cart_total() ),
			)
		);
	}

	wp_send_json_error();
}
add_action( 'wp_ajax_update_cart', 'wc_ajax_update_cart' );
add_action( 'wp_ajax_nopriv_update_cart', 'wc_ajax_update_cart' );

/**
 * AJAX: Remove from Cart
 */
function wc_ajax_remove_from_cart() {
	check_ajax_referer( 'wc_cart_nonce', 'nonce' );

	$cart_key = isset( $_POST['cart_key'] ) ? sanitize_text_field( $_POST['cart_key'] ) : '';

	if ( $cart_key ) {
		WC()->cart->remove_cart_item( $cart_key );

		wp_send_json_success(
			array(
				'count'    => WC()->cart->get_cart_contents_count(),
				'subtotal' => format_price( WC()->cart->get_cart_subtotal() ),
				'total'    => format_price( WC()->cart->get_cart_total() ),
			)
		);
	}

	wp_send_json_error();
}
add_action( 'wp_ajax_remove_from_cart', 'wc_ajax_remove_from_cart' );
add_action( 'wp_ajax_nopriv_remove_from_cart', 'wc_ajax_remove_from_cart' );

/*
------------------------------------------------------------------
	SINGLE AJAX ENDPOINT for all “Search / Filter / Show More”
	------------------------------------------------------------------ */

/**
 * Helper function to filter products by first letter
 */
function filter_products_by_title_first_letter( $where ) {
	global $wpdb;
	$title_filter = get_query_var( 'title_filter' );
	if ( $title_filter ) {
		// Compare both uppercase + lowercase
		$where .= $wpdb->prepare(
			" AND (UPPER(SUBSTR($wpdb->posts.post_title,1,1)) = %s OR LOWER(SUBSTR($wpdb->posts.post_title,1,1)) = %s)",
			strtoupper( $title_filter ),
			strtolower( $title_filter )
		);
	}
	return $where;
}

/**
 * AJAX: Search products (handles search, A-Z filter, pagination, category filter).
 */
/**
 * AJAX: Search products (handles search, A-Z filter, pagination, category filter).
 */
function wc_ajax_search_products() {
	check_ajax_referer( 'wc_filter_nonce', 'nonce' );

	$search_term      = isset( $_POST['search'] ) ? sanitize_text_field( $_POST['search'] ) : '';
	$letter           = isset( $_POST['letter'] ) ? sanitize_text_field( $_POST['letter'] ) : '';
	$category         = isset( $_POST['category'] ) ? sanitize_text_field( $_POST['category'] ) : '';
	$per_page         = isset( $_POST['per_page'] ) ? intval( $_POST['per_page'] ) : 10;
	$page             = isset( $_POST['page'] ) ? intval( $_POST['page'] ) : 1;
	$visible_variants = isset( $_POST['visible_variants'] ) ? array_map( 'intval', $_POST['visible_variants'] ) : array();

	// Calculate offset based on current page
	// E.g. page=1 => offset=0, page=2 => offset=5, etc.
	$offset = ( $page - 1 ) * $per_page;

	// Build main query
	$args = array(
		'post_type'      => 'product',
		'orderby'        => 'title',
		'order'          => 'ASC',
		'posts_per_page' => $per_page,
		'offset'         => $offset, // <-- use offset instead of paged
	);

	// If category is specified
	if ( ! empty( $category ) ) {
		$args['tax_query'] = array(
			array(
				'taxonomy' => 'product_cat',
				'field'    => 'slug',
				'terms'    => explode( ',', $category ),
				'operator' => 'IN',
			),
		);
	}

	// If user chose an A-Z filter
	if ( ! empty( $letter ) && $letter !== 'all' ) {
		set_query_var( 'title_filter', $letter );
		add_filter( 'posts_where', 'filter_products_by_title_first_letter' );
	}

	// If user typed in a search term
	if ( ! empty( $search_term ) ) {
		$args['s'] = $search_term;
	}

	// First get total count
	$count_args                   = $args;
	$count_args['posts_per_page'] = -1;
	$count_args['offset']         = 0; // for counting, do not offset
	$count_args['fields']         = 'ids'; // faster for counting
	$count_query                  = new WP_Query( $count_args );
	$total_products               = $count_query->found_posts;
	wp_reset_postdata();

	// Now get paginated results using the offset
	$loop = new WP_Query( $args );
	ob_start();
	while ( $loop->have_posts() ) :
		$loop->the_post();
		global $product;
		// Make sure we pass the visible_variants to the template
		set_query_var( 'visible_variants', $visible_variants );
		include plugin_dir_path( __FILE__ ) . 'templates/product-row.php';
	endwhile;
	wp_reset_postdata();

	// Remove the filter if used
	if ( ! empty( $letter ) && $letter !== 'all' ) {
		remove_filter( 'posts_where', 'filter_products_by_title_first_letter' );
	}

	// Figure out the “range” being shown now
	// Example: If page=2 & per_page=5 => offset=5 => showing products 6..10
	$showing_start = $offset + 1;
	$showing_end   = min( $offset + $per_page, $total_products );

	// If there's still more products beyond $showing_end
	$has_more = ( $showing_end < $total_products );

	$response = array(
		'html'            => ob_get_clean(),
		'count'           => $total_products,
		'show_pagination' => true,  // show/hide pagination
		'total_products'  => $total_products,
		'current_page'    => $page,
		'showing_start'   => $showing_start,
		'showing_end'     => $showing_end,
		'has_more'        => $has_more,
		'category'        => $category,
	);

	wp_send_json_success( $response );
}

add_action( 'wp_ajax_search_products', 'wc_ajax_search_products' );
add_action( 'wp_ajax_nopriv_search_products', 'wc_ajax_search_products' );
