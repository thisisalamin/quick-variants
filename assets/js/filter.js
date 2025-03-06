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
    let visibleVariants = new Set();

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
                    
                    // Restore variant visibility state
                    visibleVariants.forEach(productId => {
                        const $button = $('.toggle-variants[data-id="' + productId + '"]');
                        const $variantRows = $('.variant-' + productId);
                        if ($button.length && $variantRows.length) {
                            $variantRows.addClass('showing').show();
                            $button.text('HIDE VARIANTS');
                        }
                    });

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

    function performSearch(searchTerm = '', letter = 'all') {
        jQuery.ajax({
            url: wcFilter.ajaxUrl,
            type: 'POST',
            data: {
                action: 'search_products',
                nonce: wcFilter.nonce,
                search: searchTerm,
                letter: letter,
                category: wcFilter.category, // Make sure to include category
                per_page: wcFilter.per_page,
                visible_variants: getVisibleVariants()
            },
            success: function(response) {
                if (response.success) {
                    updateProductTable(response.data);
                }
            }
        });
    }

    // Add this function to track visible variants
    function updateVisibleVariants() {
        visibleVariants.clear();
        $('.variant-row.showing').each(function() {
            const productId = $(this).attr('class').match(/variant-(\d+)/)[1];
            visibleVariants.add(parseInt(productId));
        });
    }

    // Update the search/filter function
    function searchProducts(searchTerm = '', letter = '') {
        const data = {
            action: 'search_products',
            nonce: wcFilter.nonce,
            search: searchTerm,
            letter: letter,
            category: wcFilter.category,
            per_page: wcFilter.per_page,
            visible_variants: Array.from(visibleVariants)
        };

        jQuery.post(wcFilter.ajaxUrl, data, function(response) {
            if (response.success) {
                jQuery('#product-table tbody').html(response.data.html);
                updatePagination(response.data);
            }
        });
    }

    // Remove the old toggle handler and replace with this one
    $(document).on('click', '.toggle-variants', function() {
        const $button = $(this);
        const productId = $button.data('id');
        const $variantRows = $('.variant-' + productId);
        
        if ($variantRows.hasClass('showing')) {
            $variantRows.removeClass('showing').slideUp(300);
            $button.text('SHOW VARIANTS');
            visibleVariants.delete(productId);
        } else {
            $variantRows.addClass('showing').slideDown(300);
            $button.text('HIDE VARIANTS');
            visibleVariants.add(productId);
        }
        
        // Update visible variants tracking
        updateVisibleVariants();
    });

    // Track variant visibility when toggling
    $(document).on('click', '.toggle-variants', function() {
        const productId = $(this).data('id');
        const variantRows = $(`.variant-${productId}`);
        
        if (variantRows.first().is(':visible')) {
            visibleVariants.delete(productId);
        } else {
            visibleVariants.add(productId);
        }
        
        variantRows.toggle();
        updateVisibleVariants();
    });
});
