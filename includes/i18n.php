<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; }

/**
 * Load plugin textdomain.
 */
function quick_variants_load_textdomain() {
	load_plugin_textdomain( 'quick-variants', false, dirname( plugin_basename( QUICK_VARIANTS_PATH . 'quick-variants.php' ) ) . '/languages' );
}
add_action( 'plugins_loaded', 'quick_variants_load_textdomain' );
