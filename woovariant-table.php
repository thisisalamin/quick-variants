<?php
/**
 * Plugin Name: WooCommerce Variant  Product Table
 * Description: Display WooCommerce products in a table format with expandable variants.
 * Version: 1.0
 * Author: Mohamed Alamin
 * Author URI: https://www.crafely.com
 * License: GPL2
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: wc-product-table
 * Domain Path: /languages
 * Requires at least: 5.0
 * Tested up to: 6.4
 */

if (!defined('ABSPATH')) exit;

// Enqueue scripts and styles
function wc_product_table_enqueue_scripts() {
    // Enqueue compiled Tailwind CSS
    wp_enqueue_style('wc-product-table-styles', plugins_url('assets/css/dist/style.css', __FILE__));
    wp_enqueue_style('wc-product-table-custom', plugins_url('assets/css/table.css', __FILE__));
    wp_enqueue_script('wc-product-table', plugins_url('assets/js/table.js', __FILE__), array('jquery'), '1.0', true);
    wp_enqueue_script('wc-product-cart', plugins_url('assets/js/cart.js', __FILE__), array('jquery'), '1.0', true);
    wp_localize_script('wc-product-cart', 'wcCart', array(
        'ajaxUrl' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('wc_cart_nonce')
     ));
    // Add new JavaScript for filtering
    wp_enqueue_script('wc-product-filter', plugins_url('assets/js/filter.js', __FILE__), array('jquery'), '1.0', true);
}
add_action('wp_enqueue_scripts', 'wc_product_table_enqueue_scripts');

