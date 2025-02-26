jQuery(document).ready(function($) {
    $('.show-more-button').on('click', function(e) {
        e.preventDefault();
        const button = $(this);
        const currentPage = parseInt(button.data('page'));
        const perPage = parseInt(button.data('per-page'));
        const total = parseInt(button.data('total'));
        
        const buttonContent = button.find('.button-content');
        const buttonText = buttonContent.find('.button-text');
        const loader = buttonContent.find('.loader');

        // Show loader
        buttonText.hide();
        loader.show();

        $.ajax({
            url: wcPagination.ajaxUrl,
            type: 'POST',
            data: {
                action: 'load_more_products',
                nonce: wcPagination.nonce,
                page: currentPage + 1,
                per_page: perPage,
                category: button.data('category')
            },
            success: function(response) {
                if (response.success) {
                    // Append new products
                    $('#product-table tbody').append(response.data.html);
                    
                    // Update pagination counter
                    const startCount = 1;
                    const endCount = Math.min((currentPage + 1) * perPage, total);
                    
                    $('[data-total-start]').text(startCount);
                    $('[data-total-end]').text(endCount);

                    // Update progress bar
                    const progressPercentage = (endCount / total) * 100;
                    $('.pagination-total-item').css('width', progressPercentage + '%');

                    // Update button data and visibility
                    button.data('page', currentPage + 1);
                    if (!response.data.has_more) {
                        button.parent().hide();
                    }
                }
            },
            complete: function() {
                // Hide loader and show text
                loader.hide();
                buttonText.show();
            }
        });
    });
});
