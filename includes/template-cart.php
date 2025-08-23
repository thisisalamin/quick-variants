<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; }

/**
 * Slide-out cart template output in footer.
 */
function quick_variants_cart_template() {
	?>
	<div id="slide-cart" class="qv-cart-panel flex flex-col font-sans">
		<div class="flex flex-col h-full">
			<div class="qv-cart-header flex justify-between items-center px-5 py-4 border-b border-gray-200 bg-white/90 backdrop-blur">
				<div class="flex flex-col gap-0.5">
					<h2 class="qv-cart-title text-base font-semibold tracking-wide text-gray-900"><?php esc_html_e( 'Shopping Cart', 'quick-variants' ); ?></h2>
					<p class="text-gray-500 text-xs font-medium"><span id="cart-count">0</span> <?php esc_html_e( 'items', 'quick-variants' ); ?></p>
				</div>
				<button id="close-cart" class="text-gray-400 hover:text-gray-500 transition-colors" aria-label="<?php esc_attr_e( 'Close cart', 'quick-variants' ); ?>">
					<svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
						<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
					</svg>
				</button>
			</div>
			<div class="qv-cart-items-wrapper flex-1 overflow-y-auto p-5 space-y-4 bg-gray-50/60">
				<div id="cart-items" class="space-y-4 text-sm"></div>
			</div>
			<div class="qv-cart-footer border-t border-gray-200 p-4 bg-white">
				<div class="flex justify-between items-center mb-1 text-sm">
					<span class="font-medium text-gray-600"><?php esc_html_e( 'Subtotal', 'quick-variants' ); ?>:</span>
					<span id="cart-subtotal" class="font-semibold text-gray-900">0.00</span>
				</div>
				<div class="flex justify-between items-center mb-4 text-sm">
					<span class="font-medium text-gray-600"><?php esc_html_e( 'Total', 'quick-variants' ); ?>:</span>
					<span id="cart-total" class="font-semibold text-gray-900">0.00</span>
				</div>
				<a href="<?php echo esc_url( wc_get_checkout_url() ); ?>" class="qv-cart-btn primary w-full mb-2" data-action="checkout"><?php esc_html_e( 'Checkout', 'quick-variants' ); ?></a>
				<a href="<?php echo esc_url( wc_get_cart_url() ); ?>" class="qv-cart-btn secondary w-full" data-action="view-cart"><?php esc_html_e( 'View Cart', 'quick-variants' ); ?></a>
			</div>
		</div>
	</div>
	<div id="cart-overlay"></div>
	<?php
}
function quick_variants_maybe_hook_cart_template() {
	if ( quick_variants_get_setting( 'show_slide_cart' ) ) {
		add_action( 'wp_footer', 'quick_variants_cart_template' );
	}
}
add_action( 'init', 'quick_variants_maybe_hook_cart_template' );