// Shortcode to display product table
function wc_product_table_shortcode($atts) {
    $atts = shortcode_atts(array(
        'category' => '',
        'per_page' => 10, // Default products per page
    ), $atts);

    // Add pagination script
    wp_enqueue_script('wc-product-pagination', plugins_url('assets/js/pagination.js', __FILE__), array('jquery'), '1.0', true);
    wp_localize_script('wc-product-pagination', 'wcPagination', array(
        'ajaxUrl' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('wc_pagination_nonce')
    ));

    ob_start();
    ?>
    <!-- Search and Filter Section -->
    <div class="mb-6 search-filter-container">
        <div class="search-filter-wrapper">
            <input type="text" 
                   id="product-search" 
                   placeholder="Search products..." 
                   class="search-input">
            <div class="dropdown-filter">
                <button id="filter-button" class="filter-btn">
                    <span id="current-filter">All</span>
                    <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </button>
                <div id="filter-dropdown" class="filter-dropdown">
                    <button class="alphabet-filter active" data-letter="all">All</button>
                    <?php
                    foreach (range('A', 'Z') as $letter) {
                        echo '<button class="alphabet-filter" data-letter="' . $letter . '">' . $letter . '</button>';
                    }
                    ?>
                </div>
            </div>
        </div>
    </div>

    <div class="overflow-x-auto">
        <table id="product-table" class="min-w-full border-collapse !border !border-gray-200">
            <!-- Table Header -->
            <thead>
                <tr class="!border-b !border-gray-200 bg-[#FAFAFA]">
                    <th class="p-4 text-center font-semibold !border !border-gray-200 w-24">IMAGES</th>
                    <th class="p-4 text-left font-semibold !border !border-gray-200 w-[400px]">PRODUCT</th>
                    <th class="p-4 text-center font-semibold !border !border-gray-200">PRICE</th>
                    <th class="p-4 text-center font-semibold !border !border-gray-200">QTY</th>
                    <th class="p-4 text-center font-semibold !border !border-gray-200">OPTIONS</th>
                </tr>
            </thead>
            <!-- Table Body -->
            <tbody>
                <?php
                $args = array(
                    'post_type' => 'product',
                    'posts_per_page' => $atts['per_page'],
                    'orderby' => 'title',
                    'order' => 'ASC'
                );

                // Add category filter if specified
                if (!empty($atts['category'])) {
                    $args['tax_query'] = array(
                        array(
                            'taxonomy' => 'product_cat',
                            'field' => 'slug',
                            'terms' => explode(',', $atts['category'])
                        )
                    );
                }

                $loop = new WP_Query($args);
                $total_products = $loop->found_posts;

                while ($loop->have_posts()) : $loop->the_post();
                    global $product;
                    ?>
                    <!-- Product Row -->
                    <tr class="!border-b !border-gray-200">
                        <td class="p-4 !border !border-gray-200 w-24 text-center">
                            <?php 
                            $image = wp_get_attachment_image_src(get_post_thumbnail_id(), 'full');
                            $img_url = $image ? $image[0] : wc_placeholder_img_src();
                            ?>
                            <img src="<?php echo esc_url($img_url); ?>" 
                                 alt="<?php echo esc_attr(get_the_title()); ?>" 
                                 class="w-20 h-20 object-contain mx-auto"/>
                        </td>
                        <td class="p-4 !border text-black !border-gray-200 w-[400px]"><?php echo strtoupper(get_the_title()); ?></td>
                        <td class="p-4 !border !border-gray-200 text-center">
                            <div class="flex flex-row items-center justify-center gap-1">
                                <?php if ($product->is_type('variable')): 
                                    $min_price = $product->get_variation_price('min');
                                    echo 'From ' . '<span class="text-black font-medium">' . wc_price($min_price) . '</span>';
                                else:
                                    echo '<span class="text-black font-medium">' . $product->get_price_html() . '</span>';
                                endif; ?>
                            </div>
                        </td>
                        <td class="p-4 !border !border-gray-200 text-center">
                            <?php if (!$product->is_type('variable')): ?>
                                <div class="flex justify-center">
                                    <input type="number" min="1" value="1" class="w-20 p-2 border rounded text-center">
                                </div>
                            <?php endif; ?>
                        </td>
                        <td class="p-4 !border !border-gray-200 text-center">
                            <?php if ($product->is_type('variable')): ?>
                                <button class="toggle-variants bg-[#232323] text-white px-4 py-2.5 text-sm hover:bg-white hover:text-black hover:border-black hover:border transition-all duration-300 w-full mx-auto font-bold" data-id="<?php echo $product->get_id(); ?>">
                                    SHOW VARIANTS
                                </button>
                            <?php else: ?>
                                <button class="add-to-cart bg-[#232323] text-white px-4 py-2.5 text-sm hover:bg-white hover:text-black hover:border-black hover:border transition-all duration-300 w-full mx-auto font-bold" data-id="<?php echo $product->get_id(); ?>">
                                    ADD TO CART
                                </button>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php
                    if ($product->is_type('variable')) {
                        $variations = $product->get_available_variations();
                        foreach ($variations as $variation) {
                            // Skip if variation is out of stock or has no price/zero price
                            if (!$variation['is_in_stock'] || 
                                !isset($variation['display_price']) || 
                                $variation['display_price'] <= 0) {
                                continue;
                            }
                            ?>
                            <tr class="variant-row variant-<?php echo $product->get_id(); ?> !border-b !border-gray-200 bg-gray-50">
                                <td class="p-4 !border !border-gray-200">
                                    <img src="<?php echo esc_url($variation['image']['url']); ?>" 
                                         alt="<?php echo esc_attr($variation['variation_description']); ?>"
                                         class="w-20 h-20 object-contain" />
                                </td>
                                <td class="p-4 !border !border-gray-200"><?php echo strtoupper(implode(', ', $variation['attributes'])); ?></td>
                                <td class="p-4 !border !border-gray-200">
                                    <div class="flex flex-col items-center">
                                        <span class="font-medium text-black"><?php echo wc_price($variation['display_price']); ?></span>
                                    </div>
                                </td>
                                <td class="p-4 !border !border-gray-200">
                                    <div class="flex justify-center">
                                        <input type="number" min="1" value="1" class="w-20 p-2 border rounded text-center">
                                    </div>
                                </td>
                                <td class="p-4 !border !border-gray-200">
                                    <button class="add-to-cart bg-[#232323] text-white px-4 py-2.5 text-sm hover:bg-white hover:text-black hover:border-black hover:border transition-all duration-300 w-full font-bold" 
                                            data-id="<?php echo $variation['variation_id']; ?>">
                                        ADD TO CART
                                    </button>
                                </td>
                            </tr>
                            <?php
                        }
                    }
                endwhile;
                wp_reset_postdata();
                ?>
            </tbody>
        </table>
    </div>
    <div class="pagination-wrapper text-center">
        <nav class="pagination style--1 text-center" role="navigation" aria-label="Pagination">
            <div class="pagination-page-item pagination-page-total">
                <div class="flex items-center justify-center gap-1 text-[16px] text-gray-600 mb-2">
                    <span>Showing</span>
                    <span data-total-start="1" class="font-medium">1</span>
                    <span>-</span>
                    <span data-total-end="<?php echo min($atts['per_page'], $total_products); ?>" class="font-medium"><?php echo min($atts['per_page'], $total_products); ?></span>
                    <span>of</span>
                    <span class="font-medium"><?php echo $total_products; ?></span>
                    <span>total</span>
                </div>
                <div class="pagination-total-progress">
                    <span style="width: <?php echo ($atts['per_page'] / $total_products) * 100; ?>%" class="pagination-total-item"></span>
                </div>
            </div>
            <?php if ($total_products > $atts['per_page']): ?>
            <div class="pagination-button">
                <a href="#" class="show-more-button" data-page="1" data-per-page="<?php echo $atts['per_page']; ?>" data-total="<?php echo $total_products; ?>">SHOW MORE</a>
            </div>
            <?php endif; ?>
        </nav>
    </div>
    <?php
    return ob_get_clean();
}
add_shortcode('wc_product_table', 'wc_product_table_shortcode');

