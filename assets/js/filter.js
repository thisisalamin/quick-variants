jQuery(document).ready(function($) {
    let searchTimeout;
    const productTable = $('#product-table tbody');
    const paginationWrapper = $('.pagination-wrapper');
    const searchInput = $('#product-search');
    const filterButton = $('#filter-button');
    const filterDropdown = $('#filter-dropdown');
    const alphabetFilters = $('.alphabet-filter');
    const currentFilter = $('#current-filter');
    let currentLetter = 'all';
    let searchTimer;
    const searchDelay = 500; // Delay in milliseconds

    // Toggle dropdown
    filterButton.on('click', function(e) {
        e.stopPropagation();
        filterDropdown.toggleClass('show');
    });

    // Close dropdown when clicking outside
    $(document).on('click', function(e) {
        if (!$(e.target).closest('.dropdown-filter').length) {
            filterDropdown.removeClass('show');
        }
    });

    // Function to update products
    function updateProducts(searchTerm = '', letter = '') {
        $.ajax({
            url: wcFilter.ajaxUrl,
            type: 'POST',
            data: {
                action: 'search_products',
                nonce: wcFilter.nonce,
                search: searchTerm,
                letter: letter,
                per_page: $('.show-more-button').data('per-page'),
                category: $('#product-table').data('category') // Get category if set
            },
            beforeSend: function() {
                $('#product-table tbody').addClass('loading');
            },
            success: function(response) {
                if (response.success) {
                    productTable.html(response.data.html);
                    // Trigger our custom event after updating content
                    $(document).trigger('wc_product_table_updated');
                    
                    // Show/hide pagination based on search/filter status
                    if (response.data.show_pagination) {
                        paginationWrapper.show();
                        // Reset pagination display
                        $('.pagination-page-total span[data-total-end]').text(
                            Math.min($('.show-more-button').data('per-page'), response.data.total_products)
                        );
                    } else {
                        paginationWrapper.hide();
                    }
                }
            },
            complete: function() {
                $('#product-table tbody').removeClass('loading');
            }
        });
    }

    function updateProductTable(response) {
        if (response.success) {
            $('#product-table tbody').html(response.data.html);
            updatePagination(response.data);
            // Trigger custom event after content update
            $(document).trigger('wc_product_table_updated');
        }
    }

    // Search input handler with debounce
    $('#product-search').on('input', function() {
        clearTimeout(searchTimeout);
        const searchTerm = $(this).val();
        const activeLetter = $('.alphabet-filter.active').data('letter');
        
        searchTimeout = setTimeout(function() {
            updateProducts(searchTerm, activeLetter);
        }, 300);
    });

    // Clear search
    $('#product-search').on('search', function() {
        if ($(this).val() === '') {
            updateProducts('', 'all');
        }
    });

    // Alphabet filter handler
    $('.alphabet-filter').click(function() {
        $('.alphabet-filter').removeClass('active');
        $(this).addClass('active');
        
        const letter = $(this).data('letter');
        const searchTerm = $('#product-search').val();
        
        updateProducts(searchTerm, letter);
    });
});
