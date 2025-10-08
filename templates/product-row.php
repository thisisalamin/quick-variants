<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

global $product;
$visible_variants = get_query_var( 'visible_variants', array() );
?>
<tr class="group transition-colors duration-150 bg-white even:bg-gray-50 hover:bg-[#f5f9ff] !border-b !border-gray-200" data-product-id="<?php echo esc_attr( $product->get_id() ); ?>">
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
		<div class="flex items-center gap-2 justify-center">
			<?php if ( $product->is_type( 'variable' ) ) : ?>
				<button class="toggle-variants relative inline-flex items-center justify-center gap-2 bg-[#232323] text-white px-4 py-2.5 text-[13px] font-semibold rounded-md hover:bg-white hover:text-black hover:border-black hover:border transition-all duration-300 shadow-sm focus:outline-none focus:ring-2 focus:ring-offset-0 focus:ring-black/40"
						data-id="<?php echo esc_attr( $product->get_id() ); ?>">
					<span class="pointer-events-none">
						<?php echo in_array( $product->get_id(), $visible_variants, true ) ? esc_html__( 'Hide variants', 'quick-variants' ) : esc_html__( 'Show variants', 'quick-variants' ); ?>
					</span>
				</button>
			<?php else : ?>
				<button class="add-to-cart relative inline-flex items-center justify-center gap-2 bg-[#232323] text-white px-4 py-2.5 text-[13px] font-semibold rounded-md hover:bg-white hover:text-black hover:border-black hover:border transition-all duration-300 shadow-sm focus:outline-none focus:ring-2 focus:ring-offset-0 focus:ring-black/40"
						data-id="<?php echo esc_attr( $product->get_id() ); ?>">
					<?php echo esc_html( quick_variants_get_setting( 'label_add_to_cart' ) ); ?>
				</button>
			<?php endif; ?>

			<!-- Quick view button -->
			<?php if ( function_exists( 'quick_variants_get_setting' ) && quick_variants_get_setting( 'enable_quick_view' ) ) : ?>
			<button class="qv-quick-view-btn inline-flex items-center justify-center p-2 rounded-md border hover:bg-gray-50" title="<?php esc_attr_e( 'Quick view', 'quick-variants' ); ?>" data-id="<?php echo esc_attr( $product->get_id() ); ?>">
				<svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-700" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
					<path d="M10 3C5 3 1.73 6.11 1 10c.73 3.89 4 7 9 7s8.27-3.11 9-7c-.73-3.89-4-7-9-7zm0 12a5 5 0 110-10 5 5 0 010 10z" />
					<path d="M10 7a3 3 0 100 6 3 3 0 000-6z" />
				</svg>
			</button>
			<?php endif; ?>
		</div>
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
		<tr class="variant-row variant-<?php echo esc_attr( $product->get_id() ); ?> !border-b !border-gray-200 bg-white even:bg-gray-50 hover:bg-[#f1f7ff] transition-colors"
			style="display: <?php echo $is_visible ? 'table-row' : 'none'; ?>;">
			<td class="p-4 align-middle !border !border-gray-200">
				<?php
				$variation_image_url = '';
				if ( ! empty( $variation['image']['url'] ) ) {
					$variation_image_url = $variation['image']['url'];
				} else {
					// Fallback to parent product image
					$parent_image        = wp_get_attachment_image_src( get_post_thumbnail_id( $product->get_id() ), 'full' );
					$variation_image_url = $parent_image ? $parent_image[0] : wc_placeholder_img_src();
				}
				?>
				<img src="<?php echo esc_url( $variation_image_url ); ?>"
					alt="<?php echo esc_attr( $product->get_name() . ' - ' . implode( ', ', array_values( $variation['attributes'] ) ) ); ?>"
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
				<div class="flex items-center gap-2">
					<button class="add-to-cart relative inline-flex items-center justify-center gap-2 bg-[#232323] text-white px-4 py-2.5 text-[13px] font-semibold rounded-md hover:bg-white hover:text-black hover:border-black hover:border transition-all duration-300 w-full shadow-sm focus:outline-none focus:ring-2 focus:ring-offset-0 focus:ring-black/40"
							data-id="<?php echo esc_attr( $variation['variation_id'] ); ?>">
						<?php echo esc_html( quick_variants_get_setting( 'label_add_to_cart' ) ); ?>
					</button>
					<?php if ( function_exists( 'quick_variants_get_setting' ) && quick_variants_get_setting( 'enable_quick_view' ) ) : ?>
					<button class="qv-quick-view-btn inline-flex items-center justify-center p-2 rounded-md border hover:bg-gray-50" title="<?php esc_attr_e( 'Quick view', 'quick-variants' ); ?>" data-id="<?php echo esc_attr( $variation['variation_id'] ); ?>">
						<svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-700" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
							<path d="M10 3C5 3 1.73 6.11 1 10c.73 3.89 4 7 9 7s8.27-3.11 9-7c-.73-3.89-4-7-9-7zm0 12a5 5 0 110-10 5 5 0 010 10z" />
							<path d="M10 7a3 3 0 100 6 3 3 0 000-6z" />
						</svg>
					</button>
					<?php endif; ?>
				</div>
			</td>
		</tr>
	<?php endforeach; ?>
<?php endif; ?>