// Add AJAX handler for loading more products
function wc_ajax_load_more_products() {
    check_ajax_referer('wc_pagination_nonce', 'nonce');
    
    $page = isset($_POST['page']) ? intval($_POST['page']) : 1;
    $per_page = isset($_POST['per_page']) ? intval($_POST['per_page']) : 10;
    $category = isset($_POST['category']) ? sanitize_text_field($_POST['category']) : '';

    $args = array(
        'post_type' => 'product',
        'posts_per_page' => $per_page,
        'paged' => $page,
        'orderby' => 'title',
        'order' => 'ASC'
    );

    if (!empty($category)) {
        $args['tax_query'] = array(
            array(
                'taxonomy' => 'product_cat',
                'field' => 'slug',
                'terms' => explode(',', $category)
            )
        );
    }

    $loop = new WP_Query($args);
    $response = array('html' => '', 'has_more' => false);

    ob_start();
    while ($loop->have_posts()) : $loop->the_post();
        global $product;
        // Include your product row HTML here
        // This should be the same HTML structure as in your main shortcode
        include(plugin_dir_path(__FILE__) . 'templates/product-row.php');
    endwhile;
    wp_reset_postdata();
    
    $response['html'] = ob_get_clean();
    $response['has_more'] = $loop->max_num_pages > $page;
    
    wp_send_json_success($response);
}
add_action('wp_ajax_load_more_products', 'wc_ajax_load_more_products');
add_action('wp_ajax_nopriv_load_more_products', 'wc_ajax_load_more_products');

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
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
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

                <a href="<?php echo wc_get_checkout_url(); ?>" class="block w-full text-sm bg-[#232323] text-white py-2.5 mb-2 hover:bg-black/80 transition-all duration-300 text-center font-bold">
                    CHECKOUT
                </a>
                <a href="<?php echo wc_get_cart_url(); ?>" class="block w-full text-sm border border-gray-300 text-gray-700 py-2.5 hover:bg-gray-50 transition-all duration-300 text-center font-bold">
                    VIEW CART
                </a>
            </div>
        </div>
    </div>
    <div id="cart-overlay"></div>
    <?php
}
add_action('wp_footer', 'wc_cart_template');

// Add to cart AJAX handler
function format_price($price_html) {
    // Convert HTML entities to their actual characters and strip tags
    return html_entity_decode(strip_tags($price_html));
}

