/**
 * Single Product Page JavaScript
 * Handles product image slider and variation forms
 */

(function($) {
    'use strict';
    
    $(document).ready(function() {
        // Initialize product image slider
        var $slider = $('#lfa-product-slider');
        
        if ($slider.length && typeof $.fn.slick !== 'undefined') {
            // Check if slider is already initialized
            if (!$slider.hasClass('slick-initialized')) {
                $slider.slick({
                    slidesToShow: 2,
                    slidesToScroll: 2,
                    infinite: true,
                    arrows: true,
                    dots: false,
                    autoplay: false,
                    speed: 300,
                    prevArrow: '<button type="button" class="slick-prev" aria-label="Previous"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M15 18l-6-6 6-6"/></svg></button>',
                    nextArrow: '<button type="button" class="slick-next" aria-label="Next"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 18l6-6-6-6"/></svg></button>',
                    responsive: [
                        {
                            breakpoint: 768,
                            settings: {
                                slidesToShow: 1,
                                slidesToScroll: 1
                            }
                        }
                    ]
                });
            }
        }
        
        // Convert variation selects to radio buttons
        function convertSelectsToRadios() {
            $('.lfa-product-attributes .variations select').each(function() {
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
                $select.find('option').each(function() {
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
            $radioWrapper.find('.lfa-variation-radio').each(function() {
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
        $(document).on('woocommerce_update_variation_values', function() {
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
            
            // Create add to cart row wrapper
            var $addToCartRow = $('<div class="lfa-add-to-cart-row"></div>');
            
            // Update button content
            $button.html('<span class="lfa-cart-btn-text">ADD TO CART</span><span class="lfa-cart-btn-price">' + productPrice + '</span>');
            
            // Create wishlist button
            var $wishlistBtn = $('<button type="button" class="lfa-wishlist-btn">' +
                '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">' +
                '<path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path>' +
                '</svg></button>');
            
            // Get product ID for buy now link
            var productId = $form.data('product_id');
            var checkoutUrl = typeof wc_add_to_cart_params !== 'undefined' ? wc_add_to_cart_params.wc_ajax_url.replace('wc-ajax=%%endpoint%%', '') : '/checkout/';
            
            // Create buy it now button
            var $buyNowBtn = $('<a href="' + checkoutUrl + '?add-to-cart=' + productId + '" class="lfa-buy-now-btn">BUY IT NOW</a>');
            
            // Wrap button and wishlist in row
            $button.wrap($addToCartRow);
            $button.after($wishlistBtn);
            
            // Add buy now button after the row
            $button.closest('.lfa-add-to-cart-row').after($buyNowBtn);
            
            // Mark as customized
            $addToCartWrapper.addClass('lfa-customized');
        }
        
        // Update add to cart button price when variation changes
        $(document).on('found_variation', function(event, variation) {
            // Update the price in the button
            if (variation.price_html) {
                $('.lfa-cart-btn-price').html(variation.price_html);
            }
            
            // Customize the button if not already done
            setTimeout(customizeVariableAddToCart, 100);
            
            // Update buy now link with variation
            if (variation.variation_id) {
                var $form = $(event.target);
                var productId = $form.data('product_id');
                var checkoutUrl = window.location.origin + '/checkout/';
                var buyNowUrl = checkoutUrl + '?add-to-cart=' + productId + '&variation_id=' + variation.variation_id;
                
                // Add variation attributes to URL
                $form.find('.variations select').each(function() {
                    var name = $(this).attr('name');
                    var value = $(this).val();
                    if (name && value) {
                        buyNowUrl += '&' + name + '=' + encodeURIComponent(value);
                    }
                });
                
                $form.find('.lfa-buy-now-btn').attr('href', buyNowUrl);
            }
        });
        
        $(document).on('reset_data', function() {
            var $price = $('.lfa-product-title-price-row .lfa-product-price').html();
            if ($price) {
                $('.lfa-cart-btn-price').html($price);
            }
        });
        
        // Initialize variable button customization
        setTimeout(customizeVariableAddToCart, 200);
        
        // Watch for variation form changes (radio buttons)
        $(document).on('change', '.lfa-product-attributes .lfa-variation-radio', function() {
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
        
        // Wishlist button toggle
        $(document).on('click', '.lfa-wishlist-btn', function(e) {
            e.preventDefault();
            $(this).toggleClass('active');
            // You can add AJAX call here for wishlist functionality
        });
        
        // =============================================
        // Section 2: Why You Need This - Tabs
        // =============================================
        
        // Tab switching
        $(document).on('click', '.lfa-why-tab', function() {
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
    });
    
})(jQuery);
