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
    });
})(jQuery);