function wc_ajax_add_to_cart() {
    check_ajax_referer('wc_cart_nonce', 'nonce');
    
    $product_id = isset($_POST['product_id']) ? intval($_POST['product_id']) : 0;
    $quantity = isset($_POST['quantity']) ? intval($_POST['quantity']) : 1;
    
    if ($product_id > 0) {
        $added = WC()->cart->add_to_cart($product_id, $quantity);
        if ($added) {
            $cart_data = array(
                'items' => array(),
                'count' => WC()->cart->get_cart_contents_count(),
                'subtotal' => format_price(WC()->cart->get_cart_subtotal()),
                'total' => format_price(WC()->cart->get_cart_total())
            );

            foreach (WC()->cart->get_cart() as $cart_item_key => $cart_item) {
                $product = $cart_item['data'];
                $cart_data['items'][] = array(
                    'key' => $cart_item_key,
                    'name' => $product->get_name(),
                    'quantity' => $cart_item['quantity'],
                    'price' => format_price(WC()->cart->get_product_price($product)),
                    'image' => wp_get_attachment_image_src(get_post_thumbnail_id($product->get_id()), 'thumbnail')[0],
                    'variation' => isset($cart_item['variation']) ? implode(', ', $cart_item['variation']) : ''
                );
            }

            wp_send_json_success($cart_data);
        }
    }
    
    wp_send_json_error(array(
        'message' => 'Failed to add product'
    ));
}
add_action('wp_ajax_add_to_cart', 'wc_ajax_add_to_cart');
add_action('wp_ajax_nopriv_add_to_cart', 'wc_ajax_add_to_cart');

// Get cart contents AJAX handler
function wc_ajax_get_cart() {
    check_ajax_referer('wc_cart_nonce', 'nonce');
    
    $cart_data = array(
        'items' => array(),
        'count' => WC()->cart->get_cart_contents_count(),
        'subtotal' => format_price(WC()->cart->get_cart_subtotal()),
        'total' => format_price(WC()->cart->get_cart_total())
    );
    
    foreach (WC()->cart->get_cart() as $cart_item_key => $cart_item) {
        $product = $cart_item['data'];
        $cart_data['items'][] = array(
            'key' => $cart_item_key,
            'name' => $product->get_name(),
            'quantity' => $cart_item['quantity'],
            'price' => format_price(WC()->cart->get_product_price($product)),
            'image' => wp_get_attachment_image_src(get_post_thumbnail_id($product->get_id()), 'thumbnail')[0],
            'variation' => isset($cart_item['variation']) ? implode(', ', $cart_item['variation']) : ''
        );
    }
    
    wp_send_json_success($cart_data);
}
add_action('wp_ajax_get_cart', 'wc_ajax_get_cart');
add_action('wp_ajax_nopriv_get_cart', 'wc_ajax_get_cart');

// Update cart quantity AJAX handler
function wc_ajax_update_cart() {
    check_ajax_referer('wc_cart_nonce', 'nonce');
    
    $cart_key = isset($_POST['cart_key']) ? sanitize_text_field($_POST['cart_key']) : '';
    $quantity = isset($_POST['quantity']) ? intval($_POST['quantity']) : 0;
    
    if ($cart_key && $quantity > 0) {
        WC()->cart->set_quantity($cart_key, $quantity);
        
        wp_send_json_success(array(
            'count' => WC()->cart->get_cart_contents_count(),
            'subtotal' => format_price(WC()->cart->get_cart_subtotal()),
            'total' => format_price(WC()->cart->get_cart_total())
        ));
    }
    
    wp_send_json_error();
}
add_action('wp_ajax_update_cart', 'wc_ajax_update_cart');
add_action('wp_ajax_nopriv_update_cart', 'wc_ajax_update_cart');

// Remove from cart AJAX handler
function wc_ajax_remove_from_cart() {
    check_ajax_referer('wc_cart_nonce', 'nonce');
    
    $cart_key = isset($_POST['cart_key']) ? sanitize_text_field($_POST['cart_key']) : '';
    
    if ($cart_key) {
        WC()->cart->remove_cart_item($cart_key);
        
        wp_send_json_success(array(
            'count' => WC()->cart->get_cart_contents_count(),
            'subtotal' => format_price(WC()->cart->get_cart_subtotal()),
            'total' => format_price(WC()->cart->get_cart_total())
        ));
    }
    
    wp_send_json_error();
}
add_action('wp_ajax_remove_from_cart', 'wc_ajax_remove_from_cart');
add_action('wp_ajax_nopriv_remove_from_cart', 'wc_ajax_remove_from_cart');
