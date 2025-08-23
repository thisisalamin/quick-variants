<?php
global $product;
$visible_variants = get_query_var( 'visible_variants', array() );
?>
<tr class="group transition-colors duration-150 bg-white even:bg-gray-50 hover:bg-[#f5f9ff] !border-b !border-gray-200" data-product-id="<?php echo $product->get_id(); ?>">
	<td class="p-4 align-middle !border !border-gray-200 w-24 text-center">
		<?php
		$image   = wp_get_attachment_image_src( get_post_thumbnail_id(), 'full' );
		$img_url = $image ? $image[0] : wc_placeholder_img_src();
		?>
		<img src="<?php echo esc_url( $img_url ); ?>"
			alt="<?php echo esc_attr( get_the_title() ); ?>"
			class="w-[60px] h-[60px] object-contain mx-auto"/>
	</td>
	<td class="p-4 align-middle !border text-gray-800 !border-gray-200 w-[400px]">
		<div class="flex flex-col gap-1">
			<span class="font-semibold tracking-wide text-sm leading-snug group-hover:text-gray-900"><?php echo esc_html( get_the_title() ); ?></span>
			<?php if ( $product->is_on_sale() ) : ?>
				<span class="inline-flex items-center w-max gap-1 rounded-full bg-rose-50 text-rose-600 text-[10px] font-medium px-2 py-0.5 ring-1 ring-rose-200">
					<?php esc_html_e( 'On Sale', 'quick-variants' ); ?>
				</span>
			<?php endif; ?>
		</div>
	</td>
	<td class="p-4 align-middle !border !border-gray-200 text-center">
		<div class="flex flex-col items-center justify-center gap-1 text-sm">
			<?php if ( $product->is_type( 'variable' ) ) : ?>
				<?php $min_price = $product->get_variation_price( 'min' ); ?>
				<span class="text-xs uppercase tracking-wide text-gray-400"><?php esc_html_e( 'From', 'quick-variants' ); ?></span>
				<span class="text-gray-900 font-semibold"><?php echo wp_kses_post( wc_price( $min_price ) ); ?></span>
			<?php else : ?>
				<span class="text-gray-900 font-semibold leading-tight"><?php echo wp_kses_post( $product->get_price_html() ); ?></span>
			<?php endif; ?>
		</div>
	</td>
	<td class="p-4 align-middle !border !border-gray-200 text-center">
		<?php if ( ! $product->is_type( 'variable' ) ) : ?>
			<div class="flex justify-center">
				<input type="number" min="1" value="1" class="w-20 h-9 px-2 border border-gray-300 rounded-md text-center text-sm focus:outline-none focus:ring-2 focus:ring-offset-0 focus:ring-blue-500 focus:border-blue-500 transition">
			</div>
		<?php else : ?>
			<span class="text-xs text-gray-400 font-medium tracking-wide">—</span>
		<?php endif; ?>
	</td>
	<td class="p-4 align-middle !border !border-gray-200 text-center">
		<?php if ( $product->is_type( 'variable' ) ) : ?>
			<button class="toggle-variants relative inline-flex items-center justify-center gap-2 bg-[#232323] text-white px-4 py-2.5 text-[13px] font-semibold rounded-md hover:bg-white hover:text-black hover:border-black hover:border transition-all duration-300 w-full mx-auto shadow-sm focus:outline-none focus:ring-2 focus:ring-offset-0 focus:ring-black/40"
					data-id="<?php echo $product->get_id(); ?>">
				<span class="pointer-events-none">
					<?php echo in_array( $product->get_id(), $visible_variants, true ) ? esc_html__( 'Hide variants', 'quick-variants' ) : esc_html__( 'Show variants', 'quick-variants' ); ?>
				</span>
			</button>
		<?php else : ?>
			<button class="add-to-cart relative inline-flex items-center justify-center gap-2 bg-[#232323] text-white px-4 py-2.5 text-[13px] font-semibold rounded-md hover:bg-white hover:text-black hover:border-black hover:border transition-all duration-300 w-full mx-auto shadow-sm focus:outline-none focus:ring-2 focus:ring-offset-0 focus:ring-black/40"
					data-id="<?php echo $product->get_id(); ?>">
				<?php esc_html_e( 'Add to cart', 'quick-variants' ); ?>
			</button>
		<?php endif; ?>
	</td>
</tr>

<?php if ( $product->is_type( 'variable' ) ) : ?>
	<?php $variations = $product->get_available_variations(); ?>
	<?php
	foreach ( $variations as $variation ) :
		if ( ! $variation['is_in_stock'] || ! isset( $variation['display_price'] ) || $variation['display_price'] <= 0 ) {
			continue;
		}
		// Should we show variants by default if in visible_variants?
		$is_visible = in_array( $product->get_id(), $visible_variants );
		?>
		<tr class="variant-row variant-<?php echo $product->get_id(); ?> !border-b !border-gray-200 bg-white even:bg-gray-50 hover:bg-[#f1f7ff] transition-colors"
			style="display: <?php echo $is_visible ? 'table-row' : 'none'; ?>;">
			<td class="p-4 align-middle !border !border-gray-200">
				<img src="<?php echo esc_url( $variation['image']['url'] ); ?>"
					alt="<?php echo esc_attr( $variation['variation_description'] ); ?>"
					class="w-[60px] h-[60px] object-contain mx-auto" />
			</td>
			<td class="p-4 align-middle !border !border-gray-200">
				<?php
				$attributes = array();
				foreach ( $variation['attributes'] as $name => $value ) {
					$attributes[] = wc_attribute_label( str_replace( 'attribute_', '', $name ) ) . ': ' . ucfirst( $value );
				}
				?>
				<span class="text-[13px] font-medium tracking-wide text-gray-700 group-hover:text-gray-900">
					<?php echo esc_html( implode( ' / ', $attributes ) ); ?>
				</span>
			</td>
			<td class="p-4 align-middle !border !border-gray-200">
				<div class="flex flex-col items-center gap-1">
					<span class="text-[11px] uppercase tracking-wider text-gray-400 font-medium"><?php esc_html_e( 'Price', 'quick-variants' ); ?></span>
					<span class="font-semibold text-gray-900 text-sm leading-tight"><?php echo wp_kses_post( wc_price( $variation['display_price'] ) ); ?></span>
				</div>
			</td>
			<td class="p-4 align-middle !border !border-gray-200">
				<div class="flex justify-center">
					<input type="number" min="1" value="1" class="w-20 h-9 px-2 border border-gray-300 rounded-md text-center text-sm focus:outline-none focus:ring-2 focus:ring-offset-0 focus:ring-blue-500 focus:border-blue-500 transition">
				</div>
			</td>
			<td class="p-4 align-middle !border !border-gray-200">
				<button class="add-to-cart relative inline-flex items-center justify-center gap-2 bg-[#232323] text-white px-4 py-2.5 text-[13px] font-semibold rounded-md hover:bg-white hover:text-black hover:border-black hover:border transition-all duration-300 w-full shadow-sm focus:outline-none focus:ring-2 focus:ring-offset-0 focus:ring-black/40"
						data-id="<?php echo $variation['variation_id']; ?>">
					<?php esc_html_e( 'Add to cart', 'quick-variants' ); ?>
				</button>
			</td>
		</tr>
	<?php endforeach; ?>
<?php endif; ?>
