jQuery(document).ready(function($) {
    // Hide all variants initially
    $('.variant-row').hide().removeClass('show');
    
    // Toggle variants with improved mobile support and event handling
    $(document).on('click', '.toggle-variants', function(e) {
        e.preventDefault();
        e.stopPropagation();
        e.stopImmediatePropagation(); // Prevent multiple triggers
        
        const $button = $(this);
        const productId = $button.data('id');
        const $variants = $('.variant-' + productId);
        
        // Toggle show class and visibility
        if ($variants.hasClass('show')) {
            $variants
                .removeClass('show')
                .addClass('hiding') // Add transitional class
                .hide()
                .removeClass('hiding'); // Remove transitional class
            $button.text('SHOW VARIANTS');
            
            // Force hide on mobile with !important
            $variants.attr('style', 'display: none !important');
        } else {
            $variants
                .addClass('show')
                .show()
                .removeAttr('style'); // Remove forced styling
            $button.text('HIDE VARIANTS');
            
            // Force proper display for mobile
            if (window.innerWidth <= 767) {
                $variants.css({
                    'display': 'flex',
                    'flex-direction': 'column',
                    'width': '100%'
                });
            }
        }
    });

    // Handle new content
    $(document).on('productsLoaded', function() {
        $('.variant-row').hide().removeClass('show');
    });

    // Handle "SHOW MORE" button click
    $(document).on('click', '.show-more-button', function(e) {
        e.preventDefault();
        
        var button = $(this);
        var page = button.data('page');
        var perPage = button.data('per-page');
        var total = button.data('total');
        var category = button.data('category');

        $.ajax({
            url: wcPagination.ajaxUrl,
            type: 'POST',
            data: {
                action: 'search_products', // Changed to use the unified search endpoint
                nonce: wcPagination.nonce,
                page: page + 1,
                per_page: perPage,
                category: category
            },
            beforeSend: function() {
                button.find('.loader').show();
                button.find('.button-text').hide();
            },
            success: function(response) {
                if (response.success) {
                    $('#product-table tbody').append(response.data.html);
                    button.data('page', page + 1);
                    
                    // Update the showing count
                    var startCount = 1;
                    var endCount = (page + 1) * perPage;
                    if (endCount > total) endCount = total;
                    
                    $('[data-total-start]').text(startCount);
                    $('[data-total-end]').text(endCount);
                    
                    // Update progress bar
                    var progressPercent = (endCount / total) * 100;
                    $('.pagination-total-item').css('width', progressPercent + '%');

                    // Hide button if we've shown all products
                    if ((page + 1) * perPage >= total) {
                        button.parent().hide();
                    }
                }
            },
            complete: function() {
                button.find('.loader').hide();
                button.find('.button-text').show();
            }
        });
    });

    // Add handler for search and filter updates
    function updateProductCount(currentCount, total) {
        var startCount = 1;
        var endCount = currentCount;
        if (endCount > total) endCount = total;
        
        $('[data-total-start]').text(startCount);
        $('[data-total-end]').text(endCount);
        $('.pagination-total-progress span').css('width', ((endCount / total) * 100) + '%');
    }

    // Hook this into your existing filter/search handlers
    $(document).on('productsFiltered', function(e, data) {
        updateProductCount(data.count, data.total);
    });
});
