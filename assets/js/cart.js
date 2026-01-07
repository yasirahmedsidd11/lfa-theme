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

    // Add custom classes to WooCommerce notices
    function addNoticeClasses() {
        $('.woocommerce-message, .woocommerce-error, .woocommerce-info').each(function() {
            if (!$(this).hasClass('lfa-woocommerce-notice')) {
                $(this).addClass('lfa-woocommerce-notice');
                var noticeType = '';
                if ($(this).hasClass('woocommerce-message')) {
                    noticeType = 'success';
                } else if ($(this).hasClass('woocommerce-error')) {
                    noticeType = 'error';
                } else if ($(this).hasClass('woocommerce-info')) {
                    noticeType = 'info';
                }
                if (noticeType) {
                    $(this).addClass('lfa-notice-' + noticeType);
                }
            }
        });
    }

    // Shipping accordion toggle (only in drawer)
    function initShippingAccordion() {
        $('.lfa-cart-drawer .lfa-shipping-accordion-toggle').off('click').on('click', function(e) {
            e.preventDefault();
            var $toggle = $(this);
            var $content = $toggle.next('.lfa-shipping-accordion-content');
            var isExpanded = $toggle.attr('aria-expanded') === 'true';
            
            $toggle.attr('aria-expanded', !isExpanded);
        });
    }

    $(document).ready(function() {
        // Apply styles immediately
        applyRemoveButtonStyles();
        addNoticeClasses();
        initShippingAccordion();
        
        // Re-apply after cart updates (when fragments are refreshed)
        $(document.body).on('wc_fragment_refresh updated_wc_div', function() {
            setTimeout(applyRemoveButtonStyles, 100);
            setTimeout(addNoticeClasses, 100);
            setTimeout(initShippingAccordion, 100);
            setTimeout(initQuantityButtonStates, 100);
        });
        
        // Also add classes when new notices appear
        var noticeObserver = new MutationObserver(function(mutations) {
            addNoticeClasses();
        });
        
        // Observe the document body for new notices
        if ($('body').length) {
            noticeObserver.observe(document.body, {
                childList: true,
                subtree: true
            });
        }
        // Function to update button states based on quantity
        function updateQuantityButtonStates($input) {
            var currentQty = parseInt($input.val()) || 0;
            var minQty = parseInt($input.attr('min')) || 0;
            var maxQty = parseInt($input.attr('max')) || 9999;
            
            // Find buttons - they might be siblings or in a wrapper
            var $quantityControl = $input.closest('.lfa-quantity-control');
            var $plusBtn = $quantityControl.find('.lfa-quantity-plus');
            var $minusBtn = $quantityControl.find('.lfa-quantity-minus');
            
            // If not found, try siblings
            if (!$plusBtn.length) {
                $plusBtn = $input.siblings('.lfa-quantity-plus');
            }
            if (!$minusBtn.length) {
                $minusBtn = $input.siblings('.lfa-quantity-minus');
            }
            
            // Remove any existing max quantity message
            $quantityControl.find('.lfa-max-quantity-message').remove();
            
            // Update plus button
            if (currentQty >= maxQty) {
                $plusBtn.prop('disabled', true).addClass('disabled');
                // Show message
                if (!$quantityControl.find('.lfa-max-quantity-message').length) {
                    $quantityControl.append('<span class="lfa-max-quantity-message">Max quantity reached</span>');
                }
            } else {
                $plusBtn.prop('disabled', false).removeClass('disabled');
            }
            
            // Update minus button
            if (currentQty <= minQty) {
                $minusBtn.prop('disabled', true).addClass('disabled');
            } else {
                $minusBtn.prop('disabled', false).removeClass('disabled');
            }
        }
        
        // Initialize button states on page load
        function initQuantityButtonStates() {
            $('.lfa-quantity-input').each(function() {
                updateQuantityButtonStates($(this));
            });
        }
        
        // Handle quantity increase
        $(document).on('click', '.lfa-quantity-plus', function(e) {
            e.preventDefault();
            e.stopPropagation();
            var $button = $(this);
            
            // Don't proceed if button is disabled
            if ($button.prop('disabled')) {
                return;
            }
            
            // Find input - might be sibling or in same container
            var $quantityControl = $button.closest('.lfa-quantity-control');
            var $input = $quantityControl.find('.lfa-quantity-input');
            if (!$input.length) {
                $input = $button.siblings('.lfa-quantity-input');
            }
            
            // Get cart item key from input or button
            var cartItemKey = $input.data('cart-item-key') || $button.data('cart-item-key');
            
            if (!cartItemKey || !$input.length) {
                return;
            }
            
            var currentQty = parseInt($input.val()) || 0;
            var maxQty = parseInt($button.data('max')) || parseInt($input.attr('max')) || 9999;
            var newQty = currentQty + 1;

            if (newQty > maxQty) {
                updateQuantityButtonStates($input);
                return;
            }

            updateCartQuantity(cartItemKey, newQty, $input, $button);
        });

        // Handle quantity decrease
        $(document).on('click', '.lfa-quantity-minus', function(e) {
            e.preventDefault();
            e.stopPropagation();
            var $button = $(this);
            
            // Don't proceed if button is disabled
            if ($button.prop('disabled')) {
                return;
            }
            
            // Find input - might be sibling or in same container
            var $quantityControl = $button.closest('.lfa-quantity-control');
            var $input = $quantityControl.find('.lfa-quantity-input');
            if (!$input.length) {
                $input = $button.siblings('.lfa-quantity-input');
            }
            
            var cartItemKey = $input.data('cart-item-key');
            var currentQty = parseInt($input.val()) || 0;
            var minQty = parseInt($button.data('min')) || parseInt($input.attr('min')) || 0;
            var newQty = currentQty - 1;

            if (newQty < minQty) {
                updateQuantityButtonStates($input);
                return;
            }

            updateCartQuantity(cartItemKey, newQty, $input, $button);
        });

        function updateCartQuantity(cartItemKey, quantity, $input, $button) {
            // Disable buttons during update
            $('.lfa-quantity-plus, .lfa-quantity-minus').prop('disabled', true);
            $('.lfa-quantity-input').prop('disabled', true);
            
            // Update button states immediately
            updateQuantityButtonStates($input);

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
                    // Check if we're in the cart drawer
                    var $drawer = $('[data-cart-drawer]');
                    var isInDrawer = $drawer.length && $drawer.hasClass('is-open');
                    
                    if (response && response.fragments) {
                        // Update cart fragments
                        $.each(response.fragments, function(key, value) {
                            $(key).replaceWith(value);
                        });

                        // Always try to update badge with fragments first
                        if (typeof window.updateCartBadge === 'function') {
                            window.updateCartBadge(response.fragments);
                            
                            // Always fetch fresh fragments as fallback to ensure badge is updated
                            // The update_cart endpoint might not include the badge fragment
                            setTimeout(function() {
                                if (typeof wc_add_to_cart_params !== 'undefined') {
                                    $.get(wc_add_to_cart_params.wc_ajax_url.toString().replace('%%endpoint%%', 'get_refreshed_fragments'), function(freshResponse) {
                                        if (freshResponse && freshResponse.fragments) {
                                            window.updateCartBadge(freshResponse.fragments);
                                        }
                                    });
                                }
                            }, 150);
                        }
                        
                        if (response.cart_hash) {
                            $(document.body).trigger('wc_fragment_refresh');
                        }

                        // If in cart drawer, reload drawer content to show updated quantities
                        if (isInDrawer) {
                            // Always reload drawer content when quantity changes
                            if (typeof window.loadCartContent === 'function') {
                                // Small delay to ensure fragments are updated first
                                setTimeout(function() {
                                    window.loadCartContent();
                                    
                                    // After drawer content reloads, update the header badge
                                    // Fetch fresh fragments to get the updated count
                                    setTimeout(function() {
                                        if (typeof window.updateCartBadge === 'function') {
                                            window.updateCartBadge();
                                        }
                                    }, 300);
                                }, 200);
                            } else {
                                // Fallback: trigger fragment refresh which should reload drawer
                                $(document.body).trigger('wc_fragment_refresh');
                            }
                        }

                        // Re-apply remove button styles after fragment update
                        setTimeout(applyRemoveButtonStyles, 100);
                        setTimeout(addNoticeClasses, 100);
                        setTimeout(initShippingAccordion, 100);
                        
                        // Re-enable inputs and update button states
                        setTimeout(function() {
                            $('.lfa-quantity-input').prop('disabled', false);
                            initQuantityButtonStates();
                        }, 150);

                        // Re-enable buttons and update states using fresh selectors (after fragments are updated)
                        setTimeout(function() {
                            $('.lfa-quantity-input').prop('disabled', false);
                            // Update button states based on current quantities
                            initQuantityButtonStates();
                        }, 100);
                    } else {
                        // If in drawer and no fragments, reload drawer content
                        if (isInDrawer && typeof window.loadCartContent === 'function') {
                            window.loadCartContent();
                            setTimeout(function() {
                                $('.lfa-quantity-plus, .lfa-quantity-minus').prop('disabled', false);
                                $('.lfa-quantity-input').prop('disabled', false);
                            }, 100);
                        } else {
                            // Fallback: reload page if AJAX fails
                            location.reload();
                        }
                    }
                },
                error: function(xhr, status, error) {
                    // If in drawer, try to reload drawer content
                    var $drawer = $('[data-cart-drawer]');
                    var isInDrawer = $drawer.length && $drawer.hasClass('is-open');
                    if (isInDrawer && typeof window.loadCartContent === 'function') {
                        window.loadCartContent();
                        setTimeout(function() {
                            $('.lfa-quantity-plus, .lfa-quantity-minus').prop('disabled', false);
                            $('.lfa-quantity-input').prop('disabled', false);
                        }, 100);
                    } else {
                        // On error, reload page
                        location.reload();
                    }
                }
            });
        }

        // Handle coupon removal via AJAX - use the original link URL which has correct nonce
        $(document).on('click', 'a.woocommerce-remove-coupon', function(e) {
            e.preventDefault();
            e.stopImmediatePropagation(); // Prevent WooCommerce's default handler
            
            var $link = $(this);
            var href = $link.attr('href');
            
            if (!href) {
                return false;
            }
            
            // Disable link during update
            $link.css('pointer-events', 'none').css('opacity', '0.5');
            
            // Parse the original URL to get all parameters
            try {
                var url = new URL(href, window.location.origin);
                var couponCode = url.searchParams.get('remove_coupon') || url.searchParams.get('coupon') || '';
                var nonce = url.searchParams.get('_wpnonce') || url.searchParams.get('security') || '';
                
                // Use admin-ajax.php directly for more control over nonce verification
                var ajaxUrl = '';
                if (typeof ajaxurl !== 'undefined') {
                    ajaxUrl = ajaxurl;
                } else {
                    // Construct admin-ajax.php URL manually
                    var baseUrl = window.location.origin + window.location.pathname;
                    if (baseUrl.endsWith('/')) {
                        baseUrl = baseUrl.slice(0, -1);
                    }
                    var pathParts = baseUrl.split('/');
                    pathParts.pop(); // Remove last part
                    ajaxUrl = pathParts.join('/') + '/wp-admin/admin-ajax.php';
                }
                
                // Prepare request data - use coupon code from URL or data attribute
                var requestData = {
                    action: 'lfa_remove_coupon',
                    coupon: couponCode,
                    security: nonce,
                    'woocommerce-cart-nonce': nonce
                };
                
                // Use custom AJAX handler to remove coupon
                $.ajax({
                    type: 'POST',
                    url: ajaxUrl,
                    data: requestData,
                    success: function(response) {
                        // Reload page on success
                        if (response && response.success === true) {
                            window.location.reload();
                        } else {
                            // Error response
                            var errorMessage = 'Failed to remove coupon.';
                            if (response && response.data && response.data.message) {
                                errorMessage = response.data.message;
                            }
                            alert(errorMessage);
                            $link.css('pointer-events', '').css('opacity', '');
                        }
                    },
                    error: function(xhr, status, error) {
                        // On error, reload the page
                        window.location.reload();
                    },
                    complete: function() {
                        // Only reset link state if not reloading
                        setTimeout(function() {
                            if (document.readyState === 'complete') {
                                $link.css('pointer-events', '').css('opacity', '');
                            }
                        }, 100);
                    }
                });
            } catch (err) {
                // Fallback: just follow the link
                window.location.href = href;
            }
            
            return false;
        });

        // Handle remove item via AJAX
        $(document).on('click', '.lfa-remove-button, .lfa-cart-item-remove a.remove', function(e) {
            e.preventDefault();
            var $link = $(this);
            var href = $link.attr('href');
            var $cartItem = $link.closest('.lfa-cart-item');
            
            if (!href) return;
            
            // Disable link during update
            $link.css('pointer-events', 'none').css('opacity', '0.5');
            
            // Check if we're in the cart drawer
            var $drawer = $('[data-cart-drawer]');
            var isInDrawer = $drawer.length && $drawer.hasClass('is-open');
            
            // Use WooCommerce AJAX to remove item
            $.get(href, function() {
                // Trigger WooCommerce fragment refresh
                $(document.body).trigger('wc_fragment_refresh');
                
                // Update cart badge - fetch fragments to get updated count
                if (typeof window.updateCartBadge === 'function') {
                    // Fetch fresh fragments after removal
                    if (typeof wc_add_to_cart_params !== 'undefined') {
                        $.get(wc_add_to_cart_params.wc_ajax_url.toString().replace('%%endpoint%%', 'get_refreshed_fragments'), function(response) {
                            if (response && response.fragments) {
                                // Update badge with fragments
                                window.updateCartBadge(response.fragments);
                            } else {
                                // Fallback
                                window.updateCartBadge();
                            }
                        });
                    } else {
                        // Fallback
                        setTimeout(function() {
                            window.updateCartBadge();
                        }, 100);
                    }
                }
                
                // If in cart drawer, reload drawer content
                if (isInDrawer && typeof window.loadCartContent === 'function') {
                    window.loadCartContent();
                } else {
                    // If on cart page, reload fragments
                    if (typeof wc_add_to_cart_params !== 'undefined') {
                        $.get(wc_add_to_cart_params.wc_ajax_url.toString().replace('%%endpoint%%', 'get_refreshed_fragments'), function(response) {
                            if (response && response.fragments) {
                                $.each(response.fragments, function(key, value) {
                                    $(key).replaceWith(value);
                                });
                                setTimeout(applyRemoveButtonStyles, 100);
                                setTimeout(initShippingAccordion, 100);
                            }
                        });
                    }
                }
            }).fail(function() {
                // On error, reload page
                location.reload();
            });
        });
    });
})(jQuery);

