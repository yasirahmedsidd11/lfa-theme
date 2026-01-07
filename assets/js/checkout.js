/**
 * Checkout Page JavaScript
 */
(function ($) {
    'use strict';

    // Immediately prevent shipping calculator form submission on checkout (runs before DOM ready)
    // Completely disable form submission by overriding submit method
    if (document.body && document.body.classList.contains('woocommerce-checkout')) {
        (function () {
            var forms = document.querySelectorAll('.woocommerce-shipping-calculator[data-lfa-checkout-shipping-calc="true"]');
            for (var i = 0; i < forms.length; i++) {
                var form = forms[i];

                // Completely remove form submission capability
                form.setAttribute('action', '#');
                form.setAttribute('method', 'get');
                form.setAttribute('onsubmit', 'return false;');
                form.setAttribute('novalidate', 'novalidate');

                // Override form's submit method completely
                var originalSubmit = form.submit;
                form.submit = function () {
                    return false;
                };

                // Add native event listener in capture phase to prevent ALL submissions
                form.addEventListener('submit', function (e) {
                    e.preventDefault();
                    e.stopPropagation();
                    e.stopImmediatePropagation();
                    return false;
                }, true); // Capture phase - runs before any other handlers

                // Also prevent any button from submitting by changing type
                var buttons = form.querySelectorAll('button, input[type="submit"]');
                for (var j = 0; j < buttons.length; j++) {
                    if (buttons[j].type === 'submit' || buttons[j].getAttribute('name') === 'calc_shipping') {
                        buttons[j].setAttribute('type', 'button');
                        buttons[j].setAttribute('onclick', 'return false;');
                    }
                }
            }
        })();
    }

    $(document).ready(function () {
        // Initialize accordions - order review open by default
        $('.lfa-checkout-order-review').addClass('lfa-accordion-open');
        $('.lfa-order-review-content').show();
        $('.lfa-order-review-title').addClass('lfa-accordion-open');

        // Initialize payment methods - open by default
        $('.lfa-checkout-payment').addClass('lfa-accordion-open');
        $('.lfa-payment-methods-content').show();
        $('.lfa-payment-title').addClass('lfa-accordion-open');

        // Prevent shipping calculator form from submitting on checkout page - set form action to #
        if ($('body').hasClass('woocommerce-checkout')) {
            $('.woocommerce-shipping-calculator[data-lfa-checkout-shipping-calc="true"]')
                .attr('action', 'javascript:void(0);')
                .attr('onsubmit', 'return false;')
                .attr('novalidate', 'novalidate');
        }

        // Toggle shipping address visibility
        $('#ship-to-different-address-checkbox, input[name="ship_to_different_address"]').on('change', function () {
            if ($(this).is(':checked')) {
                $('.shipping_address').slideDown(300);
            } else {
                $('.shipping_address').slideUp(300);
            }
        });

        // Initialize shipping address visibility on page load
        if ($('#ship-to-different-address-checkbox').is(':checked') || $('input[name="ship_to_different_address"]').is(':checked')) {
            $('.shipping_address').show();
        } else {
            $('.shipping_address').hide();
        }

        // Function to apply coupon
        function applyCoupon($form) {
            var $input = $form.find('#coupon_code');
            var couponCode = $input.length ? ($input.val() || '').toString().trim() : '';
            var $button = $form.find('.lfa-apply-coupon-btn');

            if (!couponCode) {
                return false;
            }

            $button.prop('disabled', true).text('Applying...');

            // Use WooCommerce AJAX endpoint - always use wc-ajax format
            var ajaxUrl = '';
            var baseUrl = window.location.origin + window.location.pathname;
            if (baseUrl.endsWith('/')) {
                baseUrl = baseUrl.slice(0, -1);
            }

            // Use admin-ajax.php directly for more control over nonce verification
            if (typeof ajaxurl !== 'undefined') {
                ajaxUrl = ajaxurl;
            } else {
                // Construct admin-ajax.php URL manually
                var pathParts = baseUrl.split('/');
                pathParts.pop(); // Remove last part
                ajaxUrl = pathParts.join('/') + '/wp-admin/admin-ajax.php';
            }

            if (ajaxUrl) {
                // Get nonce - WooCommerce uses 'woocommerce-cart' nonce for cart operations including coupons
                var nonce = '';
                // First try to get from the coupon form itself
                nonce = $form.find('input[name="woocommerce-cart-nonce"]').val() || '';
                if (!nonce) {
                    // Try to get from main cart form or checkout form
                    nonce = $('input[name="woocommerce-cart-nonce"]').val() ||
                        $('input[name="woocommerce-process-checkout-nonce"]').val() || '';
                }

                // Send action and nonce
                var requestData = {
                    action: 'lfa_apply_coupon',
                    security: nonce,
                    coupon_code: couponCode,
                    // Also include the nonce field name that might be checked
                    'woocommerce-cart-nonce': nonce
                };

                $.ajax({
                    type: 'POST',
                    url: ajaxUrl,
                    data: requestData,
                    success: function (response, textStatus, xhr) {
                        // Check HTTP status code - 500 means server error
                        if (xhr.status >= 400) {
                            alert('An error occurred while applying the coupon. Please try again.');
                            return;
                        }

                        // Check if response contains error indicators even with 200 status
                        var responseStr = String(response);
                        if (responseStr.indexOf('500') !== -1 || responseStr.indexOf('Internal Server Error') !== -1 || responseStr.indexOf('Fatal error') !== -1 || responseStr.indexOf('Parse error') !== -1) {
                            alert('An error occurred while applying the coupon. Please try again.');
                            return;
                        }

                        // Check if response is a string (might be HTML or error message)
                        if (typeof response === 'string') {
                            // Parse HTML response to extract error messages
                            var $response = $('<div>').html(response);
                            var $errorNotice = $response.find('.woocommerce-error, .woocommerce-info, .woocommerce-message');

                            if ($errorNotice.length > 0) {
                                // Extract error message text
                                var errorText = $errorNotice.find('li').first().text().trim() || $errorNotice.text().trim();

                                // Check if it says "already applied"
                                if (errorText.toLowerCase().indexOf('already applied') !== -1) {
                                    // Check if coupon is actually in the cart
                                    var appliedCoupons = [];
                                    $('.lfa-applied-coupon-code').each(function () {
                                        appliedCoupons.push($(this).text().trim().toUpperCase());
                                    });
                                    var couponCodeUpper = couponCode.toUpperCase();

                                    if (appliedCoupons.indexOf(couponCodeUpper) === -1) {
                                        // Coupon not in UI, but WooCommerce says it's applied - reload page to sync
                                        window.location.reload();
                                    } else {
                                        // Coupon is already in UI
                                        // Don't show alert, just clear the input
                                        $form.find('#coupon_code').val('');
                                    }
                                } else {
                                    // Other error message
                                    alert(errorText || 'Invalid coupon code');
                                }
                                return;
                            }

                            // Check for common error indicators
                            if (response === '0' || response === '-1' || response.toLowerCase().indexOf('error') !== -1 || response.toLowerCase().indexOf('invalid') !== -1) {
                                alert('Invalid coupon code');
                                return;
                            } else {
                                // String response but no error indicators - assume success
                                window.location.reload();
                                return;
                            }
                        }

                        // Handle JSON response
                        if (response && typeof response === 'object') {
                            if (response.success === true) {
                                window.location.reload();
                            } else if (response.success === false) {
                                // Check for error messages
                                var errorMessage = 'Invalid coupon code';
                                if (response.data) {
                                    if (typeof response.data === 'string') {
                                        errorMessage = response.data;
                                    } else if (response.data.message) {
                                        errorMessage = response.data.message;
                                    } else if (response.data.notice) {
                                        errorMessage = response.data.notice;
                                    }
                                } else if (response.message) {
                                    errorMessage = response.message;
                                }
                                alert(errorMessage);
                            } else {
                                // No success property - check if it's an HTML fragment or other format
                                // If response has fragments or other WooCommerce data, assume success
                                if (response.fragments || response.cart_hash) {
                                    window.location.reload();
                                } else {
                                    // Unknown format - reload page anyway
                                    window.location.reload();
                                }
                            }
                        } else if (response === null || response === undefined || response === '') {
                            // Sometimes WooCommerce returns empty/null but still succeeds
                            window.location.reload();
                        } else {
                            // If response format is unexpected, reload page anyway
                            window.location.reload();
                        }
                    },
                    error: function (xhr, status, error) {
                        // Don't show success message on error
                        $button.prop('disabled', false).text('Apply');

                        // Handle 500 Internal Server Error specifically
                        if (xhr.status === 500) {
                            alert('A server error occurred. Please try again or contact support if the problem persists.');
                            return;
                        }

                        // Try to parse response as JSON even on error
                        try {
                            var errorResponse = JSON.parse(xhr.responseText);
                            if (errorResponse && errorResponse.data && errorResponse.data.message) {
                                alert(errorResponse.data.message);
                            } else if (errorResponse && errorResponse.message) {
                                alert(errorResponse.message);
                            } else {
                                alert('An error occurred while applying the coupon. Please try again.');
                            }
                        } catch (e) {
                            alert('An error occurred while applying the coupon. Please try again.');
                        }
                    },
                    complete: function () {
                        $button.prop('disabled', false).text('Apply');
                    }
                });
            } else {
                // Fallback: trigger WooCommerce coupon application
                $('body').trigger('update_checkout');
                $button.prop('disabled', false).text('Apply');
            }

            return false;
        }

        // Handle coupon form button click - use event delegation
        $(document).on('click', '.lfa-apply-coupon-btn', function (e) {
            e.preventDefault();
            e.stopPropagation();

            var $button = $(this);

            // Try multiple ways to find the form
            var $form = $button.closest('form.checkout_coupon');
            if ($form.length === 0) {
                $form = $button.closest('.checkout_coupon');
            }
            if ($form.length === 0) {
                $form = $button.parents('form').first();
            }
            if ($form.length === 0) {
                // Fallback: find form by ID or class in the same container
                var $container = $button.closest('.lfa-checkout-coupon');
                $form = $container.find('form.checkout_coupon');
            }

            if ($form.length === 0) {
                alert('Error: Could not find coupon form. Please refresh the page.');
                return false;
            }

            applyCoupon($form);
        });

        // Handle Enter key press in coupon input field
        $(document).on('keypress', '#coupon_code', function (e) {
            if (e.which === 13 || e.keyCode === 13) {
                e.preventDefault();
                e.stopPropagation();

                var $input = $(this);
                var $form = $input.closest('form.checkout_coupon');
                if ($form.length === 0) {
                    $form = $input.closest('.checkout_coupon');
                }
                if ($form.length === 0) {
                    $form = $input.parents('form').first();
                }

                if ($form.length === 0) {
                    return false;
                }

                applyCoupon($form);
                return false;
            }
        });

        // Prevent coupon form from submitting checkout form
        $(document).on('submit', 'form.checkout_coupon, .checkout_coupon', function (e) {
            e.preventDefault();
            e.stopPropagation();

            var $form = $(this);
            // If the submit was on a container, find the actual form
            if (!$form.is('form')) {
                $form = $form.find('form').first();
            }

            if ($form.length === 0 || !$form.is('form')) {
                return false;
            }

            applyCoupon($form);

            return false;
        });

        // Toggle order review section accordion
        $(document).on('click', '.lfa-order-review-title, .lfa-order-review-title .lfa-order-review-chevron', function (e) {
            e.preventDefault();
            e.stopPropagation();
            var $content = $('.lfa-order-review-content');
            var $parent = $('.lfa-checkout-order-review');
            var $title = $('.lfa-order-review-title');

            if ($parent.hasClass('lfa-accordion-open')) {
                $content.slideUp(300);
                $parent.removeClass('lfa-accordion-open');
                $title.removeClass('lfa-accordion-open');
            } else {
                $content.slideDown(300);
                $parent.addClass('lfa-accordion-open');
                $title.addClass('lfa-accordion-open');
            }
        });

        // Toggle payment methods section accordion
        $(document).on('click', '.lfa-payment-title, .lfa-payment-title .lfa-payment-chevron', function (e) {
            e.preventDefault();
            e.stopPropagation();
            var $content = $('.lfa-payment-methods-content');
            var $parent = $('.lfa-checkout-payment');
            var $title = $('.lfa-payment-title');

            if ($parent.hasClass('lfa-accordion-open')) {
                $content.slideUp(300);
                $parent.removeClass('lfa-accordion-open');
                $title.removeClass('lfa-accordion-open');
            } else {
                $content.slideDown(300);
                $parent.addClass('lfa-accordion-open');
                $title.addClass('lfa-accordion-open');
            }
        });

        // Handle shipping calculator update button on checkout (only on checkout page)
        // Use capture phase to intercept before any other handlers
        $(document).on('click', '.lfa-shipping-update-btn', function (e) {
            // Only handle on checkout page
            if (!$('body').hasClass('woocommerce-checkout')) {
                return true; // Let default behavior happen on cart page
            }

            // Aggressively prevent default behavior
            e.preventDefault();
            e.stopPropagation();
            e.stopImmediatePropagation();
            e.stopPropagation ? e.stopPropagation() : (e.cancelBubble = true);

            // Also prevent form submission
            var $form = $(this).closest('form');
            if ($form.length) {
                $form.off('submit').on('submit', function (ev) {
                    ev.preventDefault();
                    ev.stopPropagation();
                    ev.stopImmediatePropagation();
                    return false;
                });
            }

            var $button = $(this);
            var $form = $button.closest('.woocommerce-shipping-calculator');

            $button.prop('disabled', true).text('Updating...');

            // Get form data
            var formData = $form.serialize();
            formData += '&calc_shipping=1';

            // Use our custom AJAX handler to avoid WooCommerce redirects
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

            // Add action to form data
            formData += '&action=lfa_update_shipping_calculator';

            // Use AJAX to update shipping via our custom handler
            $.ajax({
                type: 'POST',
                url: ajaxUrl,
                data: formData,
                dataType: 'json',
                success: function (response) {
                    if (response && response.success) {
                        // Update fragments if provided
                        if (response.data && response.data.fragments) {
                            $.each(response.data.fragments, function (key, value) {
                                $(key).replaceWith(value);
                            });
                        }
                        // Trigger checkout update to refresh shipping options
                        $('body').trigger('update_checkout');
                    } else {
                        var errorMsg = 'Failed to update shipping.';
                        if (response && response.data && response.data.message) {
                            errorMsg = response.data.message;
                        }
                        alert(errorMsg);
                    }
                },
                error: function () {
                    alert('An error occurred while updating shipping. Please try again.');
                },
                complete: function () {
                    $button.prop('disabled', false).text('Update');
                }
            });

            return false;
        });

        // Prevent shipping form from submitting checkout form (only on checkout page)
        // Use capture phase and highest priority - DO NOT trigger button click here to avoid loops
        $(document).on('submit', '.woocommerce-shipping-calculator[data-lfa-checkout-shipping-calc="true"]', function (e) {
            // Always prevent default for checkout shipping calculator
            e.preventDefault();
            e.stopPropagation();
            e.stopImmediatePropagation();
            e.stopPropagation ? e.stopPropagation() : (e.cancelBubble = true);
            return false;
        });

        // Also prevent any other shipping calculator forms on checkout from submitting
        $(document).on('submit', 'body.woocommerce-checkout .woocommerce-shipping-calculator', function (e) {
            e.preventDefault();
            e.stopPropagation();
            e.stopImmediatePropagation();
            e.stopPropagation ? e.stopPropagation() : (e.cancelBubble = true);

            var $form = $(this);
            var $button = $form.find('.lfa-shipping-update-btn, button[type="submit"]');
            if ($button.length) {
                $button.first().trigger('click');
            }
            return false;
        });

        // Additional safety: prevent form submission at the form level using native JS
        if ($('body').hasClass('woocommerce-checkout')) {
            $('.woocommerce-shipping-calculator[data-lfa-checkout-shipping-calc="true"]').each(function () {
                var form = this;
                form.setAttribute('onsubmit', 'return false;');
                form.method = 'get'; // Change to GET to prevent POST submission
                form.action = '#'; // Set action to hash

                // Remove any existing submit handlers and add our own with capture
                var clonedForm = form.cloneNode(true);
                form.parentNode.replaceChild(clonedForm, form);

                // Re-query to get the new form element
                var newForm = document.querySelector('.woocommerce-shipping-calculator[data-lfa-checkout-shipping-calc="true"]');
                if (newForm) {
                    newForm.addEventListener('submit', function (e) {
                        e.preventDefault();
                        e.stopPropagation();
                        e.stopImmediatePropagation();
                        return false;
                    }, true); // Capture phase
                }
            });
        }

        // Handle coupon removal on checkout page
        $(document).on('click', 'a.woocommerce-remove-coupon', function (e) {
            e.preventDefault();
            e.stopImmediatePropagation();
            e.stopPropagation();

            var $link = $(this);
            var href = $link.attr('href');
            var couponCode = $link.data('coupon') || '';

            if (!href && !couponCode) {
                return false;
            }

            // Disable link during update
            $link.css('pointer-events', 'none').css('opacity', '0.5');

            // Parse the original URL to get parameters
            try {
                var url = new URL(href, window.location.origin);
                var couponFromUrl = url.searchParams.get('remove_coupon') || url.searchParams.get('coupon') || '';

                // Use coupon code from data attribute or URL
                couponCode = couponCode || couponFromUrl;

                // Try to get nonce from URL first (it's already in the link)
                var nonce = url.searchParams.get('_wpnonce') || '';

                // If not in URL, try WooCommerce params
                if (!nonce) {
                    if (typeof wc_checkout_params !== 'undefined' && wc_checkout_params.remove_coupon_nonce) {
                        nonce = wc_checkout_params.remove_coupon_nonce;
                    } else if (typeof wc_add_to_cart_params !== 'undefined' && wc_add_to_cart_params.remove_coupon_nonce) {
                        nonce = wc_add_to_cart_params.remove_coupon_nonce;
                    } else {
                        // Try to get from form
                        nonce = $('input[name="woocommerce-process-checkout-nonce"]').val() ||
                            $('input[name="woocommerce-cart-nonce"]').val() || '';
                    }
                }

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

                // Prepare request data
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
                    success: function (response) {
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
                    error: function (xhr, status, error) {
                        // On error, reload the page
                        window.location.reload();
                    },
                    complete: function () {
                        // Only reset link state if not reloading
                        if (window.location.href === window.location.href) {
                            $link.css('pointer-events', '').css('opacity', '');
                        }
                    }
                });
            } catch (err) {
                // Fallback: just follow the link
                window.location.href = href;
            }

            return false;
        });
    });
})(jQuery);

