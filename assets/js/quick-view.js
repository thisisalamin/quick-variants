jQuery(document).ready(function($) {
    // Append modal container to body
    if ($('#qv-modal-root').length === 0) {
        $('body').append('<div id="qv-modal-root" class="qv-modal-root fixed inset-0 z-50 hidden items-center justify-center p-4"><div id="qv-modal-overlay" class="absolute inset-0 bg-black/50"></div><div id="qv-modal-content" class="relative z-10 max-w-4xl w-full"></div></div>');
    }

    // Delegate click on quick view icons
    $(document).on('click', '.qv-quick-view-btn', function(e) {
        e.preventDefault();
        var productId = $(this).data('id');
        var root = $('#qv-modal-root');
        var content = $('#qv-modal-content');

        // show loader
        content.html('<div class="p-6 bg-white rounded">Loading...</div>');
        root.removeClass('hidden').addClass('flex');

        // Resolve ajaxUrl and nonce from localized objects (prefer wcQuickView)
        var ajaxUrl = (typeof wcQuickView !== 'undefined' && wcQuickView.ajaxUrl) ? wcQuickView.ajaxUrl :
            ((typeof wcFilter !== 'undefined' && wcFilter.ajaxUrl) ? wcFilter.ajaxUrl :
                ((typeof wcPagination !== 'undefined' && wcPagination.ajaxUrl) ? wcPagination.ajaxUrl : (typeof wcCart !== 'undefined' ? wcCart.ajaxUrl : '')));
        var ajaxNonce = (typeof wcQuickView !== 'undefined' && wcQuickView.nonce) ? wcQuickView.nonce :
            ((typeof wcFilter !== 'undefined' && wcFilter.nonce) ? wcFilter.nonce :
                ((typeof wcPagination !== 'undefined' && wcPagination.nonce) ? wcPagination.nonce : (typeof wcCart !== 'undefined' ? wcCart.nonce : '')));

        $.ajax({
            url: ajaxUrl,
            type: 'POST',
            data: {
                action: 'quick_view_product',
                product_id: productId,
                nonce: ajaxNonce
            },
            success: function(response) {
                if (response.success) {
                    content.html(response.data.html);
                } else {
                    content.html('<div class="p-6 bg-white rounded">' + (response.data && response.data.message ? response.data.message : 'Failed to load quick view') + '</div>');
                }
            },
            error: function() {
                content.html('<div class="p-6 bg-white rounded">Error loading quick view</div>');
            }
        });
    });

    // Close modal on overlay or close button
    $(document).on('click', '#qv-modal-overlay, .qv-close', function() {
        $('#qv-modal-root').removeClass('flex').addClass('hidden');
    });

    // Delegate add to cart inside quick view
    $(document).on('click', '.qv-add-to-cart', function(e) {
        e.preventDefault();
        var btn = $(this);
        var productId = btn.data('id');
        var originalText = btn.text();
        btn.prop('disabled', true).text('Adding...');

        $.ajax({
            url: wcCart.ajaxUrl,
            type: 'POST',
            data: {
                action: 'add_to_cart',
                product_id: productId,
                quantity: 1,
                nonce: wcCart.nonce
            },
            success: function(response) {
                if (response.success) {
                    // update cart UI if function exists
                    if (typeof updateCartDisplay === 'function') {
                        updateCartDisplay(response.data);
                    }
                    btn.text('Added');
                    setTimeout(function() {
                        btn.prop('disabled', false).text(originalText);
                    }, 1500);
                } else {
                    alert(response.data && response.data.message ? response.data.message : 'Failed to add to cart');
                    btn.prop('disabled', false).text(originalText);
                }
            },
            error: function() {
                alert('Error adding to cart');
                btn.prop('disabled', false).text(originalText);
            }
        });
    });
});
