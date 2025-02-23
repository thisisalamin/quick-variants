jQuery(document).ready(function($) {
    $('.toggle-variants').on('click', function() {
        const productId = $(this).data('id');
        const variantRows = $(`.variant-${productId}`);
        const button = $(this);

        if (variantRows.hasClass('show')) {
            // Hide variants - faster transition
            variantRows.removeClass('show');
            setTimeout(() => {
                variantRows.css('display', 'none');
            }, 200); // Reduced from 300ms to 200ms to match CSS
            button.text('SHOW VARIANTS');
        } else {
            // Show variants
            variantRows.css('display', 'table-row');
            requestAnimationFrame(() => {
                variantRows.addClass('show');
            });
            button.text('HIDE VARIANTS');
        }
    });

    // Handle add to cart functionality
    $('.add-to-cart').on('click', function() {
        const productId = $(this).data('id');
        const quantity = $(this).closest('tr').find('input[type="number"]').val();
        // Add your add to cart logic here
    });
});
