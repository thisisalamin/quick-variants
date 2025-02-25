jQuery(document).ready(function($) {
    // Hide all variant rows initially
    $('.variant-row').hide();
    
    $('.toggle-variants').on('click', function() {
        const productId = $(this).data('id');
        const variantSelector = '.variant-' + productId;
        const $variants = $(variantSelector);
        const $button = $(this);
        
        if ($button.hasClass('active')) {
            // Hide variants instantly without animation
            $variants.hide();
            $button.removeClass('active').text('SHOW VARIANTS');
        } else {
            // Show variants with smooth animation
            $variants.each(function(index) {
                const $row = $(this);
                // Set initial state before showing
                $row.css({
                    'opacity': 0,
                    'display': 'table-row' // Explicitly set to table-row
                });
                
                setTimeout(function() {
                    $row.animate({
                        opacity: 1
                    }, {
                        duration: 300,
                        easing: 'swing'
                    });
                }, index * 50); // Staggered delay based on row index
            });
            
            $button.addClass('active').text('HIDE VARIANTS');
        }
    });

    // Handle add to cart functionality
    $('.add-to-cart').on('click', function() {
        const productId = $(this).data('id');
        const quantity = $(this).closest('tr').find('input[type="number"]').val();
        // Add your add to cart logic here
    });
});
