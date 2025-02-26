jQuery(document).ready(function($) {
    // Hide all variants initially
    $('.variant-row').hide().removeClass('show');
    
    // Toggle variants
    $(document).on('click', '.toggle-variants', function(e) {
        e.preventDefault();
        e.stopPropagation();
        
        const $button = $(this);
        const productId = $button.data('id');
        const $variants = $('.variant-' + productId);
        
        // Toggle show class and visibility
        if ($variants.first().hasClass('show')) {
            $variants.removeClass('show').hide();
            $button.text('SHOW VARIANTS');
        } else {
            $variants.addClass('show').show();
            $button.text('HIDE VARIANTS');
        }
    });

    // Handle new content
    $(document).on('productsLoaded', function() {
        $('.variant-row').hide().removeClass('show');
    });
});
