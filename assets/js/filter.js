jQuery(document).ready(function($) {
    const productTable = $('#product-table');
    const searchInput = $('#product-search');
    const filterButton = $('#filter-button');
    const filterDropdown = $('#filter-dropdown');
    const alphabetFilters = $('.alphabet-filter');
    const currentFilter = $('#current-filter');
    let currentLetter = 'all';

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

    // Search functionality
    searchInput.on('input', function() {
        filterProducts();
    });

    // Alphabet filter functionality
    alphabetFilters.on('click', function() {
        const letter = $(this).data('letter');
        alphabetFilters.removeClass('active');
        $(this).addClass('active');
        currentLetter = letter;
        currentFilter.text(letter === 'all' ? 'All' : letter);
        filterDropdown.removeClass('show');
        filterProducts();
    });

    // Filter products function
    function filterProducts() {
        const searchTerm = searchInput.val().toLowerCase();
        const productRows = productTable.find('tbody > tr:not(.variant-row)');

        productRows.each(function() {
            const row = $(this);
            const productName = row.find('td:nth-child(2)').text().toLowerCase();
            const variantRows = row.nextUntil('tr:not(.variant-row)');
            
            let showRow = true;

            // Search filter
            if (searchTerm && !productName.includes(searchTerm)) {
                showRow = false;
            }

            // Alphabet filter
            if (currentLetter !== 'all') {
                if (!productName.startsWith(currentLetter.toLowerCase())) {
                    showRow = false;
                }
            }

            // Show/hide the product row and its variants
            row.toggle(showRow);
            variantRows.hide();
            
            if (showRow && row.hasClass('expanded')) {
                variantRows.show();
            }
        });
    }
});
