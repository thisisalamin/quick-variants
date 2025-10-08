jQuery(document).ready(function($) {

    let productTable      = $('#product-table tbody');
    let paginationWrapper = $('.pagination-wrapper');
    let searchInput       = $('#product-search');
    let alphabetFilters   = $('.alphabet-filter');
    let visibleVariants   = new Set(); // tracks product IDs whose variants are visible

    // Helps us “debounce” the search calls
    let searchTimeout     = null;
    const searchDelay     = 300; // ms

    /**
     * Universal function to fetch products via AJAX
     * page        -> which page of results
     * searchTerm  -> text query
     * letter      -> A-Z filter
     * append      -> if true, we append results; otherwise we replace
     */
    function updateProducts(searchTerm = '', letter = 'all', page = 1, append = false) {
        const perPage  = $('#product-table').data('per-page') || 10;
        $.ajax({
            url: quicvaFilter.ajaxUrl,
            type: 'POST',
            data: {
                action:           'quicva_search_products',
                nonce:            quicvaFilter.nonce,
                search:           searchTerm,
                letter:           letter,
                page:             page,
                per_page:         perPage,
                category:         quicvaFilter.category,
                visible_variants: Array.from(visibleVariants)
            },
            beforeSend: function() {
                productTable.addClass('loading');
            },
            success: function(response) {
                productTable.removeClass('loading');
                if (response.success) {
                    let data = response.data;

                    // If append = true, we add to existing HTML
                    // else, we replace the entire table body
                    if (append) {
                        productTable.append(data.html);
                    } else {
                        productTable.html(data.html);
                    }

                    // Update “Showing X–Y of Z total” at the bottom
                    $('[data-total-start]').text(data.showing_start);
                    $('[data-total-end]').text(data.showing_end);
                    $('.pagination-page-total .font-medium:last').text(data.total_products);

                    // Update the progress bar
                    let progressPercentage = (data.showing_end / data.total_products) * 100;
                    $('.pagination-total-item').css('width', progressPercentage + '%');

                    // If has_more is false, hide the “Show More” button
                    if (!data.has_more) {
                        $('.pagination-button').hide();
                    } else {
                        $('.pagination-button').show();
                        $('.show-more-button')
                            .data('page', data.current_page)      // current page
                            .data('total', data.total_products)   // total
                            .data('per-page', perPage);
                    }

                    // If total products <= per_page, no need to show pagination
                    if (data.total_products <= perPage) {
                        paginationWrapper.hide();
                    } else {
                        paginationWrapper.show();
                    }

                    // Re-apply “visible variants” logic after re-render
                    restoreVariantVisibility();
                }
            }
        });
    }

    // Restores the variants that were showing before
    function restoreVariantVisibility() {
        visibleVariants.forEach(function(productId) {
            const rows = $(`.variant-${productId}`);
            if (rows.length > 0) {
                rows.show().addClass('showing');
                $(`button.toggle-variants[data-id="${productId}"]`).text('HIDE VARIANTS');
            }
        });
    }

    // Updates the visibleVariants set by scanning the table
    function updateVisibleVariants() {
        visibleVariants.clear();
        $('.variant-row.showing').each(function() {
            let classes   = $(this).attr('class');
            let match     = classes.match(/variant-(\d+)/);
            if (match) {
                let productId = parseInt(match[1], 10);
                visibleVariants.add(productId);
            }
        });
    }

    /**
     * Search Input Handler (debounced)
     */
    searchInput.on('input', function() {
        clearTimeout(searchTimeout);
        let searchTerm = $(this).val();
        let letter     = $('.alphabet-filter.active').data('letter') || 'all';

        searchTimeout = setTimeout(function() {
            updateProducts(searchTerm, letter, 1, false);
        }, searchDelay);
    });

    // If user clears search
    searchInput.on('search', function() {
        if ($(this).val() === '') {
            let letter = $('.alphabet-filter.active').data('letter') || 'all';
            updateProducts('', letter, 1, false);
        }
    });

    /**
     * Alphabet Filter Handler
     */
    alphabetFilters.on('click', function() {
        alphabetFilters.removeClass('active');
        $(this).addClass('active');

        let letter     = $(this).data('letter') || 'all';
        let searchTerm = searchInput.val();

        updateProducts(searchTerm, letter, 1, false);
    });

    /**
     * Show More Handler
     */
    $('.show-more-button').on('click', function(e) {
        e.preventDefault();
        let nextPage   = parseInt($(this).data('page'), 10) + 1;
        let total      = parseInt($(this).data('total'), 10);
        let searchTerm = $('#product-search').val();
        let letter     = $('.alphabet-filter.active').data('letter') || 'all';

        updateProducts(searchTerm, letter, nextPage, true);
    });

    /**
     * Variant Toggle Handler
     */
    $(document).on('click', '.toggle-variants', function() {
        let productId  = $(this).data('id');
        let variantRows = $(`.variant-${productId}`);

        if (variantRows.is(':visible')) {
            // Hide them
            variantRows.removeClass('showing').slideUp(300);
            $(this).text('SHOW VARIANTS');
            visibleVariants.delete(productId);
        } else {
            // Show them
            variantRows.addClass('showing').slideDown(300);
            $(this).text('HIDE VARIANTS');
            visibleVariants.add(productId);
        }
        updateVisibleVariants();
    });

});
