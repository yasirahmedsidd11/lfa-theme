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

            // Initialize the Slick Slider with smooth continuous animation
            slickSlider.slick({
                slidesToShow: 4,
                slidesToScroll: 1,
                autoplay: false, // Disable default autoplay, we'll use custom
                autoplaySpeed: 0,
                speed: 1000, // Smooth transition speed (5 seconds)
                infinite: true,
                pauseOnHover: false, // Disable default hover pause, we'll use custom
                pauseOnFocus: false,
                arrows: false,
                dots: false,
                swipe: true,
                touchMove: true,
                accessibility: true,
                lazyLoad: 'ondemand',
                variableWidth: false,
                centerMode: false,
                cssEase: 'linear', // Linear animation for smooth continuous movement
                fade: false, // Ensure slide transition, not fade
                responsive: [
                    {
                        breakpoint: 1024,
                        settings: {
                            slidesToShow: 3,
                            slidesToScroll: 1,
                            autoplaySpeed: 1000
                        }
                    },
                    {
                        breakpoint: 768,
                        settings: {
                            slidesToShow: 2,
                            slidesToScroll: 1,
                            autoplaySpeed: 1000
                        }
                    },
                    {
                        breakpoint: 480,
                        settings: {
                            slidesToShow: 1,
                            slidesToScroll: 1,
                            autoplaySpeed: 1000
                        }
                    }
                ]
            });

            // Force refresh after initialization to fix white content issue
            setTimeout(function () {
                slickSlider.slick('refresh');
            }, 500);

            // Custom continuous smooth animation
            var isAnimating = false;
            var isHovered = false;
            var animationSpeed = 1000; // 5 seconds for smooth transition
            var animationInterval = null;

            function startContinuousAnimation() {
                if (isAnimating || isHovered) return;
                isAnimating = true;

                // Clear any existing interval
                if (animationInterval) {
                    clearInterval(animationInterval);
                }

                // Use setInterval for continuous movement
                animationInterval = setInterval(function () {
                    if (!isAnimating || isHovered) {
                        clearInterval(animationInterval);
                        animationInterval = null;
                        return;
                    }
                    slickSlider.slick('slickNext');
                }, animationSpeed);
            }

            function stopContinuousAnimation() {
                isAnimating = false;
                if (animationInterval) {
                    clearInterval(animationInterval);
                    animationInterval = null;
                }
            }

            // Start the continuous animation after a short delay to prevent flicker
            setTimeout(function () {
                startContinuousAnimation();
            }, 1000);

            // Add custom pause/resume functionality for better control
            slickSlider.on('mouseenter', function () {
                isHovered = true;
                stopContinuousAnimation();
            });

            slickSlider.on('mouseleave', function () {
                isHovered = false;
                startContinuousAnimation();
            });

            // Handle touch/drag events
            slickSlider.on('touchstart', function () {
                isHovered = true;
                stopContinuousAnimation();
            });

            slickSlider.on('touchend', function () {
                var $this = $(this);
                setTimeout(function () {
                    isHovered = false;
                    startContinuousAnimation();
                }, 1000); // Resume after 1 second
            });

            // Optional: Add keyboard navigation
            $(document).on('keydown', function (e) {
                if (e.keyCode === 37) { // Left arrow
                    slickSlider.slick('slickPrev');
                } else if (e.keyCode === 39) { // Right arrow
                    slickSlider.slick('slickNext');
                }
            });

            // Optional: Add play/pause button functionality
            $('.lfa-slider-controls .play-pause').on('click', function () {
                if ($(this).hasClass('paused')) {
                    startContinuousAnimation();
                    $(this).removeClass('paused').text('Pause');
                } else {
                    stopContinuousAnimation();
                    $(this).addClass('paused').text('Play');
                }
            });
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

                // Initialize the Slick Slider with same settings as featured products
                $products.slick({
                    slidesToShow: 4,
                    slidesToScroll: 1,
                    autoplay: false,
                    autoplaySpeed: 0,
                    speed: 1000,
                    infinite: true,
                    pauseOnHover: false,
                    pauseOnFocus: false,
                    arrows: false,
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
                                slidesToShow: 3,
                                slidesToScroll: 1,
                                autoplaySpeed: 1000
                            }
                        },
                        {
                            breakpoint: 768,
                            settings: {
                                slidesToShow: 2,
                                slidesToScroll: 1,
                                autoplaySpeed: 1000
                            }
                        },
                        {
                            breakpoint: 480,
                            settings: {
                                slidesToShow: 1,
                                slidesToScroll: 1,
                                autoplaySpeed: 1000
                            }
                        }
                    ]
                });

                // Force refresh after initialization
                setTimeout(function () {
                    $products.slick('refresh');
                }, 500);

                // Custom continuous smooth animation for color slider
                var isAnimating = false;
                var isHovered = false;
                var animationSpeed = 1000;
                var animationInterval = null;

                function startContinuousAnimation() {
                    if (isAnimating || isHovered) return;
                    isAnimating = true;

                    if (animationInterval) {
                        clearInterval(animationInterval);
                    }

                    animationInterval = setInterval(function () {
                        if (!isAnimating || isHovered) {
                            clearInterval(animationInterval);
                            animationInterval = null;
                            return;
                        }
                        $products.slick('slickNext');
                    }, animationSpeed);
                }

                function stopContinuousAnimation() {
                    isAnimating = false;
                    if (animationInterval) {
                        clearInterval(animationInterval);
                        animationInterval = null;
                    }
                }

                setTimeout(function () {
                    startContinuousAnimation();
                }, 1000);

                $products.on('mouseenter', function () {
                    isHovered = true;
                    stopContinuousAnimation();
                });

                $products.on('mouseleave', function () {
                    isHovered = false;
                    startContinuousAnimation();
                });

                $products.on('touchstart', function () {
                    isHovered = true;
                    stopContinuousAnimation();
                });

                $products.on('touchend', function () {
                    var $this = $(this);
                    setTimeout(function () {
                        isHovered = false;
                        startContinuousAnimation();
                    }, 1000);
                });

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
    });
})(jQuery);




