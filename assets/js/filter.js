jQuery(document).ready(function($) {
    const productTable = $('#product-table');
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

    // Function to update product table
    function updateProducts(searchTerm = '', letter = 'all') {
        $.ajax({
            url: wcFilter.ajaxUrl,
            type: 'POST',
            data: {
                action: 'search_products',
                nonce: wcFilter.nonce,
                search: searchTerm,
                letter: letter,
                category: $('#product-table').data('category') // Get category if set
            },
            beforeSend: function() {
                $('#product-table tbody').addClass('loading');
            },
            success: function(response) {
                if (response.success) {
                    $('#product-table tbody').html(response.data.html);
                    // Update total count if needed
                    $('.pagination-page-total').find('[data-total-end]').text(response.data.count);
                }
            },
            complete: function() {
                $('#product-table tbody').removeClass('loading');
            }
        });
    }

    // Search input handler
    $('#product-search').on('input', function() {
        clearTimeout(searchTimer);
        const searchTerm = $(this).val();
        
        searchTimer = setTimeout(function() {
            updateProducts(searchTerm, $('.alphabet-filter.active').data('letter'));
        }, searchDelay);
    });

    // Alphabet filter handler
    $('.alphabet-filter').on('click', function(e) {
        e.preventDefault();
        $('.alphabet-filter').removeClass('active');
        $(this).addClass('active');
        
        updateProducts(
            $('#product-search').val(),
            $(this).data('letter')
        );
    });
});
