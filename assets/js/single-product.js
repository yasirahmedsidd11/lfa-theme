/**
 * Single Product Page JavaScript
 * Handles product image slider and variation forms
 */

(function ($) {
    'use strict';

    $(document).ready(function () {
        // Skeleton Loading - Hide skeleton and show content when page and slider are loaded
        function hideSkeleton() {
            var $skeleton = $('#lfa-product-skeleton');
            var $content = $('#lfa-product-content');
            var $slider = $('#lfa-product-slider');

            if ($skeleton.length && $content.length) {
                var contentLoaded = false;
                var sliderLoaded = false;

                function checkAndHideSkeleton() {
                    if (contentLoaded && sliderLoaded) {
                        setTimeout(function () {
                            $skeleton.addClass('hidden');
                            $content.addClass('loaded').show();
                        }, 300); // Small delay for smooth transition
                    }
                }

                // Wait for images and content to load
                $(window).on('load', function () {
                    contentLoaded = true;
                    checkAndHideSkeleton();
                });

                // Check if slider is initialized
                function checkSlider() {
                    if ($slider.length && typeof $.fn.slick !== 'undefined') {
                        if ($slider.hasClass('slick-initialized')) {
                            sliderLoaded = true;
                            checkAndHideSkeleton();
                        } else {
                            // Check again after a short delay
                            setTimeout(checkSlider, 100);
                        }
                    } else {
                        // Slider not found or slick not available, consider it loaded
                        sliderLoaded = true;
                        checkAndHideSkeleton();
                    }
                }

                // Start checking for slider after a short delay to allow initialization
                setTimeout(checkSlider, 200);

                // Fallback: If window load already fired, mark content as loaded
                if (document.readyState === 'complete') {
                    contentLoaded = true;
                    checkAndHideSkeleton();
                }

                // Maximum wait time fallback (5 seconds)
                setTimeout(function () {
                    if (!$content.hasClass('loaded')) {
                        contentLoaded = true;
                        sliderLoaded = true;
                        checkAndHideSkeleton();
                    }
                }, 5000);
            }
        }

        // Initialize skeleton hiding
        hideSkeleton();

        // Initialize product image slider
        var $slider = $('#lfa-product-slider');

        if ($slider.length && typeof $.fn.slick !== 'undefined') {
            // Check if slider is already initialized
            if (!$slider.hasClass('slick-initialized')) {
                $slider.slick({
                    slidesToShow: 2,
                    slidesToScroll: 1,
                    infinite: true,
                    arrows: true,
                    dots: false,
                    autoplay: true,
                    speed: 300,
                    prevArrow: '<button type="button" class="slick-prev" aria-label="Previous"><svg width="23" height="15" viewBox="0 0 23 15" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M0.292893 6.6569C-0.0976314 7.04743 -0.0976315 7.68059 0.292892 8.07112L6.65685 14.4351C7.04738 14.8256 7.68054 14.8256 8.07107 14.4351C8.46159 14.0446 8.46159 13.4114 8.07107 13.0209L2.41421 7.36401L8.07107 1.70716C8.46159 1.31663 8.46159 0.683469 8.07107 0.292945C7.68054 -0.0975799 7.04738 -0.0975799 6.65686 0.292944L0.292893 6.6569ZM23 7.36401L23 6.36401L1 6.36401L1 7.36401L1 8.36401L23 8.36401L23 7.36401Z" fill="black"/></svg></button>',
                    nextArrow: '<button type="button" class="slick-next" aria-label="Next"><svg width="23" height="15" viewBox="0 0 23 15" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M22.7071 6.6569C23.0976 7.04743 23.0976 7.68059 22.7071 8.07112L16.3431 14.4351C15.9526 14.8256 15.3195 14.8256 14.9289 14.4351C14.5384 14.0446 14.5384 13.4114 14.9289 13.0209L20.5858 7.36401L14.9289 1.70716C14.5384 1.31663 14.5384 0.683469 14.9289 0.292945C15.3195 -0.0975799 15.9526 -0.0975799 16.3431 0.292944L22.7071 6.6569ZM0 7.36401L-8.74228e-08 6.36401L22 6.36401L22 7.36401L22 8.36401L8.74228e-08 8.36401L0 7.36401Z" fill="black"/></svg></button>',
                    responsive: [
                        {
                            breakpoint: 1200,
                            settings: {
                                slidesToShow: 2,
                                slidesToScroll: 1
                            }
                        },
                        {
                            breakpoint: 980,
                            settings: {
                                slidesToShow: 1,
                                slidesToScroll: 1,
                                arrows: true,
                                dots: false
                            }
                        },
                        {
                            breakpoint: 768,
                            settings: {
                                slidesToShow: 1,
                                slidesToScroll: 1
                            }
                        },
                        {
                            breakpoint: 576,
                            settings: {
                                slidesToShow: 1,
                                slidesToScroll: 1,
                                arrows: true,
                                dots: false
                            }
                        }
                    ]
                });
            }
        }

        // Convert variation selects to radio buttons
        function convertSelectsToRadios() {
            $('.lfa-product-attributes .variations select').each(function () {
                var $select = $(this);
                var $wrapper = $select.closest('td.value');

                // Skip if already converted
                if ($wrapper.find('.lfa-variation-radio-wrapper').length > 0) {
                    // Update existing radios with current select options
                    updateRadios($select, $wrapper);
                    return;
                }

                var name = $select.attr('name');
                var selectedValue = $select.val();

                // Create radio wrapper
                var $radioWrapper = $('<div class="lfa-variation-radio-wrapper"></div>');

                // Create radio buttons for each option
                $select.find('option').each(function () {
                    var $option = $(this);
                    var value = $option.val();
                    var label = $option.text();

                    if (value === '') {
                        return; // Skip empty option
                    }

                    var radioId = name + '_' + value.replace(/[^a-z0-9]/gi, '_');
                    var checked = (value === selectedValue) ? 'checked' : '';

                    var $radioLabel = $('<label for="' + radioId + '" class="lfa-variation-radio-label"></label>');
                    var $radio = $('<input type="radio" id="' + radioId + '" name="' + name + '" value="' + value + '" ' + checked + ' class="lfa-variation-radio">');
                    var $radioText = $('<span class="lfa-radio-text">' + label + '</span>');

                    $radioLabel.append($radio).append($radioText);
                    $radioWrapper.append($radioLabel);
                });

                // Insert radio wrapper after select and hide select completely
                $select.after($radioWrapper);
                hideSelect($select);
            });
        }

        // Update existing radio buttons based on select options
        function updateRadios($select, $wrapper) {
            var selectedValue = $select.val();
            var $radioWrapper = $wrapper.find('.lfa-variation-radio-wrapper');

            // Update checked state
            $radioWrapper.find('.lfa-variation-radio').each(function () {
                var $radio = $(this);
                if ($radio.val() === selectedValue) {
                    $radio.prop('checked', true);
                } else {
                    $radio.prop('checked', false);
                }
            });
        }

        // Hide select element
        function hideSelect($select) {
            $select.css({
                'display': 'none',
                'visibility': 'hidden',
                'position': 'absolute',
                'opacity': '0',
                'width': '0',
                'height': '0',
                'overflow': 'hidden'
            });
        }

        // Initialize radio conversion
        convertSelectsToRadios();

        // Re-convert if variations form is updated
        $(document).on('woocommerce_update_variation_values', function () {
            setTimeout(convertSelectsToRadios, 50);
        });

        // Customize variable product add to cart section
        function customizeVariableAddToCart() {
            var $form = $('.lfa-product-attributes form.variations_form');
            if (!$form.length) return;

            var $addToCartWrapper = $form.find('.woocommerce-variation-add-to-cart');
            if (!$addToCartWrapper.length) return;

            // Skip if already customized
            if ($addToCartWrapper.hasClass('lfa-customized')) return;

            var $button = $addToCartWrapper.find('button.single_add_to_cart_button');
            if (!$button.length) return;

            // Get product price from page
            var productPrice = $('.lfa-product-title-price-row .lfa-product-price').html() || '';

            // Get product ID first (needed for wishlist and buy now buttons)
            var productId = $form.data('product_id');
            console.log('=== Variable Product Customization ===');
            console.log('Product ID from form data:', productId);
            
            // Try alternative methods to get product ID
            if (!productId) {
                console.log('Product ID not found in form data, trying alternatives...');
                
                // Try to get from form inputs
                productId = $form.find('input[name="product_id"]').val();
                console.log('Product ID from input[name="product_id"]:', productId);
                
                if (!productId) {
                    // Try to get from body class
                    var bodyClasses = $('body').attr('class').split(' ');
                    for (var i = 0; i < bodyClasses.length; i++) {
                        if (bodyClasses[i].startsWith('postid-')) {
                            productId = bodyClasses[i].replace('postid-', '');
                            console.log('Product ID from body class:', productId);
                            break;
                        }
                    }
                }
            }
            
            console.log('Final Product ID for variable product:', productId);

            // Create add to cart row wrapper
            var $addToCartRow = $('<div class="lfa-add-to-cart-row"></div>');

            // Update button content
            $button.html('<span class="lfa-cart-btn-text">ADD TO CART</span><span class="lfa-cart-btn-price">' + productPrice + '</span>');

            // Create wishlist button
            var $wishlistBtn = $('<button type="button" class="lfa-wishlist-btn" data-product-id="' + productId + '" aria-label="Add to wishlist">' +
                '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">' +
                '<path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path>' +
                '</svg></button>');
            console.log('Created wishlist button with product ID:', productId);
            var checkoutUrl = typeof wc_add_to_cart_params !== 'undefined' ? wc_add_to_cart_params.wc_ajax_url.replace('wc-ajax=%%endpoint%%', '') : '/checkout/';

            // Create buy it now button (initially disabled until variation is selected)
            var $buyNowBtn = $('<span class="lfa-buy-now-btn lfa-buy-now-btn-disabled">BUY IT NOW</span>');

            // Wrap button and wishlist in row
            $button.wrap($addToCartRow);
            $button.after($wishlistBtn);

            // Add buy now button after the row
            $button.closest('.lfa-add-to-cart-row').after($buyNowBtn);

            // Add validation for variable product buy now button
            $buyNowBtn.on('click', function(e) {
                var $variationForm = $(this).closest('.lfa-product-attributes').find('.variations_form');
                if ($variationForm.length) {
                    var variationId = $variationForm.find('input[name="variation_id"]').val();
                    
                    if (!variationId || variationId === '0' || variationId === '') {
                        e.preventDefault();
                        
                        // Show error message
                        var $errorMsg = $('.lfa-variation-error');
                        if ($errorMsg.length) {
                            $errorMsg.show();
                        } else {
                            $('<div class="lfa-variation-error" style="color: #e2401c; background: #fff3f3; padding: 12px 16px; margin: 12px 0; border: 1px solid #e2401c; border-radius: 4px; font-size: 14px;">Please select a variation first.</div>')
                                .insertBefore($(this).parent());
                        }
                        
                        // Scroll to error
                        $('html, body').animate({
                            scrollTop: $('.lfa-variation-error').offset().top - 100
                        }, 300);
                        
                        console.log('Buy now prevented - no variation selected');
                        return false;
                    }
                }
            });

            // Mark as customized
            $addToCartWrapper.addClass('lfa-customized');
        }

        // Update add to cart button price when variation changes
        $(document).on('found_variation', function (event, variation) {
            // Update the price in the button
            if (variation.price_html) {
                $('.lfa-cart-btn-price').html(variation.price_html);
            }

            // Customize the button if not already done
            setTimeout(customizeVariableAddToCart, 100);

            // Clear any error messages when variation is selected
            $('.lfa-variation-error').fadeOut(function() {
                $(this).remove();
            });
            
            // Update buy now link with variation
            var $form = $(event.target);
            var $buyNowBtn = $form.find('.lfa-buy-now-btn');
            
            if (variation.variation_id) {
                var productId = $form.data('product_id');
                var checkoutUrl = window.location.origin + '/checkout/';
                
                // Check if variation is in stock
                if (variation.is_in_stock !== false && variation.stock_status !== 'outofstock') {
                    var buyNowUrl = checkoutUrl + '?add-to-cart=' + productId + '&variation_id=' + variation.variation_id;

                    // Add variation attributes to URL
                    $form.find('.variations select').each(function () {
                        var name = $(this).attr('name');
                        var value = $(this).val();
                        if (name && value) {
                            buyNowUrl += '&' + name + '=' + encodeURIComponent(value);
                        }
                    });

                    // Enable buy now button
                    $buyNowBtn.attr('href', buyNowUrl).removeClass('lfa-buy-now-btn-disabled');
                    if ($buyNowBtn.is('span')) {
                        var $newBtn = $('<a>').attr('href', buyNowUrl).addClass('lfa-buy-now-btn').text($buyNowBtn.text());
                        $buyNowBtn.replaceWith($newBtn);
                    }
                } else {
                    // Disable buy now button for out of stock variations
                    $buyNowBtn.removeAttr('href').addClass('lfa-buy-now-btn-disabled');
                    if ($buyNowBtn.is('a')) {
                        var $disabledBtn = $('<span>').addClass('lfa-buy-now-btn lfa-buy-now-btn-disabled').text($buyNowBtn.text());
                        $buyNowBtn.replaceWith($disabledBtn);
                    }
                }
            }
        });

        $(document).on('reset_data', function () {
            var $price = $('.lfa-product-title-price-row .lfa-product-price').html();
            if ($price) {
                $('.lfa-cart-btn-price').html($price);
            }
            // Disable buy now button when variation is cleared
            var $buyNowBtn = $('.lfa-buy-now-btn');
            if ($buyNowBtn.length && $buyNowBtn.is('a')) {
                var $disabledBtn = $('<span>').addClass('lfa-buy-now-btn lfa-buy-now-btn-disabled').text($buyNowBtn.text());
                $buyNowBtn.replaceWith($disabledBtn);
            }
        });

        // Initialize variable button customization
        setTimeout(customizeVariableAddToCart, 200);

        // Watch for variation form changes (radio buttons)
        $(document).on('change', '.lfa-product-attributes .lfa-variation-radio', function () {
            var $form = $(this).closest('form.variations_form');
            if ($form.length) {
                // Update the hidden select for WooCommerce compatibility
                var $radio = $(this);
                var name = $radio.attr('name');
                var value = $radio.val();
                var $select = $form.find('select[name="' + name + '"]');

                // Trigger change on select to update WooCommerce
                $select.val(value).trigger('change');
            }
        });

        // Wishlist button functionality
        $(document).on('click', '.lfa-wishlist-btn', function (e) {
            e.preventDefault();
            console.log('=== WISHLIST BUTTON CLICKED ===');
            var $btn = $(this);
            console.log('Button element:', $btn);
            console.log('Button HTML:', $btn[0].outerHTML);
            
            var productId = $btn.data('product-id');
            console.log('Product ID from data-product-id:', productId);
            console.log('All data attributes:', $btn.data());
            
            // Try alternative methods to get product ID
            if (!productId) {
                console.log('Product ID not found in data-product-id, trying alternatives...');
                
                // Try to get from attribute
                productId = $btn.attr('data-product-id');
                console.log('Product ID from attr:', productId);
                
                // Try to get from closest form
                var $form = $btn.closest('form.cart');
                if ($form.length) {
                    console.log('Found form:', $form);
                    productId = $form.find('input[name="add-to-cart"]').val() || 
                                $form.find('input[name="product_id"]').val() ||
                                $form.data('product_id');
                    console.log('Product ID from form:', productId);
                }
                
                // Try to get from body class
                if (!productId) {
                    var bodyClasses = $('body').attr('class').split(' ');
                    for (var i = 0; i < bodyClasses.length; i++) {
                        if (bodyClasses[i].startsWith('postid-')) {
                            productId = bodyClasses[i].replace('postid-', '');
                            console.log('Product ID from body class:', productId);
                            break;
                        }
                    }
                }
            }

            console.log('Final product ID:', productId);

            if (!productId) {
                console.error('ERROR: No product ID found!');
                return;
            }

            // Check if this is a variable product and get variation ID
            var variationId = 0;
            var $variationForm = $btn.closest('.lfa-product-attributes').find('.variations_form');
            if ($variationForm.length) {
                variationId = $variationForm.find('input[name="variation_id"]').val() || 0;
                console.log('Variable product detected, variation ID:', variationId);
                
                // For variable products, check if variation is selected
                if (!variationId || variationId === '0' || variationId === '') {
                    console.log('No variation selected for variable product');
                    var errorMsg = 'Please select a variation first.';
                    if ($('.lfa-wishlist-error').length) {
                        $('.lfa-wishlist-error').html(errorMsg).show();
                    } else {
                        $('<div class="lfa-wishlist-error lfa-message-box" style="color: #e2401c; background: #fff3f3; padding: 12px 16px; margin: 12px 0; border: 1px solid #e2401c; border-radius: 4px; font-size: 14px;">' + errorMsg + '</div>')
                            .insertBefore($btn.closest('.lfa-add-to-cart-row'))
                            .delay(5000)
                            .fadeOut(function() {
                                $(this).remove();
                            });
                    }
                    return;
                }
            }

            // Use our custom AJAX handler (works for both simple and variable products)
            console.log('Using custom AJAX handler');
            var ajaxUrl = typeof LFA !== 'undefined' && LFA.ajaxUrl ? LFA.ajaxUrl : (typeof wc_add_to_cart_params !== 'undefined' ? wc_add_to_cart_params.ajax_url : '/wp-admin/admin-ajax.php');
            var nonce = typeof LFA !== 'undefined' && LFA.nonce ? LFA.nonce : '';
            
            console.log('LFA object:', typeof LFA !== 'undefined' ? LFA : 'undefined');
            console.log('Nonce available:', nonce ? 'Yes' : 'No');
            
            var ajaxData = {
                action: 'lfa_add_to_wishlist',
                product_id: productId,
                variation_id: variationId,
                quantity: 1,
                nonce: nonce
            };
            console.log('AJAX URL:', ajaxUrl);
            console.log('AJAX Data:', ajaxData);
            
            $.ajax({
                url: ajaxUrl,
                type: 'POST',
                data: ajaxData,
                success: function (response) {
                    console.log('AJAX Response:', response);
                    if (response && response.success) {
                        console.log('Product added to wishlist successfully');
                        $btn.addClass('active');
                        $btn.find('svg').css('fill', '#000000');
                        
                        // Show success message
                        var successMsg = response.data && response.data.message ? response.data.message : 'Product added to wishlist!';
                        if ($('.lfa-wishlist-success').length) {
                            $('.lfa-wishlist-success').html(successMsg).show();
                        } else {
                            $('<div class="lfa-wishlist-success lfa-message-box" style="color: #4caf50; background: #f1f8f4; padding: 12px 16px; margin: 12px 0; border: 1px solid #4caf50; border-radius: 4px; font-size: 14px;">' + successMsg + '</div>')
                                .insertBefore($btn.closest('.lfa-add-to-cart-row'))
                                .delay(3000)
                                .fadeOut(function() {
                                    $(this).remove();
                                });
                        }
                    } else {
                        console.log('Failed to add product via AJAX:', response.data ? response.data.message : 'Unknown error');
                        // Show error message
                        var errorMsg = response.data && response.data.message ? response.data.message : 'Failed to add product to wishlist.';
                        if ($('.lfa-wishlist-error').length) {
                            $('.lfa-wishlist-error').html(errorMsg).show();
                        } else {
                            $('<div class="lfa-wishlist-error lfa-message-box" style="color: #e2401c; background: #fff3f3; padding: 12px 16px; margin: 12px 0; border: 1px solid #e2401c; border-radius: 4px; font-size: 14px;">' + errorMsg + '</div>')
                                .insertBefore($btn.closest('.lfa-add-to-cart-row'))
                                .delay(5000)
                                .fadeOut(function() {
                                    $(this).remove();
                                });
                        }
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AJAX Error:', status, error);
                    console.error('XHR Response:', xhr.responseText);
                    var errorMsg = 'An unexpected error occurred. Please try again.';
                    if ($('.lfa-wishlist-error').length) {
                        $('.lfa-wishlist-error').html(errorMsg).show();
                    } else {
                        $('<div class="lfa-wishlist-error lfa-message-box" style="color: #e2401c; background: #fff3f3; padding: 12px 16px; margin: 12px 0; border: 1px solid #e2401c; border-radius: 4px; font-size: 14px;">' + errorMsg + '</div>')
                            .insertBefore($btn.closest('.lfa-add-to-cart-row'))
                            .delay(5000)
                            .fadeOut(function() {
                                $(this).remove();
                            });
                    }
                }
            });
        });

        // Check if product is already in wishlist on page load
        function checkWishlistStatus() {
            $('.lfa-wishlist-btn').each(function () {
                var $btn = $(this);
                var productId = $btn.data('product-id');

                if (productId) {
                    // Check if TI Wishlist button exists and product is in list
                    var $tinvwlBtn = $('.tinvwl_add_to_wishlist_button[data-tinv-wl-list][data-tinv-wl-product="' + productId + '"]');
                    if ($tinvwlBtn.length && $tinvwlBtn.hasClass('tinvwl-product-in-list')) {
                        $btn.addClass('active');
                        $btn.find('svg').css('fill', '#000000');
                    }
                }
            });
        }

        // Check wishlist status on page load and after AJAX
        $(document).ready(function () {
            setTimeout(checkWishlistStatus, 500);
        });

        // Also check after TI Wishlist AJAX completes
        $(document.body).on('tinvwl_wishlist_added', function () {
            setTimeout(checkWishlistStatus, 100);
        });

        // =============================================
        // Section 2: Why You Need This - Tabs
        // =============================================

        // Tab switching
        $(document).on('click', '.lfa-why-tab', function () {
            var $tab = $(this);
            var tabId = $tab.data('tab');
            var $wrapper = $tab.closest('.lfa-why-content');

            // Update active tab
            $wrapper.find('.lfa-why-tab').removeClass('active');
            $tab.addClass('active');

            // Update active content
            $wrapper.find('.lfa-why-tab-content').removeClass('active');
            $wrapper.find('.lfa-why-tab-content[data-content="' + tabId + '"]').addClass('active');

            // Initialize size chart slider if switching to that tab
            if (tabId === 'size-chart') {
                initSizeChartSlider();
            }
        });

        // Initialize Size Chart Slider
        function initSizeChartSlider() {
            var $sizeChartSlider = $('#lfa-size-chart-slider');

            if ($sizeChartSlider.length && typeof $.fn.slick !== 'undefined') {
                if (!$sizeChartSlider.hasClass('slick-initialized')) {
                    $sizeChartSlider.slick({
                        slidesToShow: 1,
                        slidesToScroll: 1,
                        infinite: true,
                        arrows: false,
                        dots: true,
                        autoplay: false,
                        speed: 300,
                        adaptiveHeight: true
                    });
                } else {
                    $sizeChartSlider.slick('refresh');
                }
            }
        }

        // Initialize size chart slider if it's the only/first tab
        if ($('.lfa-why-tab-content[data-content="size-chart"].active').length) {
            initSizeChartSlider();
        }

        // =============================================
        // Section 3: Reviews - Write Review Form Toggle
        // =============================================

        // Function to open review form
        function openReviewForm() {
            var $formWrapper = $('#lfa-review-form-wrapper');
            var $btn = $('#lfa-write-review-trigger');

            if ($formWrapper.length) {
                // Show the form
                $formWrapper.slideDown(300);
                $btn.text('HIDE REVIEW FORM');
                // Scroll to form smoothly
                setTimeout(function () {
                    $('html, body').animate({
                        scrollTop: $formWrapper.offset().top - 100
                    }, 500);
                }, 100);
            }
        }

        // Write Review Button Toggle
        $(document).on('click', '#lfa-write-review-trigger', function (e) {
            e.preventDefault();
            var $formWrapper = $('#lfa-review-form-wrapper');
            var $btn = $(this);

            if ($formWrapper.is(':visible')) {
                $formWrapper.slideUp(300);
                $btn.text('WRITE A REVIEW');
            } else {
                openReviewForm();
            }
        });

        // Add Review Link from Section 1 - Scroll and Open Form
        $(document).on('click', '.lfa-add-review-link', function (e) {
            e.preventDefault();
            var $reviewsSection = $('#reviews');

            if ($reviewsSection.length) {
                // Scroll to reviews section
                $('html, body').animate({
                    scrollTop: $reviewsSection.offset().top - 100
                }, 500, function () {
                    // After scrolling, open the form
                    setTimeout(openReviewForm, 300);
                });
            }
        });

        // Star Rating Input Functionality
        var currentRating = 0;

        // Initialize star rating
        function initStarRating() {
            var $stars = $('.lfa-rating-star');
            var $ratingInput = $('#lfa-rating-value');
            var $errorMsg = $('.lfa-rating-error');

            // Click handler
            $stars.on('click', function () {
                var rating = parseInt($(this).data('rating'));
                currentRating = rating;
                $ratingInput.val(rating);
                $errorMsg.hide();
                updateStars(rating);
            });

            // Hover handler
            $stars.on('mouseenter', function () {
                var rating = parseInt($(this).data('rating'));
                updateStars(rating, true);
            });

            // Mouse leave handler
            $('.lfa-review-rating-stars').on('mouseleave', function () {
                updateStars(currentRating, false);
            });
        }

        // Update star display
        function updateStars(rating, isHover) {
            var $stars = $('.lfa-rating-star');
            $stars.each(function () {
                var starRating = parseInt($(this).data('rating'));
                var $star = $(this).find('.star');

                if (starRating <= rating) {
                    $star.addClass('filled').removeClass('empty');
                } else {
                    $star.removeClass('filled').addClass('empty');
                }
            });
        }

        // Form validation
        $(document).on('submit', '#lfa-review-form', function (e) {
            var rating = parseInt($('#lfa-rating-value').val());
            var $errorMsg = $('.lfa-rating-error');

            if (!rating || rating === 0) {
                e.preventDefault();
                $errorMsg.show();
                $('html, body').animate({
                    scrollTop: $('.lfa-review-rating-field').offset().top - 100
                }, 300);
                return false;
            }
        });

        // Initialize on page load
        initStarRating();

        // Re-initialize when form is shown
        $(document).on('click', '#lfa-write-review-trigger', function () {
            setTimeout(initStarRating, 100);
        });

        // =============================================
        // Section 3: Reviews - Sort and Filter
        // =============================================

        // Store original reviews order
        var $reviewsList = $('.lfa-reviews-list');
        var originalReviews = [];

        // Initialize reviews data
        function initReviewsData() {
            if ($reviewsList.length) {
                originalReviews = [];
                $reviewsList.find('.lfa-review-item').each(function () {
                    var $item = $(this);
                    originalReviews.push({
                        element: $item.clone(true),
                        rating: parseInt($item.data('rating')) || 0,
                        timestamp: parseInt($item.data('timestamp')) || 0
                    });
                });
            }
        }

        // Sort and filter reviews
        function applyReviewSortAndFilter() {
            if (!originalReviews.length) {
                initReviewsData();
            }

            var sortValue = $('.lfa-reviews-sort').val() || 'recent';
            var filterValue = $('.lfa-reviews-filter').val() || 'all';

            // Filter reviews
            var filteredReviews = originalReviews.filter(function (review) {
                if (filterValue === 'all') {
                    return true;
                }
                return review.rating === parseInt(filterValue);
            });

            // Sort reviews
            filteredReviews.sort(function (a, b) {
                switch (sortValue) {
                    case 'recent':
                        return b.timestamp - a.timestamp; // Newest first
                    case 'oldest':
                        return a.timestamp - b.timestamp; // Oldest first
                    case 'highest':
                        return b.rating - a.rating; // Highest rating first
                    case 'lowest':
                        return a.rating - b.rating; // Lowest rating first
                    default:
                        return 0;
                }
            });

            // Clear current list
            $reviewsList.empty();

            // Append sorted and filtered reviews
            if (filteredReviews.length > 0) {
                filteredReviews.forEach(function (review) {
                    $reviewsList.append(review.element);
                });
            } else {
                $reviewsList.append('<div class="lfa-no-reviews" style="text-align: center; padding: 40px; color: #666; font-family: \'Questrial\', sans-serif;">No reviews found matching your criteria.</div>');
            }
        }

        // Initialize reviews data on page load
        if ($reviewsList.length) {
            initReviewsData();
        }

        // Sort change handler
        $(document).on('change', '.lfa-reviews-sort', function () {
            applyReviewSortAndFilter();
        });

        // Filter change handler
        $(document).on('change', '.lfa-reviews-filter', function () {
            applyReviewSortAndFilter();
        });

        // =============================================
        // Section 4: Upsells Slider
        // =============================================

        // Initialize upsells slider
        function initUpsellsSlider() {
            // Wait for WooCommerce shortcode to render (longer timeout like featured slider)
            setTimeout(function () {
                var $upsellsContainer = $('#lfa-upsells-slider');

                if ($upsellsContainer.length) {
                    var $productsList = $upsellsContainer.find('.products');

                    // Check if products list exists
                    if ($productsList.length === 0) {
                        // Try alternative selector
                        $productsList = $upsellsContainer.find('ul.products, .woocommerce ul.products');
                    }

                    if ($productsList.length === 0) {
                        return;
                    }

                    if (typeof $.fn.slick !== 'undefined') {
                        // Check if slider is already initialized
                        if (!$productsList.hasClass('slick-initialized')) {
                            $productsList.slick({
                                slidesToShow: 4,
                                slidesToScroll: 1,
                                infinite: true,
                                arrows: true,
                                dots: false,
                                autoplay: false,
                                speed: 300,
                                swipe: true,
                                touchMove: true,
                                draggable: true,
                                prevArrow: '<button type="button" class="slick-prev" aria-label="Previous"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M15 18l-6-6 6-6"/></svg></button>',
                                nextArrow: '<button type="button" class="slick-next" aria-label="Next"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 18l6-6-6-6"/></svg></button>',
                                responsive: [
                                    {
                                        breakpoint: 1200,
                                        settings: {
                                            slidesToShow: 3,
                                            slidesToScroll: 1
                                        }
                                    },
                                    {
                                        breakpoint: 980,
                                        settings: {
                                            slidesToShow: 2,
                                            slidesToScroll: 1
                                        }
                                    },
                                    {
                                        breakpoint: 768,
                                        settings: {
                                            slidesToShow: 2,
                                            slidesToScroll: 1
                                        }
                                    },
                                    {
                                        breakpoint: 576,
                                        settings: {
                                            slidesToShow: 1,
                                            slidesToScroll: 1
                                        }
                                    }
                                ]
                            });

                            // Force refresh after initialization to fix display issues
                            setTimeout(function () {
                                $productsList.slick('refresh');
                            }, 500);
                        }
                    }
                }
            }, 1000); // Wait 1 second for WooCommerce to render (same as featured slider)
        }

        // Initialize upsells slider on page load
        initUpsellsSlider();

        // Composite Product Customization
        function customizeCompositeProduct() {
            console.log('=== Attempting to customize composite product ===');

            // Check if we're on a composite product page
            var $compositeContainer = $('.lfa-composite-product-container[data-product-type="composite"]');
            console.log('Composite container found:', $compositeContainer.length);

            // ONLY find composite forms (not simple or variable)
            var $compositeForm = $('.composite_form, form.cart[data-product_type="composite"]');

            console.log('Found composite forms:', $compositeForm.length);

            // If no forms found, try within the container
            if (!$compositeForm.length && $compositeContainer.length) {
                $compositeForm = $compositeContainer.find('form.cart');
                console.log('Found forms in container:', $compositeForm.length);
            }

            if (!$compositeForm.length) {
                console.log('No composite form found');
                return;
            }

            // Check each composite form
            $compositeForm.each(function () {
                var $form = $(this);

                // Double-check this is actually a composite product
                // Check if form has composite class, or if it's inside composite container, or has composite data attribute
                var isComposite = $form.hasClass('composite_form') ||
                    $form.data('product_type') === 'composite' ||
                    $form.closest('.lfa-composite-product-container').length > 0 ||
                    $form.find('.composite_data').length > 0;

                if (!isComposite) {
                    console.log('Not a composite product, skipping');
                    return;
                }

                console.log('Confirmed composite product, proceeding...');

                // Skip if already customized
                if ($form.hasClass('lfa-customized')) {
                    console.log('Form already customized');
                    return;
                }

                $form.addClass('lfa-customized');

                console.log('Customizing composite product form...');
                console.log('Form HTML:', $form.attr('class'));

                // Find and hide quantity selector
                var $quantity = $form.find('.quantity, div.quantity');
                console.log('Found quantity selectors:', $quantity.length);
                $quantity.hide().css({
                    'display': 'none !important',
                    'visibility': 'hidden',
                    'opacity': '0',
                    'position': 'absolute',
                    'left': '-9999px'
                });

                // Find and hide ALL buttons that might be default composite buttons
                var $allButtons = $form.find('button');
                console.log('Found buttons:', $allButtons.length);

                $allButtons.each(function () {
                    var $btn = $(this);
                    console.log('Button classes:', $btn.attr('class'), 'Type:', $btn.attr('type'), 'Name:', $btn.attr('name'));

                    // Hide all add-to-cart buttons except our custom one
                    if (!$btn.hasClass('lfa-add-to-cart-btn') &&
                        ($btn.attr('type') === 'submit' ||
                            $btn.hasClass('single_add_to_cart_button') ||
                            $btn.hasClass('composite_add_to_cart_button') ||
                            $btn.hasClass('composite_button') ||
                            $btn.attr('name') === 'add-to-cart')) {
                        $btn.hide().css({
                            'display': 'none',
                            'visibility': 'hidden',
                            'opacity': '0',
                            'position': 'absolute',
                            'left': '-9999px'
                        });
                        console.log('Hidden button:', $btn.attr('class'));
                    }
                });

                // Get product price and ID from multiple sources
                var productPrice = $('.lfa-product-title-price-row .lfa-product-price').html() || '';
                var productId = $form.data('product_id') || 
                                $form.data('product-id') ||
                                $form.find('input[name="add-to-cart"]').val() || 
                                $form.find('button[name="add-to-cart"]').val() ||
                                $('input[name="product_id"]').val() ||
                                $('.lfa-composite-product-container').data('product-id') ||
                                $form.closest('.lfa-composite-product-container').data('product-id');

                console.log('Product ID:', productId);
                console.log('Product Price:', productPrice);
                
                // If still no product ID found, try to get from URL or body class
                if (!productId) {
                    var bodyClass = $('body').attr('class');
                    var match = bodyClass.match(/postid-(\d+)/);
                    if (match) {
                        productId = match[1];
                        console.log('Product ID from body class:', productId);
                    }
                }

                // Check if custom actions already exist
                if ($form.find('.lfa-composite-actions, .lfa-product-add-to-cart-section').length > 0) {
                    console.log('Custom actions already exist');
                    return;
                }

                // Create custom actions wrapper
                var $actionsWrapper = $('<div class="lfa-product-add-to-cart-section lfa-composite-actions"></div>');

                // Create add to cart row
                var $addToCartRow = $('<div class="lfa-add-to-cart-row"></div>');

                // Create custom add to cart button
                var $addToCartBtn = $('<button type="button" class="lfa-add-to-cart-btn button alt">' +
                    '<span class="lfa-cart-btn-text">ADD TO CART</span>' +
                    '<span class="lfa-cart-btn-price">' + productPrice + '</span>' +
                    '</button>');

                // Create wishlist button
                var $wishlistBtn = $('<button type="button" class="lfa-wishlist-btn" data-product-id="' + productId + '" aria-label="Add to wishlist">' +
                    '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">' +
                    '<path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path>' +
                    '</svg></button>');

                // Create buy it now button
                var checkoutUrl = typeof wc_add_to_cart_params !== 'undefined' ? wc_add_to_cart_params.wc_ajax_url.replace('wc-ajax=%%endpoint%%', '') : '/checkout/';
                var $buyNowBtn = $('<a href="' + checkoutUrl + '?add-to-cart=' + productId + '" class="lfa-buy-now-btn">BUY IT NOW</a>');

                // Append buttons to row
                $addToCartRow.append($addToCartBtn).append($wishlistBtn);

                // Append row and buy now to actions wrapper
                $actionsWrapper.append($addToCartRow).append($buyNowBtn);

                // Insert at the end of form
                $form.append($actionsWrapper);

                console.log('Custom buttons added to form');

// Helper function to validate composite selections
                function validateCompositeSelections() {
                    var allComponentsSelected = true;
                    var missingComponents = [];

                    // Check each component
                    $form.find('.component').each(function() {
                        var $component = $(this);
                        var componentTitle = $component.find('.component_title, .component_title_wrapper .component_title').text().trim() || 'Component';
                        var isSelected = false;

                        // Check dropdown selection
                        var $select = $component.find('select.component_options_select');
                        if ($select.length && $select.val() && $select.val() !== '') {
                            isSelected = true;
                        }

                        // Check radio/thumbnail selection
                        var $radio = $component.find('input[type="radio"]:checked');
                        if ($radio.length) {
                            isSelected = true;
                        }

                        // Check if component has a variation form with selected variation
                        var $variationForm = $component.find('.variations_form');
                        if ($variationForm.length) {
                            var variationId = $variationForm.find('input[name="variation_id"]').val();
                            if (variationId && variationId !== '0' && variationId !== '') {
                                isSelected = true;
                            } else {
                                isSelected = false;
                            }
                        }

                        if (!isSelected) {
                            allComponentsSelected = false;
                            missingComponents.push(componentTitle);
                            console.log('Component not selected:', componentTitle);
                        }
                    });

                    return { allSelected: allComponentsSelected, missing: missingComponents };
                }

                // Add validation for composite product buy now button
                $buyNowBtn.on('click', function(e) {
                    console.log('Composite buy now clicked - validating selections...');
                    
                    var validation = validateCompositeSelections();

                    if (!validation.allSelected) {
                        e.preventDefault();
                        
                        var errorMessage = validation.missing.length === 1 
                            ? 'Please select a variation for: ' + validation.missing[0]
                            : 'Please select variations for: ' + validation.missing.join(', ');
                        
                        // Show error message
                        var $errorMsg = $('.lfa-composite-variation-error');
                        if ($errorMsg.length) {
                            $errorMsg.html(errorMessage).show();
                        } else {
                            $('<div class="lfa-composite-variation-error" style="color: #e2401c; background: #fff3f3; padding: 12px 16px; margin: 12px 0; border: 1px solid #e2401c; border-radius: 4px; font-size: 14px;">' + errorMessage + '</div>')
                                .insertBefore($actionsWrapper);
                        }
                        
                        // Scroll to error
                        $('html, body').animate({
                            scrollTop: $('.lfa-composite-variation-error').offset().top - 100
                        }, 300);
                        
                        console.log('Buy now prevented - components not selected:', validation.missing);
                        return false;
                    }
                    
                    console.log('All components selected, proceeding with buy now');
                });

                // Add wishlist button handler for composite products
                $wishlistBtn.on('click', function(e) {
                    e.preventDefault();
                    console.log('Composite wishlist button clicked');
                    
                    // Get product ID from button data attribute or form
                    var currentProductId = $(this).data('product-id') || 
                                          $(this).data('productId') || 
                                          $form.data('product_id') || 
                                          $form.find('input[name="add-to-cart"]').val() ||
                                          productId;
                    
                    console.log('Product ID for wishlist:', currentProductId);
                    
                    if (!currentProductId) {
                        console.error('No product ID found for wishlist');
                        alert('Error: Could not determine product ID');
                        return false;
                    }
                    
                    var validation = validateCompositeSelections();

                    if (!validation.allSelected) {
                        var errorMessage = validation.missing.length === 1 
                            ? 'Please select a variation for: ' + validation.missing[0]
                            : 'Please select variations for: ' + validation.missing.join(', ');
                        
                        // Show error message
                        var $errorMsg = $('.lfa-composite-variation-error');
                        if ($errorMsg.length) {
                            $errorMsg.html(errorMessage).show();
                        } else {
                            $('<div class="lfa-composite-variation-error" style="color: #e2401c; background: #fff3f3; padding: 12px 16px; margin: 12px 0; border: 1px solid #e2401c; border-radius: 4px; font-size: 14px;">' + errorMessage + '</div>')
                                .insertBefore($actionsWrapper);
                        }
                        
                        // Scroll to error
                        $('html, body').animate({
                            scrollTop: $('.lfa-composite-variation-error').offset().top - 100
                        }, 300);
                        
                        console.log('Wishlist prevented - components not selected:', validation.missing);
                        return false;
                    }

                    // All components selected, add composite to wishlist
                    console.log('Adding composite product to wishlist...', 'Product ID:', currentProductId);
                    
                    // Use custom AJAX handler for composite products
                    var formData = new FormData($form[0]);
                    formData.append('product_id', currentProductId);
                    formData.append('action', 'lfa_add_composite_to_wishlist');
                    
                    // Get AJAX URL
                    var ajaxUrl = typeof wc_add_to_cart_params !== 'undefined' && wc_add_to_cart_params.ajax_url 
                        ? wc_add_to_cart_params.ajax_url 
                        : '/wp-admin/admin-ajax.php';
                    
                    console.log('Sending AJAX request to:', ajaxUrl);
                    
                    $.ajax({
                        url: ajaxUrl,
                        type: 'POST',
                        data: formData,
                        processData: false,
                        contentType: false,
                        success: function(response) {
                            console.log('Wishlist response:', response);
                            
                            if (response.success) {
                                console.log('Composite product added to wishlist successfully');
                                $wishlistBtn.addClass('active');
                                
                                // Show success message
                                if ($('.lfa-wishlist-success').length) {
                                    $('.lfa-wishlist-success').show().delay(3000).fadeOut();
                                } else {
                                    $('<div class="lfa-wishlist-success" style="color: #4caf50; background: #f1f8f4; padding: 12px 16px; margin: 12px 0; border: 1px solid #4caf50; border-radius: 4px; font-size: 14px;">Product added to wishlist!</div>')
                                        .insertBefore($actionsWrapper)
                                        .delay(3000)
                                        .fadeOut();
                                }
                            } else {
                                console.error('Failed to add to wishlist:', response.data);
                                
                                // Show error message
                                var errorMsg = response.data && response.data.message 
                                    ? response.data.message 
                                    : 'Failed to add product to wishlist.';
                                    
                                $('<div class="lfa-wishlist-error" style="color: #e2401c; background: #fff3f3; padding: 12px 16px; margin: 12px 0; border: 1px solid #e2401c; border-radius: 4px; font-size: 14px;">' + errorMsg + '</div>')
                                    .insertBefore($actionsWrapper)
                                    .delay(5000)
                                    .fadeOut();
                            }
                        },
                        error: function(xhr, status, error) {
                            console.error('AJAX error adding to wishlist:', error);
                            console.error('Response:', xhr.responseText);
                            
                            // Show error message
                            $('<div class="lfa-wishlist-error" style="color: #e2401c; background: #fff3f3; padding: 12px 16px; margin: 12px 0; border: 1px solid #e2401c; border-radius: 4px; font-size: 14px;">Error adding product to wishlist. Please try again.</div>')
                                .insertBefore($actionsWrapper)
                                .delay(5000)
                                .fadeOut();
                        }
                    });
                });

                // Mark out-of-stock variations as disabled
                function markOutOfStockVariations() {
                    $form.find('.component').each(function () {
                        var $component = $(this);

                        // For dropdown/select variations
                        $component.find('select.component_options_select option').each(function () {
                            var $option = $(this);
                            var variationId = $option.val();

                            if (variationId) {
                                // Check if this variation is out of stock
                                var isOutOfStock = false;

                                // Try to find stock status from data attribute
                                var stockStatus = $option.data('stock_status');
                                if (stockStatus === 'out-of-stock' || stockStatus === 'outofstock') {
                                    isOutOfStock = true;
                                }

                                // Check variation stock in availability
                                var availability = $option.data('availability_html');
                                if (availability && (availability.includes('out-of-stock') || availability.includes('Out of stock'))) {
                                    isOutOfStock = true;
                                }

                                if (isOutOfStock) {
                                    $option.prop('disabled', true);
                                    var optionText = $option.text();
                                    if (!optionText.includes('Out of Stock')) {
                                        $option.text(optionText + ' - Out of Stock');
                                    }
                                    $option.addClass('lfa-out-of-stock-option');
                                }
                            }
                        });

                        // For thumbnail/radio button variations
                        $component.find('.component_option_thumbnail_container, .component_option').each(function () {
                            var $container = $(this);
                            var $input = $container.find('input[type="radio"]');

                            if ($input.length) {
                                var stockStatus = $container.data('stock_status') || $input.data('stock_status');

                                if (stockStatus === 'out-of-stock' || stockStatus === 'outofstock') {
                                    $container.addClass('lfa-out-of-stock-item');
                                    $input.prop('disabled', true);

                                    // Add out of stock label if not exists
                                    if (!$container.find('.lfa-out-of-stock-label').length) {
                                        $container.append('<span class="lfa-out-of-stock-label">Out of Stock</span>');
                                    }

                                    // Make it look disabled
                                    $container.css({
                                        'opacity': '0.5',
                                        'pointer-events': 'none',
                                        'cursor': 'not-allowed'
                                    });
                                }
                            }
                        });
                    });
                }

                // Initial marking
                markOutOfStockVariations();

                // Re-mark when composite updates
                $(document).on('wc-composite-initializing wc-composite-initialized', markOutOfStockVariations);

                // Store reference to hidden button for later
                var $hiddenBtn = $form.find('button.single_add_to_cart_button, button.composite_add_to_cart_button').first();

                // Update price on composite price change
                $(document).on('wc-composite-totals-changed', function (event, composite) {
                    if (composite && composite.price_data) {
                        var newPrice = composite.price_data.price_html || productPrice;
                        $addToCartBtn.find('.lfa-cart-btn-price').html(newPrice);
                        console.log('Price updated:', newPrice);
                    }

                    // Clear any error messages when selection changes
                    $('.lfa-composite-stock-error, .lfa-composite-variation-error').fadeOut(function () {
                        $(this).remove();
                    });
                });

                // Handle add to cart click
                $addToCartBtn.on('click', function (e) {
                    e.preventDefault();
                    console.log('Custom add to cart clicked');

                    // Check stock status of all selected variations
                    var hasOutOfStock = false;
                    var outOfStockItems = [];

                    // Check all composite components
                    $form.find('.component').each(function () {
                        var $component = $(this);
                        var componentTitle = $component.find('.component_title, .component_title_wrapper .component_title').text().trim() || 'Component';

                        // Check selected dropdown option
                        var $selectedOption = $component.find('select.component_options_select option:selected');
                        if ($selectedOption.length && $selectedOption.hasClass('lfa-out-of-stock-option')) {
                            hasOutOfStock = true;
                            outOfStockItems.push(componentTitle);
                            console.log('Out of stock option selected:', componentTitle);
                        }

                        // Check selected radio/thumbnail option
                        var $selectedInput = $component.find('input[type="radio"]:checked');
                        if ($selectedInput.length && $selectedInput.closest('.lfa-out-of-stock-item').length) {
                            hasOutOfStock = true;
                            if (outOfStockItems.indexOf(componentTitle) === -1) {
                                outOfStockItems.push(componentTitle);
                            }
                            console.log('Out of stock item selected:', componentTitle);
                        }

                        // Check if selected variation is out of stock
                        var $selectedVariation = $component.find('.single_variation_wrap');
                        if ($selectedVariation.length) {
                            // Check for out of stock message
                            var $stockInfo = $selectedVariation.find('.stock');
                            if ($stockInfo.length && $stockInfo.hasClass('out-of-stock')) {
                                hasOutOfStock = true;
                                if (outOfStockItems.indexOf(componentTitle) === -1) {
                                    outOfStockItems.push(componentTitle);
                                }
                                console.log('Out of stock component found:', componentTitle);
                            }
                        }

                        // Also check variation data attributes
                        var $variationId = $component.find('input[name*="variation_id"]');
                        if ($variationId.length && $variationId.val()) {
                            var variationData = $component.find('.variations_form').data('product_variations');
                            if (variationData) {
                                var selectedVariation = variationData.find(function (v) {
                                    return v.variation_id == $variationId.val();
                                });

                                if (selectedVariation && !selectedVariation.is_in_stock) {
                                    hasOutOfStock = true;
                                    if (outOfStockItems.indexOf(componentTitle) === -1) {
                                        outOfStockItems.push(componentTitle);
                                    }
                                    console.log('Stock check failed for:', componentTitle);
                                }
                            }
                        }
                    });

                    // If any component is out of stock, show error and prevent add to cart
                    if (hasOutOfStock) {
                        var errorMessage = 'Sorry, the following item(s) are out of stock: ' + outOfStockItems.join(', ') + '. Please select different options.';

                        // Display error message
                        if ($('.lfa-composite-stock-error').length) {
                            $('.lfa-composite-stock-error').html(errorMessage).show();
                        } else {
                            $('<div class="lfa-composite-stock-error" style="color: #e2401c; background: #fff3f3; padding: 12px 16px; margin: 12px 0; border: 1px solid #e2401c; border-radius: 4px; font-size: 14px;">' + errorMessage + '</div>')
                                .insertBefore($actionsWrapper);
                        }

                        console.log('Add to cart prevented - out of stock items detected');
                        return false;
                    }

                    // Remove any existing error messages
                    $('.lfa-composite-stock-error').remove();

                    // All items in stock, proceed with add to cart
                    // Try to trigger the original composite button click
                    if ($hiddenBtn.length) {
                        console.log('Triggering hidden button');
                        $hiddenBtn.trigger('click');
                    } else {
                        // If no default button found, submit the form
                        console.log('Submitting form directly');
                        $form.submit();
                    }
                });
            });
        }

        // Initialize composite customization with multiple attempts and timing
        setTimeout(customizeCompositeProduct, 100);
        setTimeout(customizeCompositeProduct, 500);
        setTimeout(customizeCompositeProduct, 1000);
        setTimeout(customizeCompositeProduct, 1500);
        setTimeout(customizeCompositeProduct, 2000);
        setTimeout(customizeCompositeProduct, 3000);
        setTimeout(customizeCompositeProduct, 5000);

        // Re-run after composite is initialized
        $(document).on('wc-composite-initializing wc-composite-initialized', function () {
            console.log('Composite initialized event triggered');
            setTimeout(customizeCompositeProduct, 100);
            setTimeout(customizeCompositeProduct, 500);
            setTimeout(customizeCompositeProduct, 1000);
        });

        // Watch for composite form being added to DOM
        if (typeof MutationObserver !== 'undefined') {
            var observer = new MutationObserver(function (mutations) {
                mutations.forEach(function (mutation) {
                    if (mutation.addedNodes.length) {
                        // Check if any added node contains composite form
                        mutation.addedNodes.forEach(function (node) {
                            if (node.nodeType === 1) { // Element node
                                if ($(node).is('.composite_form, form[data-product_type="composite"]') ||
                                    $(node).find('.composite_form, form[data-product_type="composite"]').length) {
                                    console.log('Composite form detected in DOM, customizing...');
                                    setTimeout(customizeCompositeProduct, 100);
                                }
                            }
                        });
                    }
                });
            });

            // Start observing the product attributes section
            var targetNode = document.querySelector('.lfa-product-attributes');
            if (targetNode) {
                observer.observe(targetNode, {
                    childList: true,
                    subtree: true
                });
            }
        }
    });

})(jQuery);
