(function ($) {
    'use strict';

    // Wait for DOM to be ready
    $(document).ready(function () {

        // Wait a bit for WooCommerce shortcode to render
        setTimeout(function () {
            var slickSlider = $('#lfa-featured-slider .products');

            // Check if slider element exists
            if (slickSlider.length === 0) {
                // Try alternative selectors
                var altSlider = $('#lfa-featured-slider ul.products, #lfa-featured-slider .woocommerce ul.products');

                if (altSlider.length > 0) {
                    slickSlider = altSlider;
                } else {
                    return;
                }
            }

            // Initialize the Slick Slider - simple configuration
            slickSlider.slick({
                slidesToShow: 4,
                slidesToScroll: 1,
                autoplay: true,
                autoplaySpeed: 3000,
                speed: 500,
                infinite: true,
                pauseOnHover: true,
                pauseOnFocus: false,
                arrows: false,
                dots: false,
                swipe: true,
                touchMove: true,
                draggable: true,
                accessibility: true,
                lazyLoad: 'ondemand',
                variableWidth: false,
                centerMode: false,
                cssEase: 'ease',
                fade: false,
                responsive: [
                    {
                        breakpoint: 1024,
                        settings: {
                            slidesToShow: 3,
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
                        breakpoint: 480,
                        settings: {
                            slidesToShow: 1,
                            slidesToScroll: 1
                        }
                    }
                ]
            });

            // Force refresh after initialization to fix white content issue
            setTimeout(function () {
                slickSlider.slick('refresh');
            }, 500);
        }, 1000); // Wait 1 second for WooCommerce to render

        // Shop by Color Slider
        setTimeout(function () {
            var colorSection = $('#lfa-bycolor-slider');

            if (colorSection.length === 0) {
                return;
            }

            // Function to initialize slider for a specific tabpanel
            function initializeColorSlider($tabpanel) {
                var $products = $tabpanel.find('.products');

                // Try alternative selectors if products not found
                if ($products.length === 0) {
                    $products = $tabpanel.find('ul.products, .woocommerce ul.products');
                }

                // Check if products exist
                var $noProductsMsg = $tabpanel.find('.lfa-no-products');
                var productItems = $tabpanel.find('.products li.product, .products .product');

                if ($products.length === 0 || productItems.length === 0) {
                    // No products found, show message
                    if ($noProductsMsg.length > 0) {
                        $noProductsMsg.show();
                    }
                    return null;
                } else {
                    // Products found, hide message
                    if ($noProductsMsg.length > 0) {
                        $noProductsMsg.hide();
                    }
                }

                // Check if slider is already initialized
                if ($products.hasClass('slick-initialized')) {
                    $products.slick('refresh');
                    return $products;
                }

                // Initialize the Slick Slider - simple configuration
                $products.slick({
                    slidesToShow: 4,
                    slidesToScroll: 1,
                    autoplay: true,
                    autoplaySpeed: 3000,
                    speed: 500,
                    infinite: true,
                    pauseOnHover: true,
                    pauseOnFocus: false,
                    arrows: false,
                    dots: false,
                    swipe: true,
                    touchMove: true,
                    draggable: true,
                    accessibility: true,
                    lazyLoad: 'ondemand',
                    variableWidth: false,
                    centerMode: false,
                    cssEase: 'ease',
                    fade: false,
                    responsive: [
                        {
                            breakpoint: 1024,
                            settings: {
                                slidesToShow: 3,
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
                            breakpoint: 480,
                            settings: {
                                slidesToShow: 1,
                                slidesToScroll: 1
                            }
                        }
                    ]
                });

                // Force refresh after initialization
                setTimeout(function () {
                    $products.slick('refresh');
                }, 500);

                return $products;
            }

            // Initialize slider for the active tabpanel
            var $activeTabpanel = colorSection.find('.lfa-tabpanel.is-active');
            if ($activeTabpanel.length > 0) {
                // Check if products exist for active tab
                var $products = $activeTabpanel.find('.products');
                var productItems = $activeTabpanel.find('.products li.product, .products .product');
                var $noProductsMsg = $activeTabpanel.find('.lfa-no-products');

                if ($products.length === 0 || productItems.length === 0) {
                    if ($noProductsMsg.length > 0) {
                        $noProductsMsg.show();
                    }
                } else {
                    if ($noProductsMsg.length > 0) {
                        $noProductsMsg.hide();
                    }
                    initializeColorSlider($activeTabpanel);
                }
            }

            // Handle tab switching
            colorSection.on('click', '[data-color-tabs] .lfa-chip', function (e) {
                e.preventDefault();
                var $button = $(this);
                var tabValue = $button.data('tab');

                // Update active state of buttons with animation
                colorSection.find('[data-color-tabs] .lfa-chip').removeClass('is-active');
                $button.addClass('is-active');

                // Hide all tabpanels with fade out
                var $tabpanels = colorSection.find('.lfa-tabpanel');
                $tabpanels.removeClass('is-active').addClass('fade-out');

                // Find and show the matching tabpanel with fade in
                var $targetTabpanel = colorSection.find('.lfa-tabpanel[data-panel="' + tabValue + '"]');
                if ($targetTabpanel.length > 0) {
                    // Remove fade-out and add fade-in for animation
                    $targetTabpanel.removeClass('fade-out').addClass('fade-in');

                    setTimeout(function () {
                        $targetTabpanel.addClass('is-active').removeClass('fade-in');
                    }, 150);

                    // Destroy any existing slider in this tabpanel
                    var $existingSlider = $targetTabpanel.find('.products.slick-initialized');
                    if ($existingSlider.length > 0) {
                        $existingSlider.slick('unslick');
                    }

                    // Initialize slider for the new active tabpanel
                    setTimeout(function () {
                        var $products = $targetTabpanel.find('.products');
                        var productItems = $targetTabpanel.find('.products li.product, .products .product');
                        var $noProductsMsg = $targetTabpanel.find('.lfa-no-products');

                        if ($products.length === 0 || productItems.length === 0) {
                            if ($noProductsMsg.length > 0) {
                                $noProductsMsg.show();
                            }
                        } else {
                            if ($noProductsMsg.length > 0) {
                                $noProductsMsg.hide();
                            }
                            initializeColorSlider($targetTabpanel);
                        }
                    }, 200);
                }
            });

        }, 1000); // Wait 1 second for WooCommerce to render

        // Customer Reviews Slider
        setTimeout(function () {
            var reviewsSlider = $('#lfa-reviews-slider .lfa-reviews-slider');

            if (reviewsSlider.length === 0) {
                return;
            }

            // Initialize the Slick Slider with same settings as featured products but 3 items
            reviewsSlider.slick({
                slidesToShow: 3,
                slidesToScroll: 1,
                autoplay: true,
                autoplaySpeed: 5000,
                speed: 1000,
                infinite: true,
                pauseOnHover: false,
                pauseOnFocus: false,
                arrows: true,
                prevArrow: $('.lfa-reviews-prev'),
                nextArrow: $('.lfa-reviews-next'),
                dots: false,
                swipe: true,
                touchMove: true,
                accessibility: true,
                lazyLoad: 'ondemand',
                variableWidth: false,
                centerMode: false,
                cssEase: 'linear',
                fade: false,
                responsive: [
                    {
                        breakpoint: 1024,
                        settings: {
                            slidesToShow: 2,
                            slidesToScroll: 1,
                        }
                    },
                    {
                        breakpoint: 768,
                        settings: {
                            slidesToShow: 1,
                            slidesToScroll: 1,
                        }
                    }
                ]
            });

            // Force refresh after initialization
            setTimeout(function () {
                reviewsSlider.slick('refresh');
                updateReviewsSeparators();
            }, 500);

            // Function to update separators (remove border from last visible slide)
            function updateReviewsSeparators() {
                var slidesToShow = reviewsSlider.slick('slickGetOption', 'slidesToShow') || 3;
                var currentSlide = reviewsSlider.slick('slickCurrentSlide') || 0;

                // Reset all borders
                reviewsSlider.find('.slick-slide .lfa-review').css('border-right', '1px solid #eee');

                // Find the last visible slide and remove its border
                var $activeSlides = reviewsSlider.find('.slick-slide.slick-active');
                if ($activeSlides.length > 0) {
                    // Get the last active slide
                    var $lastActive = $activeSlides.last();
                    $lastActive.find('.lfa-review').css('border-right', 'none');
                }
            }

            // Update separators on slide change
            reviewsSlider.on('afterChange', function (event, slick, currentSlide) {
                updateReviewsSeparators();
            });

            // Update separators on window resize (responsive breakpoints)
            $(window).on('resize', function () {
                setTimeout(updateReviewsSeparators, 100);
            });

        }, 500);

        // Cart Drawer Featured Products Slider
        window.initializeCartDrawerSlider = function () {
            setTimeout(function () {
                var cartSlider = $('#lfa-cart-featured-slider .lfa-cart-products-slider');

                // Check if slider element exists
                if (cartSlider.length === 0) {
                    return;
                }

                // Check if slider is already initialized
                if (cartSlider.hasClass('slick-initialized')) {
                    cartSlider.slick('unslick');
                }

                // Get container width to ensure proper slide sizing
                var containerWidth = cartSlider.closest('.lfa-cart-featured-products-wrapper').width() || cartSlider.parent().width();
                
                // Initialize the Slick Slider for cart drawer (single slide, full width)
                cartSlider.slick({
                    slidesToShow: 1,
                    slidesToScroll: 1,
                    autoplay: false,
                    speed: 300,
                    infinite: false,
                    pauseOnHover: true,
                    pauseOnFocus: false,
                    arrows: true,
                    prevArrow: $('.lfa-cart-slider-prev'),
                    nextArrow: $('.lfa-cart-slider-next'),
                    dots: false,
                    swipe: true,
                    touchMove: true,
                    draggable: true,
                    accessibility: true,
                    variableWidth: false,
                    centerMode: false,
                    cssEase: 'ease',
                    fade: false,
                    adaptiveHeight: true,
                    responsive: false
                });
                
                // Force slide width after initialization
                setTimeout(function() {
                    cartSlider.find('.slick-slide').each(function() {
                        $(this).css({
                            'width': containerWidth + 'px',
                            'min-width': containerWidth + 'px',
                            'max-width': containerWidth + 'px'
                        });
                    });
                    cartSlider.slick('setPosition');
                }, 100);

                // Handle variation selection for variable products
                $(document).off('change', '.lfa-cart-variation-select').on('change', '.lfa-cart-variation-select', function() {
                    var $select = $(this);
                    var $form = $select.closest('.lfa-cart-product-form');
                    var productId = $form.data('product-id');
                    var $btn = $form.find('.lfa-cart-add-to-cart-btn');
                    var $info = $form.find('.lfa-cart-product-variation-info');
                    
                    // Collect all attribute values using the name attribute (more reliable)
                    var attributes = {};
                    $form.find('.lfa-cart-variation-select').each(function() {
                        var $select = $(this);
                        var name = $select.attr('name'); // Use name attribute instead of data attribute
                        var value = $select.val();
                        if (name && value) {
                            attributes[name] = value;
                        }
                    });
                    
                    // Check if all attributes are selected
                    var allSelected = true;
                    $form.find('.lfa-cart-variation-select').each(function() {
                        if (!$(this).val()) {
                            allSelected = false;
                            return false;
                        }
                    });
                    
                    if (allSelected && Object.keys(attributes).length > 0) {
                        // Find matching variation via AJAX
                        $.ajax({
                            url: (typeof LFA !== 'undefined' && LFA.ajaxUrl) ? LFA.ajaxUrl : ajaxurl,
                            type: 'POST',
                            data: {
                                action: 'lfa_find_variation',
                                product_id: productId,
                                attributes: attributes,
                                nonce: (typeof LFA !== 'undefined' && LFA.nonce) ? LFA.nonce : ''
                            },
                            success: function(response) {
                                if (response.success && response.data.variation_id) {
                                    $form.data('variation-id', response.data.variation_id);
                                    $btn.prop('disabled', false);
                                    if (response.data.price_html) {
                                        $info.html(response.data.price_html).show();
                                    }
                                } else {
                                    $btn.prop('disabled', true);
                                    $info.hide();
                                }
                            },
                            error: function() {
                                $btn.prop('disabled', true);
                                $info.hide();
                            }
                        });
                    } else {
                        $btn.prop('disabled', true);
                        $info.hide();
                    }
                });
                
                // Handle add to cart for simple products
                $(document).off('click', '.lfa-cart-add-to-cart-btn[data-product-id]').on('click', '.lfa-cart-add-to-cart-btn[data-product-id]', function(e) {
                    e.preventDefault();
                    var $btn = $(this);
                    var productId = $btn.data('product-id');
                    
                    if ($btn.prop('disabled')) {
                        return;
                    }
                    
                    $btn.prop('disabled', true).text('Adding...');
                    
                    $.ajax({
                        url: (typeof LFA !== 'undefined' && LFA.ajaxUrl) ? LFA.ajaxUrl : ajaxurl,
                        type: 'POST',
                        data: {
                            action: 'lfa_add_to_cart',
                            product_id: productId,
                            quantity: 1,
                            nonce: (typeof LFA !== 'undefined' && LFA.nonce) ? LFA.nonce : ''
                        },
                        success: function(response) {
                            if (response.success) {
                                // Update cart badge
                                if (typeof window.updateCartBadge === 'function') {
                                    window.updateCartBadge();
                                }
                                
                                // Remove product slide from slider
                                var $slide = $btn.closest('.lfa-cart-product-slide');
                                $slide.fadeOut(300, function() {
                                    $(this).remove();
                                    cartSlider.slick('refresh');
                                    
                                    // Reload cart drawer content without page reload
                                    if (typeof window.loadCartContent === 'function') {
                                        window.loadCartContent();
                                    } else if (typeof loadCartContent === 'function') {
                                        loadCartContent();
                                    }
                                });
                            } else {
                                alert(response.data.message || 'Error adding product to cart');
                                $btn.prop('disabled', false).text('ADD TO CART');
                            }
                        },
                        error: function() {
                            alert('Error adding product to cart');
                            $btn.prop('disabled', false).text('ADD TO CART');
                        }
                    });
                });
                
                // Handle add to cart for variable products
                $(document).off('submit', '.lfa-cart-product-form').on('submit', '.lfa-cart-product-form', function(e) {
                    e.preventDefault();
                    var $form = $(this);
                    var productId = $form.data('product-id');
                    var variationId = $form.data('variation-id');
                    var $btn = $form.find('.lfa-cart-add-to-cart-btn');
                    
                    if (!$btn.length || $btn.prop('disabled') || !variationId) {
                        return;
                    }
                    
                    // Collect all attribute values from the form using name attribute
                    var attributes = {};
                    $form.find('.lfa-cart-variation-select').each(function() {
                        var $select = $(this);
                        var name = $select.attr('name');
                        var value = $select.val();
                        if (name && value) {
                            // Ensure attribute name starts with 'attribute_'
                            if (name.indexOf('attribute_') === 0) {
                                attributes[name] = value;
                            } else {
                                attributes['attribute_' + name] = value;
                            }
                        }
                    });
                    
                    $btn.prop('disabled', true).text('Adding...');
                    
                    // Prepare data object
                    var ajaxData = {
                        action: 'lfa_add_to_cart',
                        product_id: productId,
                        variation_id: variationId,
                        quantity: 1,
                        nonce: (typeof LFA !== 'undefined' && LFA.nonce) ? LFA.nonce : ''
                    };
                    
                    // Add attributes to the data
                    $.extend(ajaxData, attributes);
                    
                    $.ajax({
                        url: (typeof LFA !== 'undefined' && LFA.ajaxUrl) ? LFA.ajaxUrl : ajaxurl,
                        type: 'POST',
                        data: ajaxData,
                        success: function(response) {
                            if (response.success) {
                                // Update cart badge
                                if (typeof window.updateCartBadge === 'function') {
                                    window.updateCartBadge();
                                }
                                
                                // Remove product slide from slider
                                var $slide = $form.closest('.lfa-cart-product-slide');
                                $slide.fadeOut(300, function() {
                                    $(this).remove();
                                    cartSlider.slick('refresh');
                                    
                                    // Reload cart drawer content without page reload
                                    if (typeof window.loadCartContent === 'function') {
                                        window.loadCartContent();
                                    } else if (typeof loadCartContent === 'function') {
                                        loadCartContent();
                                    }
                                });
                            } else {
                                alert(response.data.message || 'Error adding product to cart');
                                $btn.prop('disabled', false).text('ADD TO CART');
                            }
                        },
                        error: function() {
                            alert('Error adding product to cart');
                            $btn.prop('disabled', false).text('ADD TO CART');
                        }
                    });
                });
            }, 500); // Wait for content to render
        };

        // Quick View Image Slider
        // Function to initialize quick view slider
        window.initializeQuickViewSlider = function ($wrapper) {
            if (!$wrapper || !$wrapper.length) {
                $wrapper = $('.lfa-quick-view-wrapper');
            }

            if (!$wrapper.length) {
                return;
            }

            var $slider = $wrapper.find('.lfa-quick-view-slider');

            if ($slider.length && typeof $.fn.slick !== 'undefined') {
                // Destroy any existing slider first
                if ($slider.hasClass('slick-initialized')) {
                    try {
                        $slider.slick('unslick');
                    } catch (e) {
                        // Ignore errors
                    }
                }

                // Get the nav container
                var $navContainer = $wrapper.find('.lfa-quick-view-slider-nav');

                // Initialize slider
                $slider.slick({
                    slidesToShow: 1,
                    slidesToScroll: 1,
                    arrows: true,
                    prevArrow: '<button type="button" class="lfa-quick-view-slider-nav-prev" aria-label="Previous"><svg class="rotate-90" xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#000000"><path d="M450-635.69 317.08-502.77q-8.31 8.31-20.89 8.5-12.57.19-21.27-8.5-8.69-8.69-8.69-21.08 0-12.38 8.69-21.07l179.77-179.77q10.85-10.85 25.31-10.85 14.46 0 25.31 10.85l179.77 179.77q8.3 8.3 8.5 20.88.19 12.58-8.5 21.27-8.7 8.69-21.08 8.69-12.38 0-21.08-8.69L510-635.69v351.84q0 12.77-8.62 21.39-8.61 8.61-21.38 8.61t-21.38-8.61q-8.62-8.62-8.62-21.39v-351.84Z"></path></svg></button>',
                    nextArrow: '<button type="button" class="lfa-quick-view-slider-nav-next" aria-label="Next"><svg class="rotate-90" xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#000000"><path d="M450-635.69 317.08-502.77q-8.31 8.31-20.89 8.5-12.57.19-21.27-8.5-8.69-8.69-8.69-21.08 0-12.38 8.69-21.07l179.77-179.77q10.85-10.85 25.31-10.85 14.46 0 25.31 10.85l179.77 179.77q8.3 8.3 8.5 20.88.19 12.58-8.5 21.27-8.7 8.69-21.08 8.69-12.38 0-21.08-8.69L510-635.69v351.84q0 12.77-8.62 21.39-8.61 8.61-21.38 8.61t-21.38-8.61q-8.62-8.62-8.62-21.39v-351.84Z"></path></svg></button>',
                    appendArrows: $navContainer.length ? $navContainer : false,
                    fade: false,
                    dots: false,
                    infinite: true,
                    adaptiveHeight: true,
                    speed: 300,
                    useCSS: true,
                    cssEase: 'ease',
                    waitForAnimate: true,
                    touchMove: true,
                    swipe: true,
                    draggable: true
                });
            }
        };
    });
})(jQuery);




