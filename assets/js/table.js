jQuery(document).ready(function($) {
    // Replace any direct event binding with delegated events
    $(document).on('click', '.toggle-variants', function(e) {
        e.preventDefault();
        const productId = $(this).data('id');
        const variantRows = $('.variant-' + productId);
        
        if (variantRows.is(':visible')) {
            variantRows.hide();
            $(this).text('SHOW VARIANTS');
        } else {
            variantRows.show();
            $(this).text('HIDE VARIANTS');
        }
    });

    // Hide all variant rows initially
    $('.variant-row').hide();

    // Function to initialize variant rows for new content
    function initializeVariantRows() {
        $('.variant-row').hide();
    }

    // Listen for custom event after content is loaded
    $(document).on('productsLoaded', function() {
        initializeVariantRows();
    });
});
