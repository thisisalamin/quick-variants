jQuery(document).ready(function($) {
    function initializeVariantToggles() {
        // First remove any existing click handlers to prevent duplicates
        $('.toggle-variants').off('click');
        
        // Add new click handlers
        $('.toggle-variants').on('click', function() {
            const productId = $(this).data('id');
            const variantRows = $(`.variant-${productId}`);
            const button = $(this);
            
            // Store the visibility state
            const isVisible = !variantRows.is(':visible');
            
            // Toggle visibility with animation
            variantRows.slideToggle(200, function() {
                // Update button text based on final state
                button.text(isVisible ? 'HIDE VARIANTS' : 'SHOW VARIANTS');
            });
        });
    }

    // Initialize on page load
    initializeVariantToggles();

    // Reinitialize after any content update
    $(document).on('wc_product_table_updated', function() {
        initializeVariantToggles();
    });
});
