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

            // Initialize the Slick Slider with smooth autoplay
            slickSlider.slick({
                slidesToShow: 4,
                slidesToScroll: 1,
                autoplay: true,
                autoplaySpeed: 3000, // 3 seconds between slides
                speed: 1000, // Transition speed
                infinite: true,
                pauseOnHover: true,
                pauseOnFocus: true,
                arrows: false,
                dots: false,
                swipe: true,
                touchMove: true,
                accessibility: true,
                lazyLoad: 'ondemand',
                responsive: [
                    {
                        breakpoint: 1024,
                        settings: {
                            slidesToShow: 3,
                            slidesToScroll: 1,
                            autoplaySpeed: 3500
                        }
                    },
                    {
                        breakpoint: 768,
                        settings: {
                            slidesToShow: 2,
                            slidesToScroll: 1,
                            autoplaySpeed: 4000
                        }
                    },
                    {
                        breakpoint: 480,
                        settings: {
                            slidesToShow: 1,
                            slidesToScroll: 1,
                            autoplaySpeed: 4500
                        }
                    }
                ]
            });

            // Force refresh after initialization to fix white content issue
            setTimeout(function () {
                slickSlider.slick('refresh');
            }, 500);

            // Add custom pause/resume functionality for better control
            slickSlider.on('mouseenter', function () {
                $(this).slick('slickPause');
            });

            slickSlider.on('mouseleave', function () {
                $(this).slick('slickPlay');
            });

            // Handle touch/drag events
            slickSlider.on('touchstart', function () {
                $(this).slick('slickPause');
            });

            slickSlider.on('touchend', function () {
                var $this = $(this);
                setTimeout(function () {
                    $this.slick('slickPlay');
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
                    slickSlider.slick('slickPlay');
                    $(this).removeClass('paused').text('Pause');
                } else {
                    slickSlider.slick('slickPause');
                    $(this).addClass('paused').text('Play');
                }
            });
        }, 1000); // Wait 1 second for WooCommerce to render
    });
})(jQuery);




