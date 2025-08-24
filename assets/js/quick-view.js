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

    // show loader and open modal with fade-in
    content.html('<div class="p-6 bg-white rounded">Loading...</div>');
    root.removeClass('hidden');
    // trigger reflow then fade in
    root.addClass('qv-visible');
    setTimeout(function() { root.addClass('flex'); }, 10);

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
                    // move focus to modal title for accessibility
                    $('#qv-title').attr('tabindex', '-1').focus();

                    // Setup variation matching if variations data exists
                    var rootEl = $('#qv-modal-content').find('.qv-quick-view');
                    var variationsData = [];
                    try {
                        var raw = rootEl.data('variations');
                        variationsData = raw || [];
                    } catch (err) {
                        variationsData = [];
                    }

                    if (variationsData && variationsData.length) {
                        // When any select changes, attempt to find matching variation
                        var addBtn = rootEl.find('.qv-add-to-cart');
                        // disable until a valid variation is selected
                        addBtn.prop('disabled', true).addClass('qv-disabled');

                        rootEl.find('.qv-attr-select').on('change', function() {
                            var selects = rootEl.find('.qv-attr-select');
                            var selected = {};
                            selects.each(function() {
                                var k = $(this).data('attr');
                                var v = $(this).val();
                                if (v) selected[k] = v;
                            });

                            var matched = null;
                            variationsData.forEach(function(v) {
                                var ok = true;
                                for (var key in v.attributes) {
                                    // var attribute keys on PHP are like 'attribute_pa_color' or 'attribute_color'
                                    var want = v.attributes[key] || '';
                                    var sel = selected[key] || '';
                                    // Normalise: lowercase
                                    if ((want+'').toLowerCase() !== (sel+'').toLowerCase()) {
                                        ok = false; break;
                                    }
                                }
                                if (ok) matched = v;
                            });

                            if (matched) {
                                rootEl.find('.qv-selected-variation-id').val(matched.variation_id);
                                rootEl.find('.qv-variation-info').removeClass('hidden').text('Price: ' + (matched.display_price || ''));
                                // enable add button and remove invalid highlighting
                                addBtn.prop('disabled', false).removeClass('qv-disabled');
                                selects.removeClass('qv-invalid-select');
                            } else {
                                rootEl.find('.qv-selected-variation-id').val('');
                                rootEl.find('.qv-variation-info').addClass('hidden').text('');
                                // keep disabled
                                addBtn.prop('disabled', true).addClass('qv-disabled');
                            }
                        });
                    }
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
    // smooth close
    var root = $('#qv-modal-root');
    root.removeClass('flex qv-visible');
    setTimeout(function() { root.addClass('hidden'); $('#qv-modal-content').empty(); }, 250);
    });

    // Close when clicking outside the modal content (modal root itself)
    $(document).on('click', '#qv-modal-root', function(e) {
        // If the click target is the root (i.e., not the content), close
        if ( $(e.target).is('#qv-modal-root') || $(e.target).is('#qv-modal-overlay') ) {
            var root = $('#qv-modal-root');
            root.removeClass('flex qv-visible');
            setTimeout(function() { root.addClass('hidden'); $('#qv-modal-content').empty(); }, 250);
        }
    });

    // Prevent clicks inside the quick view content from bubbling to the root
    $(document).on('click', '#qv-modal-content .qv-quick-view', function(e) {
        e.stopPropagation();
    });

    // Delegate add to cart inside quick view
    $(document).on('click', '.qv-add-to-cart', function(e) {
        e.preventDefault();
        var btn = $(this);
        var productId = btn.data('id');
        var originalText = btn.text();
        btn.prop('disabled', true).text('Adding...');

        // If a variation is selected in modal, include it
        var rootModal = $('#qv-modal-content').find('.qv-quick-view');
        var selectedVariationId = rootModal.find('.qv-selected-variation-id').val() || 0;
        var variationData = {};
        rootModal.find('.qv-attr-select').each(function() {
            var key = $(this).data('attr');
            var val = $(this).val();
            if (val) variationData[key] = val;
        });

        // Client-side pre-check: if the product has attribute selects but no matched variation, show inline hint
        var hasSelects = rootModal.find('.qv-attr-select').length > 0;
        if (hasSelects && (!selectedVariationId || selectedVariationId === '')) {
            // highlight empty selects
            rootModal.find('.qv-attr-select').each(function() {
                if (!$(this).val()) $(this).addClass('qv-invalid-select');
                else $(this).removeClass('qv-invalid-select');
            });
            showQvToast('Please choose product options');
            btn.prop('disabled', false).text(originalText);
            return;
        }

        // Resolve ajaxUrl and nonce (prefer wcCart, fallback to others)
        var addAjaxUrl = (typeof wcCart !== 'undefined' && wcCart.ajaxUrl) ? wcCart.ajaxUrl :
            ((typeof wcQuickView !== 'undefined' && wcQuickView.ajaxUrl) ? wcQuickView.ajaxUrl :
                ((typeof wcFilter !== 'undefined' && wcFilter.ajaxUrl) ? wcFilter.ajaxUrl : ''));
        var addAjaxNonce = (typeof wcCart !== 'undefined' && wcCart.nonce) ? wcCart.nonce :
            ((typeof wcQuickView !== 'undefined' && wcQuickView.nonce) ? wcQuickView.nonce :
                ((typeof wcFilter !== 'undefined' && wcFilter.nonce) ? wcFilter.nonce : ''));

        var payload = {
            action: 'add_to_cart',
            product_id: productId,
            quantity: 1,
            variation_id: selectedVariationId,
            variation: variationData,
            nonce: addAjaxNonce
        };
        // debug
        if (window.console && console.log) console.log('QV add_to_cart payload:', payload);

        $.ajax({
            url: addAjaxUrl,
            type: 'POST',
            data: payload,
            success: function(response) {
                if (window.console && console.log) console.log('QV add_to_cart response:', response);
                if (response.success) {
                    // Attempt to update cart UI using the shared renderer if available.
                    // If that doesn't visibly update the cart (timing issues), fetch the cart and re-render.
                    function manualRenderCart(data) {
                        if ($('#cart-count').length) $('#cart-count').text(data.count);
                        if ($('#cart-subtotal').length) $('#cart-subtotal').text(data.subtotal);
                        if ($('#cart-total').length) $('#cart-total').text(data.total);
                        var cartItems = $('#cart-items');
                        if (cartItems.length) {
                            cartItems.empty();
                            data.items.forEach(function(item) {
                                var html = '\n                <div class="flex items-center space-x-4 cart-item" data-key="' + item.key + '">\n                    <img src="' + item.image + '" alt="' + item.name + '" class="w-16 h-16 object-cover">\n                    <div class="flex-1">\n                        <h4 class="font-medium">' + item.name + '</h4>\n                        ' + (item.variation ? '<p class="text-sm text-gray-500">' + item.variation + '</p>' : '') + '\n                        <div class="flex items-center mt-2">\n                            <input type="number" value="' + item.quantity + '" min="1" class="w-16 p-1 border rounded cart-qty-input text-center">\n                            <span class="ml-4">' + item.price + '</span>\n                        </div>\n                    </div>\n                    <button class="remove-item text-red-500 hover:text-red-700">\n                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">\n                            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />\n                        </svg>\n                    </button>\n                </div>\n            ';
                                cartItems.append(html);
                            });
                        }
                    }

                    var calledRenderer = false;
                    if (typeof window.updateCartDisplay === 'function') {
                        try {
                            window.updateCartDisplay(response.data);
                            calledRenderer = true;
                        } catch (err) {
                            console.warn('updateCartDisplay threw', err);
                            calledRenderer = false;
                        }
                    }

                    // Verify the cart DOM reflects the new data. If not, fetch the freshest cart payload and render.
                    setTimeout(function() {
                        var expectedCount = (response.data && response.data.items) ? response.data.items.length : 0;
                        var currentCount = $('#cart-items').children().length;
                        if (currentCount < expectedCount) {
                            // Re-fetch cart from server to ensure we have the latest state
                            $.post(addAjaxUrl, { action: 'get_cart', nonce: addAjaxNonce }, function(resp) {
                                if (resp && resp.success) {
                                    if (typeof window.updateCartDisplay === 'function') {
                                        try {
                                            window.updateCartDisplay(resp.data);
                                            return;
                                        } catch (e) {
                                            console.warn('updateCartDisplay on re-fetch threw', e);
                                        }
                                    }
                                    // Last-resort manual render
                                    manualRenderCart(resp.data);
                                }
                            });
                        }
                    }, 250);
                    // Open slide cart if present
                    if ($('#slide-cart').length) {
                        $('#slide-cart').addClass('active');
                        $('#cart-overlay').addClass('active');
                        $('body').css('overflow', 'hidden');
                    }
                    showQvToast('Added to cart');
                    btn.text('Added');
                    setTimeout(function() {
                        btn.prop('disabled', false).text(originalText);
                    }, 1500);
                } else {
                    var msg = (response.data && response.data.message) ? response.data.message : 'Failed to add to cart';
                    showQvToast(msg);
                    btn.prop('disabled', false).text(originalText);
                }
            },
            error: function(xhr, status, err) {
                console.log('QV add_to_cart error', status, err, xhr);
                showQvToast('Error adding to cart');
                btn.prop('disabled', false).text(originalText);
            }
        });
    });

        // Simple toast inside quick view
        function showQvToast(message) {
            var container = $('#qv-modal-content');
            if (!container.length) return;
            var t = $('<div class="qv-toast fixed top-6 right-6 z-50 bg-black text-white px-4 py-2 rounded shadow">' + message + '</div>');
            container.append(t);
            setTimeout(function() { t.fadeOut(300, function() { t.remove(); }); }, 1800);
        }
});
