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
        const perPage = $('#product-table').data('per-page');
        
        $.ajax({
            url: wcFilter.ajaxUrl,
            type: 'POST',
            data: {
                action: 'search_products',
                nonce: wcFilter.nonce,
                search: searchTerm,
                letter: letter,
                per_page: $('.show-more-button').data('per-page'),
                category: wcFilter.category // Always use the category from shortcode
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
                        
                        // Update pagination numbers
                        const totalProducts = response.data.total_products;
                        const currentEnd = Math.min(perPage, totalProducts);
                        
                        $('[data-total-start]').text('1');
                        $('[data-total-end]').text(currentEnd);
                        $('.pagination-page-total .font-medium:last').text(totalProducts);
                        
                        // Update progress bar
                        const progressPercentage = (currentEnd / totalProducts) * 100;
                        $('.pagination-total-item').css('width', progressPercentage + '%');
                        
                        // Update show more button
                        $('.show-more-button')
                            .data('total', totalProducts)
                            .data('page', 1)
                            .data('per-page', perPage);
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

    function performSearch(searchTerm, letter) {
        $.ajax({
            url: wcFilter.ajaxUrl,
            type: 'POST',
            data: {
                action: 'search_products',
                nonce: wcFilter.nonce,
                search: searchTerm,
                letter: letter,
                category: wcFilter.category // Always include the category
            },
            success: function(response) {
                if (response.success) {
                    $('#product-table tbody').html(response.data.html);
                    
                    // Trigger event with count data
                    $(document).trigger('productsFiltered', {
                        count: response.data.count,
                        total: response.data.total_products
                    });

                    // Update pagination visibility
                    if (response.data.show_pagination) {
                        $('.pagination-button').show();
                    } else {
                        $('.pagination-button').hide();
                    }
                }
            }
            // ...rest of the code...
        });
    }
});
