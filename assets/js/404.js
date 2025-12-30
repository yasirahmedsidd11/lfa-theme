(function ($) {
    'use strict';

    // Wait for DOM to be ready
    $(document).ready(function () {

        // Wait a bit for WooCommerce shortcode to render
        setTimeout(function () {
            var error404Slider = $('#lfa-404-featured-slider .products');

            // Check if slider element exists
            if (error404Slider.length === 0) {
                // Try alternative selectors
                var altError404Slider = $('#lfa-404-featured-slider ul.products, #lfa-404-featured-slider .woocommerce ul.products');

                if (altError404Slider.length > 0) {
                    error404Slider = altError404Slider;
                } else {
                    return;
                }
            }

            // Check if slider is already initialized
            if (error404Slider.hasClass('slick-initialized')) {
                error404Slider.slick('unslick');
            }

            // Initialize the Slick Slider
            error404Slider.slick({
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
                            slidesToScroll: 1,
                            autoplay: true,
                            autoplaySpeed: 3000
                        }
                    },
                    {
                        breakpoint: 768,
                        settings: {
                            slidesToShow: 2,
                            slidesToScroll: 1,
                            autoplay: true,
                            autoplaySpeed: 3000
                        }
                    },
                    {
                        breakpoint: 480,
                        settings: {
                            slidesToShow: 1,
                            slidesToScroll: 1,
                            autoplay: true,
                            autoplaySpeed: 3000
                        }
                    }
                ]
            });

            // Force refresh after initialization to fix white content issue
            setTimeout(function () {
                error404Slider.slick('refresh');
                // Ensure autoplay is active after refresh (sometimes refresh disables it)
                if (!error404Slider.slick('slickGetOption', 'autoplay')) {
                    error404Slider.slick('slickSetOption', 'autoplay', true, true);
                }
            }, 500);
        }, 1000); // Wait 1 second for WooCommerce to render
    });
})(jQuery);

