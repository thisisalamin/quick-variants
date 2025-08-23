<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; }

/**
 * Slide-out cart template output in footer.
 */
function quick_variants_cart_template() {
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
				<div id="cart-items" class="space-y-4"></div>
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
				<a href="<?php echo esc_url( wc_get_checkout_url() ); ?>" class="block w-full text-sm bg-[#232323] text-white py-2.5 mb-2 hover:bg-black/80 transition-all duration-300 text-center font-bold">CHECKOUT</a>
				<a href="<?php echo esc_url( wc_get_cart_url() ); ?>" class="block w-full text-sm border border-gray-300 text-gray-700 py-2.5 hover:bg-gray-50 transition-all duration-300 text-center font-bold">VIEW CART</a>
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
