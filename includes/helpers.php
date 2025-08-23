<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; }

/** General helper functions */
function quick_variants_format_price( $price_html ) {
	return html_entity_decode( strip_tags( $price_html ) );
}
