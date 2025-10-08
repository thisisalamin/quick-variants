<?php
/**
 * Plugin Name: Quick Variants
 * Description: Display WooCommerce products in a table format with expandable variants.
 * Version: 1.0
 * Author: Crafely
 * Author URI: https://www.crafely.com
 * License: GPL2
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: quick-variants
 * Domain Path: /languages
 * Requires at least: 5.0
 * Tested up to: 6.8
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Core constants.
define( 'QUICVA_VERSION', '1.0' );
define( 'QUICVA_PATH', plugin_dir_path( __FILE__ ) );
define( 'QUICVA_URL', plugin_dir_url( __FILE__ ) );

// Autoload (simple) or manual includes.
require_once QUICVA_PATH . 'includes/enqueue.php';
require_once QUICVA_PATH . 'includes/shortcode.php';
require_once QUICVA_PATH . 'includes/template-cart.php';
require_once QUICVA_PATH . 'includes/ajax-cart.php';
require_once QUICVA_PATH . 'includes/ajax-search.php';
require_once QUICVA_PATH . 'includes/helpers.php';
require_once QUICVA_PATH . 'includes/admin-settings.php';
// Quick view AJAX handler
require_once QUICVA_PATH . 'includes/ajax-quick-view.php';

// Future: activation / deactivation hooks could go here.
