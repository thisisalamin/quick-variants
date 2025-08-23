<?php
/**
 * Quick view modal partial. Expect $quick_variant_product via set_query_var.
 */
$product = get_query_var( 'quick_variant_product' );
if ( ! $product ) {
	return;
}
?>
<div class="qv-quick-view max-w-4xl mx-auto p-6 bg-white rounded-lg shadow-lg">
	<div class="grid grid-cols-1 md:grid-cols-2 gap-6">
		<div class="product-gallery">
			<?php
			$image_id = $product->get_image_id();
			$image    = $image_id ? wp_get_attachment_image_url( $image_id, 'large' ) : wc_placeholder_img_src();
			?>
			<img src="<?php echo esc_url( $image ); ?>" alt="<?php echo esc_attr( $product->get_name() ); ?>" class="w-full h-auto object-contain" loading="lazy" />
		</div>
		<div class="product-details">
			<h2 class="text-xl font-semibold mb-2"><?php echo esc_html( $product->get_name() ); ?></h2>
			<div class="text-sm text-gray-600 mb-3">
				<?php echo wp_kses_post( wpautop( $product->get_short_description() ) ); ?>
			</div>
			<div class="mb-3">
				<span class="text-lg font-bold"><?php echo wp_kses_post( $product->get_price_html() ); ?></span>
			</div>
			<?php if ( $product->is_type( 'variable' ) ) : ?>
				<div class="product-attributes mb-3">
					<?php
					$available_variations = $product->get_available_variations();
					if ( ! empty( $available_variations ) ) :
						foreach ( $available_variations as $variation ) :
							$attrs = array_values( $variation['attributes'] );
							?>
							<div class="mb-2 text-sm text-gray-700"><?php echo esc_html( implode( ' / ', $attrs ) ); ?> — <?php echo wp_kses_post( wc_price( $variation['display_price'] ) ); ?></div>
							<?php
						endforeach;
					endif;
					?>
				</div>
			<?php endif; ?>
			<div class="flex items-center gap-3">
				<button class="qv-add-to-cart inline-flex items-center gap-2 bg-[#232323] text-white px-4 py-2 rounded-md" data-id="<?php echo esc_attr( $product->get_id() ); ?>"><?php esc_html_e( 'Add to cart', 'quick-variants' ); ?></button>
				<button class="qv-close inline-flex items-center gap-2 border px-4 py-2 rounded-md"><?php esc_html_e( 'Close', 'quick-variants' ); ?></button>
			</div>
		</div>
	</div>
</div>
