<?php
/**
 * Quick view modal partial. Expect $quick_variant_product via set_query_var.
 */
$product = get_query_var( 'quick_variant_product' );
if ( ! $product ) {
	return;
}
?>
<?php
// Pass variations to JS via data attribute (JSON)
$variations_for_js = array();
if ( $product->is_type( 'variable' ) ) {
	$available_variations = $product->get_available_variations();
	foreach ( $available_variations as $v ) {
		$variations_for_js[] = array(
			'variation_id'  => isset( $v['variation_id'] ) ? $v['variation_id'] : 0,
			'attributes'    => isset( $v['attributes'] ) ? $v['attributes'] : array(),
			'display_price' => isset( $v['display_price'] ) ? $v['display_price'] : '',
		);
	}
}
$variations_json = wp_json_encode( $variations_for_js );
?>
<div class="qv-quick-view max-w-4xl mx-auto bg-white rounded-lg shadow-lg" role="dialog" aria-modal="true" aria-labelledby="qv-title" data-variations='<?php echo esc_attr( $variations_json ); ?>'>
	<button type="button" class="qv-close qv-close-top absolute top-3 right-3 p-2 rounded-md bg-white border shadow-sm" aria-label="<?php esc_attr_e( 'Close quick view', 'quick-variants' ); ?>">
		<svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-700" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
			<path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
		</svg>
	</button>
	<div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-6">
		<div class="product-gallery flex items-center justify-center">
			<?php
			$image_id = $product->get_image_id();
			$image    = $image_id ? wp_get_attachment_image_url( $image_id, 'large' ) : wc_placeholder_img_src();
			?>
			<img src="<?php echo esc_url( $image ); ?>" alt="<?php echo esc_attr( $product->get_name() ); ?>" class="w-full h-auto object-contain max-h-96" loading="lazy" />
		</div>
		<div class="product-details relative">
			<h2 id="qv-title" class="text-2xl font-semibold mb-2"><?php echo esc_html( $product->get_name() ); ?></h2>
			<div class="mb-3 text-gray-700">
				<span class="text-2xl font-bold"><?php echo wp_kses_post( $product->get_price_html() ); ?></span>
			</div>
			<div class="text-sm text-gray-600 mb-4 max-h-48 overflow-auto">
				<?php echo wp_kses_post( wpautop( $product->get_short_description() ) ); ?>
			</div>

			<?php if ( $product->is_type( 'variable' ) ) : ?>
				<?php
				$attributes           = $product->get_variation_attributes(); // associative array of taxonomy => options
				$available_variations = $product->get_available_variations();
				?>
				<div class="product-attributes mb-4 text-sm text-gray-700">
					<strong><?php esc_html_e( 'Choose options', 'quick-variants' ); ?></strong>
					<div class="mt-2 space-y-3">
						<?php
						foreach ( $attributes as $attr_name => $options ) :
							$clean_name  = wc_attribute_label( str_replace( 'attribute_', '', $attr_name ) );
							$select_name = esc_attr( $attr_name );
							?>
							<div class="flex items-center gap-2">
								<label class="w-32 text-sm text-gray-600"><?php echo esc_html( $clean_name ); ?></label>
								<select class="qv-attr-select border rounded p-2 w-full" data-attr="<?php echo esc_attr( $attr_name ); ?>">
									<option value=""><?php esc_html_e( 'Select', 'quick-variants' ); ?></option>
									<?php foreach ( $options as $opt ) : ?>
										<option value="<?php echo esc_attr( $opt ); ?>"><?php echo esc_html( $opt ); ?></option>
									<?php endforeach; ?>
								</select>
							</div>
						<?php endforeach; ?>
					</div>
					<input type="hidden" class="qv-selected-variation-id" value="" />
				</div>
				<div class="qv-variation-info text-sm text-gray-700 mb-2 hidden"></div>
			<?php endif; ?>

			<div class="flex items-center gap-3 mt-4">
				<button class="qv-add-to-cart qv-cart-btn primary inline-flex items-center gap-2 px-4 py-2 rounded-md" data-id="<?php echo esc_attr( $product->get_id() ); ?>"><?php esc_html_e( 'Add to cart', 'quick-variants' ); ?></button>
				<button class="qv-close qv-cart-btn secondary inline-flex items-center gap-2 px-4 py-2 rounded-md"><?php esc_html_e( 'Close', 'quick-variants' ); ?></button>
			</div>
		</div>
	</div>
</div>
