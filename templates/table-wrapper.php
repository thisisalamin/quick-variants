<?php
/**
 * Table wrapper template.
 * Expects $atts, $total_products, $args_for_display in scope.
 */
?>
<!-- Search + Filter Section -->
<div class="search-filter-container">
	<div class="search-filter-wrapper">
		<div class="search-section">
			<div class="search-input-wrapper">
				<input type="text" id="product-search" placeholder="<?php esc_attr_e( 'Search products...', 'quick-variants' ); ?>" class="search-input">
			</div>
			<?php if ( ! empty( $enable_alpha ) ) : ?>
			<div class="alphabet-filter-container">
				<button class="alphabet-filter active" data-letter="all"><?php esc_html_e( 'All', 'quick-variants' ); ?></button>
				<?php foreach ( range( 'A', 'Z' ) as $letter ) : ?>
					<button class="alphabet-filter" data-letter="<?php echo esc_attr( $letter ); ?>"><?php echo esc_html( $letter ); ?></button>
				<?php endforeach; ?>
			</div>
			<?php endif; ?>
		</div>
	</div>
</div>

<?php if ( ! empty( $button_color ) ) : ?>
<style>
.add-to-cart,
.toggle-variants,
.show-more-button,
.alphabet-filter.active { background-color: <?php echo esc_html( $button_color ); ?> !important; border-color: <?php echo esc_html( $button_color ); ?> !important; }
.add-to-cart:hover,
.toggle-variants:hover,
.show-more-button:hover { color: <?php echo esc_html( $button_color ); ?> !important; }
.pagination-total-item { background-color: <?php echo esc_html( $button_color ); ?> !important; }
</style>
<?php endif; ?>

<div class="overflow-x-auto">
	<table id="product-table"
			class="min-w-full border-collapse !border !border-gray-200"
			data-total="<?php echo esc_attr( $total_products ); ?>"
			data-per-page="<?php echo esc_attr( $atts['per_page'] ); ?>">
		<thead>
		<tr class="!border-b !border-gray-200 bg-[#FAFAFA]">
			<th style="width:120px !important" class="p-4 align-middle text-center font-semibold !border !border-gray-200"><?php esc_html_e( 'Images', 'quick-variants' ); ?></th>
			<th style="width:50% !important" class="p-4 align-middle text-left font-semibold !border !border-gray-200 w-[400px]"><?php esc_html_e( 'Product', 'quick-variants' ); ?></th>
			<th style="width:30% !important" class="p-4 align-middle text-center font-semibold !border !border-gray-200"><?php esc_html_e( 'Price', 'quick-variants' ); ?></th>
			<th style="width:100px !important" class="p-4 align-middle text-center font-semibold !border !border-gray-200"><?php esc_html_e( 'Qty', 'quick-variants' ); ?></th>
			<th style="width:180px !important" class="p-4 align-middle text-center font-semibold !border !border-gray-200"><?php esc_html_e( 'Options', 'quick-variants' ); ?></th>
		</tr>
		</thead>
		<tbody>
		<?php
		$display_query = new WP_Query( $args_for_display );
		while ( $display_query->have_posts() ) :
			$display_query->the_post();
			global $product; // template expects $product
			include QUICK_VARIANTS_PATH . 'templates/product-row.php';
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
				<span><?php esc_html_e( 'Showing', 'quick-variants' ); ?></span>
				<span data-total-start="1" class="font-medium">1</span>
				<span>-</span>
				<span data-total-end="<?php echo esc_html( min( $atts['per_page'], $total_products ) ); ?>" class="font-medium">
					<?php echo esc_html( min( $atts['per_page'], $total_products ) ); ?>
				</span>
				<span><?php esc_html_e( 'of', 'quick-variants' ); ?></span>
				<span class="font-medium"><?php echo esc_html( $total_products ); ?></span>
				<span><?php esc_html_e( 'total', 'quick-variants' ); ?></span>
			</div>
			<div class="pagination-total-progress">
				<?php $initial_progress = $total_products > 0 ? ( $atts['per_page'] / $total_products ) * 100 : 0; ?>
				<span style="width: <?php echo esc_attr( $initial_progress ); ?>%" class="pagination-total-item"></span>
			</div>
		</div>
		<div class="pagination-button">
			<a href="#" class="show-more-button"
				data-page="1"
				data-per-page="<?php echo esc_attr( $atts['per_page'] ); ?>"
				data-total="<?php echo esc_attr( $total_products ); ?>">
				<div class="button-content">
					<span class="loader"></span>
					<span class="button-text"><?php esc_html_e( 'Show more', 'quick-variants' ); ?></span>
				</div>
			</a>
		</div>
	</nav>
</div>
