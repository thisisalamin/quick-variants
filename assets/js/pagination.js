jQuery(document).ready(function($) {
    $('.show-more-button').on('click', function(e) {
        e.preventDefault();
        
        const button = $(this);
        const currentPage = parseInt(button.data('page'));
        const perPage = parseInt(button.data('per-page'));
        const total = parseInt(button.data('total'));
        
        $.ajax({
            url: wcPagination.ajaxUrl,
            type: 'POST',
            data: {
                action: 'load_more_products',
                nonce: wcPagination.nonce,
                page: currentPage + 1,
                per_page: perPage,
                category: '' // Add category if needed
            },
            success: function(response) {
                if (response.success) {
                    // Append new products
                    $('#product-table tbody').append(response.data.html);
                    
                    // Update button page number
                    button.data('page', currentPage + 1);
                    
                    // Update showing count
                    const startCount = 1;
                    const endCount = Math.min((currentPage + 1) * perPage, total);
                    $('[data-total-start]').text(startCount);
                    $('[data-total-end]').text(endCount);
                    
                    // Update progress bar
                    const progressPercentage = (endCount / total) * 100;
                    $('.pagination-total-item').css('width', progressPercentage + '%');
                    
                    // Hide button if no more products
                    if (!response.data.has_more) {
                        button.parent().hide();
                    }
                }
            }
        });
    });
});
