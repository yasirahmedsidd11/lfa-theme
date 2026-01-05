(function($) {
    'use strict';

    // Force remove button color with inline styles (highest specificity)
    function applyRemoveButtonStyles() {
        $('.lfa-remove-button, .lfa-cart-item-remove a').each(function() {
            $(this).css({
                'color': '#000',
                'text-decoration': 'underline',
                'text-underline-offset': '3px'
            });
        });
    }

    $(document).ready(function() {
        // Apply styles immediately
        applyRemoveButtonStyles();
        
        // Re-apply after cart updates (when fragments are refreshed)
        $(document.body).on('wc_fragment_refresh updated_wc_div', function() {
            setTimeout(applyRemoveButtonStyles, 100);
        });
        // Handle quantity increase
        $(document).on('click', '.lfa-quantity-plus', function(e) {
            e.preventDefault();
            var $button = $(this);
            var $input = $button.siblings('.lfa-quantity-input');
            var cartItemKey = $input.data('cart-item-key');
            var currentQty = parseInt($input.val()) || 0;
            var maxQty = parseInt($button.data('max')) || 9999;
            var newQty = currentQty + 1;

            if (newQty > maxQty) {
                return;
            }

            updateCartQuantity(cartItemKey, newQty, $input, $button);
        });

        // Handle quantity decrease
        $(document).on('click', '.lfa-quantity-minus', function(e) {
            e.preventDefault();
            var $button = $(this);
            var $input = $button.siblings('.lfa-quantity-input');
            var cartItemKey = $input.data('cart-item-key');
            var currentQty = parseInt($input.val()) || 0;
            var minQty = parseInt($button.data('min')) || 0;
            var newQty = currentQty - 1;

            if (newQty < minQty) {
                return;
            }

            updateCartQuantity(cartItemKey, newQty, $input, $button);
        });

        function updateCartQuantity(cartItemKey, quantity, $input, $button) {
            // Disable buttons during update
            var $allButtons = $('.lfa-quantity-plus, .lfa-quantity-minus');
            var $allInputs = $('.lfa-quantity-input');
            $allButtons.prop('disabled', true);
            $allInputs.prop('disabled', true);

            // Update input value immediately for better UX
            $input.val(quantity);

            // Get the cart form
            var $form = $('.woocommerce-cart-form');
            
            // Build form data manually
            var formData = {};
            formData['cart[' + cartItemKey + '][qty]'] = quantity;
            formData['update_cart'] = 'Update cart';
            formData['woocommerce-cart-nonce'] = $('input[name="woocommerce-cart-nonce"]').val();

            // Get all other cart items to preserve their quantities
            $('.lfa-quantity-input').each(function() {
                var key = $(this).data('cart-item-key');
                if (key !== cartItemKey) {
                    formData['cart[' + key + '][qty]'] = $(this).val();
                }
            });

            // Use WooCommerce AJAX endpoint
            var ajaxUrl = '';
            if (typeof wc_cart_params !== 'undefined' && wc_cart_params.wc_ajax_url) {
                ajaxUrl = wc_cart_params.wc_ajax_url.toString().replace('%%endpoint%%', 'update_cart');
            } else if (typeof wc_add_to_cart_params !== 'undefined' && wc_add_to_cart_params.wc_ajax_url) {
                ajaxUrl = wc_add_to_cart_params.wc_ajax_url.toString().replace('%%endpoint%%', 'update_cart');
            } else {
                // Fallback to form action
                ajaxUrl = $form.attr('action') || window.location.href;
            }

            $.ajax({
                url: ajaxUrl,
                type: 'POST',
                data: formData,
                dataType: 'json',
                success: function(response) {
                    if (response && response.fragments) {
                        // Update cart fragments
                        $.each(response.fragments, function(key, value) {
                            $(key).replaceWith(value);
                        });

                        // Update cart count in header if exists
                        if (response.cart_hash) {
                            $(document.body).trigger('wc_fragment_refresh');
                        }

                        // Re-apply remove button styles after fragment update
                        setTimeout(applyRemoveButtonStyles, 100);

                        // Re-enable buttons
                        $allButtons.prop('disabled', false);
                        $allInputs.prop('disabled', false);
                    } else {
                        // Fallback: reload page if AJAX fails
                        location.reload();
                    }
                },
                error: function() {
                    // On error, reload page
                    location.reload();
                }
            });
        }
    });
})(jQuery);

