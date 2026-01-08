/**
 * Single Product Page JavaScript
 * Handles product image slider and variation forms
 */

(function ($) {
    'use strict';

    $(document).ready(function () {
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

            // Create add to cart row wrapper
            var $addToCartRow = $('<div class="lfa-add-to-cart-row"></div>');

            // Update button content
            $button.html('<span class="lfa-cart-btn-text">ADD TO CART</span><span class="lfa-cart-btn-price">' + productPrice + '</span>');

            // Create wishlist button
            var $wishlistBtn = $('<button type="button" class="lfa-wishlist-btn" data-product-id="' + productId + '" aria-label="Add to wishlist">' +
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
        $(document).on('found_variation', function (event, variation) {
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
                $form.find('.variations select').each(function () {
                    var name = $(this).attr('name');
                    var value = $(this).val();
                    if (name && value) {
                        buyNowUrl += '&' + name + '=' + encodeURIComponent(value);
                    }
                });

                $form.find('.lfa-buy-now-btn').attr('href', buyNowUrl);
            }
        });

        $(document).on('reset_data', function () {
            var $price = $('.lfa-product-title-price-row .lfa-product-price').html();
            if ($price) {
                $('.lfa-cart-btn-price').html($price);
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
            var $btn = $(this);
            var productId = $btn.data('product-id');

            if (!productId) {
                return;
            }

            // Check if TI Wishlist is available
            if (typeof tinvwl !== 'undefined' && typeof tinvwl.add !== 'undefined') {
                // Use TI Wishlist AJAX
                tinvwl.add({
                    product_id: productId,
                    product_type: 'simple'
                }, function (response) {
                    if (response && response.status) {
                        $btn.addClass('active');
                        // Update icon fill
                        $btn.find('svg').css('fill', '#000000');
                    }
                });
            } else {
                // Fallback: Try to find and click TI Wishlist button if it exists on page
                var $tinvwlBtn = $('.tinvwl_add_to_wishlist_button[data-tinv-wl-list]');
                if ($tinvwlBtn.length) {
                    $tinvwlBtn.first().trigger('click');
                    // Wait a bit and check if it was added
                    setTimeout(function () {
                        if ($tinvwlBtn.hasClass('tinvwl-product-in-list')) {
                            $btn.addClass('active');
                            $btn.find('svg').css('fill', '#000000');
                        }
                    }, 500);
                } else {
                    // Last resort: Use AJAX to add via TI Wishlist endpoint
                    if (typeof wc_add_to_cart_params !== 'undefined') {
                        $.ajax({
                            url: wc_add_to_cart_params.wc_ajax_url.toString().replace('%%endpoint%%', 'tinvwl'),
                            type: 'POST',
                            data: {
                                product_id: productId,
                                product_type: 'simple',
                                action: 'tinvwl_addtowishlist'
                            },
                            success: function (response) {
                                if (response && (response.status === 'success' || response.status === true)) {
                                    $btn.addClass('active');
                                    $btn.find('svg').css('fill', '#000000');
                                }
                            }
                        });
                    } else {
                        // Simple toggle as fallback
                        $btn.toggleClass('active');
                        if ($btn.hasClass('active')) {
                            $btn.find('svg').css('fill', '#000000');
                        } else {
                            $btn.find('svg').css('fill', 'none');
                        }
                    }
                }
            }
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
    });

})(jQuery);
