jQuery(document).ready(function($) {
    $('.show-more-button').on('click', function(e) {
        e.preventDefault();
        const button = $(this);
        const page = parseInt(button.data('page')) + 1;
        const perPage = button.data('per-page');
        const total = button.data('total');

        // Add loading state to button only
        button.addClass('loading');

        loadMoreProducts(page);
    });

    function loadMoreProducts(page) {
        const button = $('.show-more-button');
        const perPage = button.data('per-page');
        const total = button.data('total');

        $.ajax({
            url: wcPagination.ajaxUrl,
            type: 'POST',
            data: {
                action: 'load_more_products',
                nonce: wcPagination.nonce,
                page: page,
                per_page: perPage
            },
            success: function(response) {
                appendNewProducts(response);
            },
            error: function(xhr, status, error) {
                console.error('Error loading more products:', error);
            },
            complete: function() {
                // Remove loading state
                button.removeClass('loading');
            }
        });
    }

    function appendNewProducts(response) {
        if (response.success) {
            $('#product-table tbody').append(response.data.html);
            // Trigger custom event after content update
            $(document).trigger('wc_product_table_updated');
            // Update pagination UI
            updatePaginationUI(response.data);
        }
    }

    function updatePaginationUI(data) {
        const button = $('.show-more-button');
        const page = parseInt(button.data('page'));
        const perPage = button.data('per-page');
        const total = button.data('total');

        // Update pagination display
        button.data('page', page);

        // Update total items showing
        const startItem = 1;
        const endItem = page * perPage;
        $('[data-total-end]').text(Math.min(endItem, total));

        // Update progress bar
        const progress = (Math.min(endItem, total) / total) * 100;
        $('.pagination-total-item').css('width', progress + '%');

        // Hide show more button if no more items
        if (!data.has_more) {
            button.parent().hide();
        }
    }
});
