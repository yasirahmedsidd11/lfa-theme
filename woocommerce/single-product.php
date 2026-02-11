<?php
/**
 * The Template for displaying all single products
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/single-product.php.
 *
 * @see https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 1.6.4
 */

defined('ABSPATH') || exit;

get_header('shop');

/**
 * Hook: woocommerce_before_main_content.
 *
 * @hooked woocommerce_output_content_wrapper - 10 (outputs opening divs for the content)
 * @hooked woocommerce_breadcrumb - 20
 */
do_action('woocommerce_before_main_content');

while (have_posts()) {
    the_post();
    global $product;
    ?>

    <div id="product-<?php the_ID(); ?>" <?php wc_product_class('lfa-single-product', $product); ?>>

        <!-- Skeleton Loading for Section 1 -->
        <div class="lfa-product-skeleton" id="lfa-product-skeleton">
            <section class="lfa-product-section-1">
                <div class="container">
                    <div class="lfa-product-main-wrapper">
                        <!-- Left Column: Product Images Skeleton -->
                        <div class="lfa-product-images-column">
                            <div class="lfa-skeleton-image">
                                <div class="skeleton-shimmer"></div>
                            </div>
                        </div>
                        <!-- Right Column: Product Info Skeleton -->
                        <div class="lfa-product-info-column">
                            <div class="lfa-skeleton-title">
                                <div class="skeleton-shimmer"></div>
                            </div>
                            <div class="lfa-skeleton-price">
                                <div class="skeleton-shimmer"></div>
                            </div>
                            <div class="lfa-skeleton-rating">
                                <div class="skeleton-shimmer"></div>
                            </div>
                            <div class="lfa-skeleton-attributes">
                                <div class="skeleton-shimmer"></div>
                                <div class="skeleton-shimmer"></div>
                            </div>
                            <div class="lfa-skeleton-buttons">
                                <div class="skeleton-shimmer"></div>
                                <div class="skeleton-shimmer"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </div>

        <!-- Actual Content (hidden initially) -->
        <div class="lfa-product-content" id="lfa-product-content" style="display: none;">

        <!-- Section 1: Product Images and Info -->
        <section class="lfa-product-section-1">
            <div class="container">
                <div class="lfa-product-main-wrapper">

                    <!-- Left Column: Product Images (60%) -->
                    <div class="lfa-product-images-column">
                        <?php
                        // Get product gallery images
                        $attachment_ids = $product->get_gallery_image_ids();
                        $main_image_id = $product->get_image_id();

                        // Combine main image with gallery images
                        $all_images = array();
                        if ($main_image_id) {
                            $all_images[] = $main_image_id;
                        }
                        if (!empty($attachment_ids)) {
                            $all_images = array_merge($all_images, $attachment_ids);
                        }

                        if (!empty($all_images)) {
                            ?>
                            <div class="lfa-product-slider" id="lfa-product-slider">
                                <?php foreach ($all_images as $image_id): ?>
                                    <div class="lfa-product-slide">
                                        <?php echo wp_get_attachment_image($image_id, 'full', false, array('class' => 'lfa-product-image')); ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                            <?php
                        } else {
                            // Fallback: featured image or placeholder
                            echo wc_placeholder_img('full');
                        }
                        ?>
                    </div>

                    <!-- Right Column: Product Info (40%) -->
                    <div class="lfa-product-info-column">
                        <div class="lfa-product-info-wrapper">

                            <!-- Product Title and Price Row -->
                            <div class="lfa-product-title-price-row">
                                <h1 class="lfa-product-title"><?php the_title(); ?></h1>
                                <div class="lfa-product-price">
                                    <?php echo $product->get_price_html(); ?>
                                </div>
                            </div>

                            <!-- Rating and Reviews -->
                            <div class="lfa-product-rating-wrapper">
                                <?php
                                $review_count = $product->get_review_count();
                                $average = $product->get_average_rating();

                                if (wc_review_ratings_enabled()) {
                                    // Show stars (filled based on rating, or empty if no reviews)
                                    ?>
                                    <div class="lfa-product-stars">
                                        <?php
                                        for ($i = 1; $i <= 5; $i++) {
                                            if ($i <= $average) {
                                                echo '<span class="star filled">★</span>';
                                            } else {
                                                echo '<span class="star">★</span>';
                                            }
                                        }
                                        ?>
                                    </div>
                                    <?php
                                    if ($review_count > 0) {
                                        ?>
                                        <span class="lfa-reviews-count">
                                            <a
                                                href="#reviews"><?php printf(_n('%d Customer Review', '%d Customer Reviews', $review_count, 'livingfitapparel'), $review_count); ?></a>
                                        </span>
                                        <span class="lfa-reviews-separator">|</span>
                                        <span class="lfa-add-review">
                                            <a href="#reviews" class="lfa-add-review-link" id="lfa-add-review-link"><?php _e('Add a review', 'livingfitapparel'); ?></a>
                                        </span>
                                        <?php
                                    }
                                }
                                ?>
                            </div>

                            <!-- Variable Product Attributes -->
                            <?php if ($product->is_type('variable')): ?>
                                <div class="lfa-product-attributes">
                                    <?php
                                    woocommerce_variable_add_to_cart();
                                    ?>
                                </div>
                            <?php endif; ?>

                            <!-- Composite Product Add to Cart -->
                            <?php if ($product->is_type('composite')): ?>
                                <div class="lfa-product-attributes lfa-composite-product-container" data-product-type="composite" data-product-id="<?php echo esc_attr($product->get_id()); ?>">
                                    <?php
                                    // Composite products use WooCommerce action hooks
                                    // The plugin will hook into this to display its add to cart form
                                    do_action('woocommerce_composite_add_to_cart');
                                    ?>
                                </div>
                            <?php endif; ?>

                            <!-- Add to Cart Section -->
                            <?php if (!$product->is_type('variable') && !$product->is_type('composite')): ?>
                                <div class="lfa-product-add-to-cart-section">
                                    <form class="cart"
                                        action="<?php echo esc_url(apply_filters('woocommerce_add_to_cart_form_action', $product->get_permalink())); ?>"
                                        method="post" enctype='multipart/form-data'>
                                        <?php
                                        if (!$product->is_sold_individually()) {
                                            woocommerce_quantity_input(array(
                                                'min_value' => apply_filters('woocommerce_quantity_input_min', $product->get_min_purchase_quantity(), $product),
                                                'max_value' => apply_filters('woocommerce_quantity_input_max', $product->get_max_purchase_quantity(), $product),
                                                'input_value' => isset($_POST['quantity']) ? wc_stock_amount(wp_unslash($_POST['quantity'])) : $product->get_min_purchase_quantity(),
                                            ));
                                        }
                                        ?>

                                        <div class="lfa-add-to-cart-row">
                                            <button type="submit" name="add-to-cart"
                                                value="<?php echo esc_attr($product->get_id()); ?>"
                                                class="lfa-add-to-cart-btn single_add_to_cart_button button alt">
                                                <span
                                                    class="lfa-cart-btn-text"><?php _e('ADD TO CART', 'livingfitapparel'); ?></span>
                                                <span
                                                    class="lfa-cart-btn-price"><?php echo $product->get_price_html(); ?></span>
                                            </button>
                                            <button type="button" class="lfa-wishlist-btn"
                                                data-product-id="<?php echo esc_attr($product->get_id()); ?>"
                                                aria-label="<?php esc_attr_e('Add to wishlist', 'livingfitapparel'); ?>">
                                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none"
                                                    stroke="currentColor" stroke-width="1.5" stroke-linecap="round"
                                                    stroke-linejoin="round">
                                                    <path
                                                        d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z">
                                                    </path>
                                                </svg>
                                            </button>
                                        </div>

                                        <?php do_action('woocommerce_after_add_to_cart_button'); ?>
                                    </form>

                                    <!-- Buy It Now Button -->
                                    <?php if ($product->is_in_stock()): ?>
                                        <a href="<?php echo esc_url(wc_get_checkout_url() . '?add-to-cart=' . $product->get_id()); ?>"
                                            class="lfa-buy-now-btn">
                                            <?php _e('BUY IT NOW', 'livingfitapparel'); ?>
                                        </a>
                                    <?php else: ?>
                                        <span class="lfa-buy-now-btn lfa-buy-now-btn-disabled">
                                            <?php _e('BUY IT NOW', 'livingfitapparel'); ?>
                                        </span>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>

                            <!-- Short Description -->
                            <?php if ($product->get_short_description()): ?>
                                <div class="lfa-product-short-description">
                                    <?php echo apply_filters('woocommerce_short_description', $product->get_short_description()); ?>
                                </div>
                            <?php endif; ?>

                            <!-- Model Details -->
                            <?php
                            $model_details = function_exists('lfa_get_product_model_details') ? lfa_get_product_model_details() : '';
                            if ($model_details):
                                ?>
                                <div class="lfa-product-model-details">
                                    <p class="lfa-model-text"><?php echo esc_html($model_details); ?></p>
                                </div>
                            <?php endif; ?>

                            <!-- Care Instructions -->
                            <?php
                            $care_instructions = function_exists('lfa_get_product_care_instructions') ? lfa_get_product_care_instructions() : '';
                            if ($care_instructions):
                                ?>
                                <div class="lfa-product-care-instructions">
                                    <h3 class="lfa-care-title"><?php _e('Care Instructions:', 'livingfitapparel'); ?></h3>
                                    <div class="lfa-care-content">
                                        <?php echo wp_kses_post($care_instructions); ?>
                                    </div>
                                </div>
                            <?php endif; ?>

                        </div>
                    </div>

                </div>
            </div>
        </section>

        <?php
        // Get Why You Need This section data
        $why_left_image = function_exists('lfa_get_why_left_image') ? lfa_get_why_left_image() : '';
        $why_right_image = function_exists('lfa_get_why_right_image') ? lfa_get_why_right_image() : '';
        $why_length = function_exists('lfa_get_why_length') ? lfa_get_why_length() : '';
        $why_material = function_exists('lfa_get_why_material') ? lfa_get_why_material() : '';
        $size_chart_images = function_exists('lfa_get_size_chart_images') ? lfa_get_size_chart_images() : array();

        // Check if we have content for this section
        $has_why_images = $why_left_image || $why_right_image;
        $has_description = $why_length || $why_material || $product->get_short_description();
        $has_size_chart = !empty($size_chart_images);

        // Only show section if we have at least images
        if ($has_why_images):
            // Determine column count
            $show_description_tab = $has_description;
            $show_size_chart_tab = $has_size_chart;
            $has_tabs = $show_description_tab || $show_size_chart_tab;
            $column_class = $has_tabs ? 'three-columns' : 'two-columns';
            $section2_title = lfa_get('single_product.section2_title', __('Why you need this', 'livingfitapparel'));
            ?>

            <!-- Section 2: Why You Need This -->
            <section class="lfa-product-section-2">
                <div class="container">
                    <?php if (!empty($section2_title)): ?>
                        <h2 class="lfa-section-title lfa-section-title-left"><?php echo esc_html($section2_title); ?></h2>
                    <?php endif; ?>

                    <div class="lfa-why-wrapper <?php echo esc_attr($column_class); ?>">
                        <!-- Left Image -->
                        <?php if ($why_left_image): ?>
                            <div class="lfa-why-image lfa-why-image-left">
                                <?php echo wp_get_attachment_image($why_left_image, 'large', false, array('class' => 'lfa-why-img')); ?>
                            </div>
                        <?php endif; ?>

                        <!-- Middle Content (Tabs) -->
                        <?php if ($has_tabs): ?>
                            <div class="lfa-why-content">
                                <!-- Tabs -->
                                <div class="lfa-why-tabs">
                                    <?php if ($show_description_tab): ?>
                                        <button type="button" class="lfa-why-tab active"
                                            data-tab="description"><?php _e('DESCRIPTION', 'livingfitapparel'); ?></button>
                                    <?php endif; ?>
                                    <?php if ($show_description_tab && $show_size_chart_tab): ?>
                                        <span class="lfa-why-tab-separator">|</span>
                                    <?php endif; ?>
                                    <?php if ($show_size_chart_tab): ?>
                                        <button type="button" class="lfa-why-tab <?php echo !$show_description_tab ? 'active' : ''; ?>"
                                            data-tab="size-chart"><?php _e('SIZE CHART', 'livingfitapparel'); ?></button>
                                    <?php endif; ?>
                                </div>

                                <!-- Tab Contents -->
                                <div class="lfa-why-tab-contents">
                                    <!-- Description Tab -->
                                    <?php if ($show_description_tab): ?>
                                        <div class="lfa-why-tab-content active" data-content="description">
                                            <?php
                                            $full_description = $product->get_description();
                                            if (!empty($full_description)): ?>
                                                <div class="lfa-why-description">
                                                    <?php echo apply_filters('the_content', $full_description); ?>
                                                </div>
                                            <?php endif; ?>

                                            <?php if ($why_length || $why_material): ?>
                                                <div class="lfa-why-specs">
                                                    <?php if ($why_length): ?>
                                                        <div class="lfa-why-spec-item">
                                                            <span class="lfa-spec-icon">

                                                                <svg width="32" height="32" viewBox="0 0 32 32" fill="none"
                                                                    xmlns="http://www.w3.org/2000/svg"
                                                                    xmlns:xlink="http://www.w3.org/1999/xlink">
                                                                    <rect width="32" height="32" fill="url(#pattern0_66_1976)" />
                                                                    <defs>
                                                                        <pattern id="pattern0_66_1976" patternContentUnits="objectBoundingBox"
                                                                            width="1" height="1">
                                                                            <use xlink:href="#image0_66_1976" transform="scale(0.00195312)" />
                                                                        </pattern>
                                                                        <image id="image0_66_1976" width="512" height="512"
                                                                            preserveAspectRatio="none"
                                                                            xlink:href="data:image/png;base64,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" />
                                                                    </defs>
                                                                </svg>

                                                            </span>
                                                            <span class="lfa-spec-label"><?php _e('Length', 'livingfitapparel'); ?></span>
                                                            <span class="lfa-spec-line"></span>
                                                            <span class="lfa-spec-value"><?php echo esc_html($why_length); ?></span>
                                                        </div>
                                                    <?php endif; ?>

                                                    <?php if ($why_material): ?>
                                                        <div class="lfa-why-spec-item">
                                                            <span class="lfa-spec-icon">

                                                                <svg width="32" height="32" viewBox="0 0 32 32" fill="none"
                                                                    xmlns="http://www.w3.org/2000/svg"
                                                                    xmlns:xlink="http://www.w3.org/1999/xlink">
                                                                    <rect width="32" height="32" fill="url(#pattern0_66_1977)" />
                                                                    <defs>
                                                                        <pattern id="pattern0_66_1977" patternContentUnits="objectBoundingBox"
                                                                            width="1" height="1">
                                                                            <use xlink:href="#image0_66_1977" transform="scale(0.00195312)" />
                                                                        </pattern>
                                                                        <image id="image0_66_1977" width="512" height="512"
                                                                            preserveAspectRatio="none"
                                                                            xlink:href="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAgAAAAIACAYAAAD0eNT6AAAABHNCSVQICAgIfAhkiAAAAAlwSFlzAAAOxAAADsQBlSsOGwAAABl0RVh0U29mdHdhcmUAd3d3Lmlua3NjYXBlLm9yZ5vuPBoAACAASURBVHic7d15tF/Xddj3L0ACFEGCsyBSHAAO4iSO4gxCHJ+UNDalxG48pKkiW44HJSlXYrtaaWtHjiNbbtzEtpzGrt3UUZx4iJvWZZ0u+3EOOM8gwVEkAILzIM4UBgLsH+ddGXwE3u/8hvM7d9/7/ay11/KSaOrefe/vnvvOPWdvkNQXewF/Dfj3wDrg9blYB/z+3H+3V7WjkyRJE7UE+ClgM/D+gNg8988uqXKkkiRpbIuBvwk8weCBf35sAn4c2HvqRy1JkkayCLgKuJ/hB/758QjwBdLLhCRJaqkZ4C7GH/jnx4Ok2QRJktQiq4EbmPzAPz9uA66Y0jlJkqQ9OAP4Y8oP/PNjLfDpKZyfJEnaxamkgX8n0x/8d41Z4OzC5ypJUu+tAn4beI+6A/+usYP0MnJiudOWJKmfjgJ+HdhC/QF/T7Ed+CZwXKEcSJLUG4cBXwfepf4AnxtbSbMURxTIhyRJnbYc+ArwBvUH9FHjbdLLy8ETzo0kSZ2zH2ng/zb1B/BJxZukF4EDJpgnSZI6YSmp9O7z1B+wS8XLpJebfSeUM0mSwlpCKrX7FPUH6GnFZuBqYJ8J5E+SpFCaRj2PU39ArhUbSbMetiCWJPXCDHAf9QfgtsTDpJehReMkVZKktpoB7qT+gNvWWIcNhyRJHXIRcD31B9gocStw+UiZliSpBU6nTqOersQscN7QWZckqZJTaEejnq7ELHDWUFdAkqQpOp5UC38H9QfNQfEW8LW5eKsFxzModgD/bi7HkiS1QoRGPU3srk5/pH4D20gvWccOuCaSJBVzKN0aOCO+yBy+wPlIkjRRTaOe16k/EA6KHaT1CJ8Y4vxWkgbX91pw/IPChkOSpOKiNeoZd/HcqcRZzGjDIUnSxDWNep6j/kCXO/BPcvvcGcTZzmjDIUnS2Jp6/U9Sf2DLiVuBK4pkIllNnIJGNhySJA2tGfgfo/5AlhPTLqE7A9xV4DxKxAZsOCRJyjAD3Ev9gSsnajfRmQHuH3CMbYn12HBIkrQba4CbqT9Q5cRG2vNXbTNb8gT185ITdwJXFcmEJCmUC4HrqD8w5cSLpAVubfyuvYT0UvIM9fOUE7cAl5VIhCSp3U4jzsr2V4izsr3ZMfE89fOWE7PAuUUyIUlqlZOJVa//68CBRTJRVlMz4TXq53FQ7ASuAc4skglJUlXHkKrbbaf+gDMo3iGV5F1RJBPTdQjwVeAN6ud1UDRVE08okQhJ0nStIP0VHam+/a6NerqiaTj0HerneVBsI12HI4tkQpJUVKRGPc1fnscVyUS7HE2cmRgbDklSIPsTp1HPTtLAf2KRTLTbKuI1HDqoRCIkSeNZRir9+iL1B4ycmAXOLpKJWCI1HHqVtJ7BhkOS1ALN/vNnqT9A5MRa4JIimYjtfNJK/NrXJyeahkMfKZIJSdKCmgp036L+gJATtwFXFslEt1wM3Ej965UTT5NePvcukQhJ0gctIg38j1J/AMiJB7EG/ShmgLupf/1yYgPtKc0sSZ00A9xD/Qd+TjwCfIE0U6HRLCLV7X+A+tczJx7Clz1Jmqg1wE3Uf8DnxCacFp60aA2H7sCGQ5I0louI06jnWeDLpFr4KmMpKcdRFnxeC3yiSCYkqaPOItaK8J8hRqOertiXlPOXqX/9B8W7wJfKpEGSuuMk4I+IsSf8DeCf4J7wmg4gXYO29xnYCfxUoRxIUmgRG/V8rEgmNIqm4dCb1L8/9hTbSbUOJEmkpje/Saq5XvsBPSi2At+gm416uuII0jVq6/20DncISOq5CH+xNdE06jm+RCJURJsbDn224HlLUmtF+WbbDPx/SFqXoJhOIl3DHdS/n5r4t0XPWJJaZh/S3vgXqP8AzolZ4FNFMqEaPkl7Gg49VvhcJakVlgJ/D3iO+g/enLiOVHtA3dSGuhJbi5+lJFUUrVHP7Vi5rU9qVpbcOYXzk6Sps3a7IqnRW+LFqZyZJE3R9wD3UX9Qz4lHgR/ERj1K98APMr3ukn8xndOSpPLs364umNZnq38wrROSpFIuIK2Wrz2o58TzwN/HRj0abCnpXnmeyd+H7wAHT+9UJGmy2rSlalC8Sio4tLxEItRpy4CrSd/sJ3U//sxUz0CSJuRE4D/QrqIqe4o3gV8ADiySCfXJgaR7adyqldcBe0352CVpLMcAv0s7y6rOj3eBfw4cViQT6rPDSPfWuwx/X94E7D/9Q5ak0RwGfB34DvUH9kGxjVT7/cgimZD+0kdJv4stDL4vt8/9s649kRTCIcAvA29Tf2AfFO8BvwccWyIR0gKOAn4eeJh0Hzb35E7SlsJfAVZWOzpJGsJy4OeA16k/sA+KnaSFiKcUyYQ0nGXAcXNxUOVjkaRsS0l740tseyoRs8A5RTIhdccy4EeA/xvYTPpMtgXYQFrM+wPAkmpHJ6mqJaSB/xnqD+o5sRa4rEQipI75EvAsg39Tj2MPDKlXmopnT1B/UM+JO/AhJeVYCvwbhvt97QR+EfthSJ3WNOq5n/qDek6sx0Y90jB+h9F/bz9f4XglTcEMcBf1B/Wc2ED6NGHRFCnf32W8391O4DNTP2pJxawGbqD+oJ4TNuqRRrMfk1nEuw47ZErhnUHaJld7UM+Jl4GvAB8pkgmp+36cyf0enQWQgjqVeI16DiiRCKlH/ozJ/S6/MeVjlzSmVaRSuLtWJGtrvE0qj2rBFGkyXmJyv8/bp3zskkZ0FPDr5NUlrx1bSS8phxfJhNRPezPZDp1PT/fwJQ2radQzSmeyacc24JtYr18qYS8mO/O3YbqHLynXctKCuTeoP7APih2k9QifKJIJSY3nmNzv9tYpH7ukAfYjDfzfpv7AnhOzwJlFMiFpvv/E5H67vzrlY5e0BxEb9ZxbJBOS9uTvMLnf8KVTPnZJ8ywBvgA8Rf1BPSduAS4vkglJgyxlMs+KO7D0tlRN06jnceoP6jmxbu54JdX1Q4z3W95OqhwqqYIZ4D7qD+o58TA26pHa5tcY/Td9dYXjlXpvBriT+oN6TmzERj1SW+1F2h48zG/6PdICY0lTdBFwPfUH9ZzYTPoLYZ8imZA0SZ8HHmXw7/oOYE2lY5R66XTiNerZt0gmJJWyN/A54HeA+4EXgGdJbcF/HbgCP+FJU/NJ4P8kRqOe14D/Edi/SCYkKdkLOAW4kLQFcUXdw5Em63hSKdxJ1usuFW8BXwMOLpIJSUouB/4EeJMPP4c2k55Dq2odnDSuiI16jiiSCUlKVpBfudA+IgrnUGzUI0nzrSQ1Gxr1DxQ7iaq1DgJ+kTSNXntgHxQ7gN8HTiiSCUn6oEOBTYz33HqL9Iw9aMrHLu1RxEY9ZxXJhCTt3u8zuWfYm6RZ1gOmegbSLvYh7Y1/gfqDek78OXBekUxI0p6dTZln2gtYn0RT1tTrf5L6g3pO3Era8ytJNfwWZZ9xm0kVSvee1gmpf5qB/zHqD+o5YaMeSW3wNNN55m3AUuWasEWk0prrqD+o58R64Puxypek+g5i+sXP1pGe2T4DNZY1wM3UH9RzYiO+/Upql2Op90y8A7iq/Cmqay4GbqT+oJ4TzwA/CSwpkQhJGsNR1H9G3kB6pksLOo04jXpewUY9ktptL9pTG2UWOKfs6SqiU4D/SIxGPa8DPwcsL5IJSZqsNrU930l61p9S9IwVwjGkEpPbqX9jDop3SL0F7JYlKZIvUv/5OT92kGZ7rYbaQx8H/ldSjenaN+Kg2EIa+D9WJBOSVNZHmN5WwGFjK2ks+Hixs1drRGrU07yhHlckE5I0PVfS7k+sTcMh/9DqoAOBX2D3vafbFjuA/wCcWCQTklTHz1L/+Too3iSNFQcWyoGmaBlppfyr1L+xcuJPgdOLZEKS6vsyMWZgXyWNHcvKpEElLQX+AfA89W+knJgFLiiSCUlql5NIz7zaz92ceJ40liwtkglNVFOv/1vUv3Fy4jbStzFJ6ptIBdeexoZDrbWINPA/Sv0bJScenDtea1VL6rsZ4B7qP5dzYgOWXG+VSDfPI8AXSDMVkqRkEalu/wPUf07nxEP4R1xVa4CbqH8j5MQmnD6SpEGifca9HRsOTdUFwLXUv/A58RJpJelHimRCkrppCemPpmep/xzPibXApUUyIQA+SSqM0+ZiEk00jXrcQiJJo9uH9CLwAvWf6zkxC3yqSCZ66iTgm8B71L+4g+ItUqVBi0hI0uTsT/qj6jXqP+cHxU7gGuCMIpnoiYiNeiwjKUnlHEL6I+sd6j/3B0VTzv34IpnoqI+SLvB3qH8BB8U20kuKjSQkaXocJzrmEOCrxKnX75udJNXlTHFw0b7t/DFpXYIkqR0irhU7qEgmgnB1pyRpkiLtFutlwyH3d0qSSrJeTMtY4UmSNE1WjK3MGs+SpJrsGVPBDHA39ROaE09hlydJ6iq7xk6JfZ4lSW0U7XP0bcCVRTIxYeeTSiDWTlhO9GLhhSRptyIuSL+kSCbGFG3rxVeB5SUSIUkKZRlwNfAi9cennGjNlvRVpCpMFl+QJEXWFKV7nfrj1aBoitKdWCQTAxxNnPKLW+eO1fKLkqRBDiX9sfgu9cevQdGUpT+uSCbmOYx4DRiOLJIJSVKXrSCNd1uoP57l/qF7RIlENI163mjBiea+EZ1QIhGSpF6J2HBoxSROfD9iNeq5BjhjEicuSdIuTiY1HNpB/fFuUDRr3g4c5USXkrZHPN+CE8mJWeCcUU5UkqQhnEaaZa497uXEK6Q/4vfNPbm/TiqOU/vAc+IGYHXuiUmSNCEXk8ag2uNgTmwCPrfQySwiTRnUPtCcuAP4zEInI0nSFHyGNCbVHhdz4pf2dBK/2IKDGxTrgM8TsDayJKmzFpHGpnXUHycHxT+Zf/BX0O4qfo8DP0wHuiNJkjprMfC3SGNW7XFzT7EDuGzXA25rq95NwI9hox5JUhx7k8auTdQfR3cX9zI3k355Cw5mfryMjXokSbE1O+qeo/64Oj8+DfAvW3AgTbwK/GNS/QFJkrpgP9LY9m3qj7NN/C8AN7XgQN4iLUK0UY8kqasOIo11b1F/3L0B4NGKB7CV9Bby0XEyKklSIB8ljX1bqTf+PrKYtCKwlqWk0r1HVzwGSZKmaQVp3FtS8Rh2QCqjW3sqYgfwB1TqbSxJ0hScSBrr2tBP4M8BvtaCA2liO/C7pO5LkiR1wTGksa1NHQX/GcCnWnAg82ML8BvA4aNkWpKkFjicNJZtof64Oj/Oag7y+hYczO5ior2NJUmagkOArwJvUH8c3V1cu+vBnkk731CaeB34OWB5VuolSZq+5aSx6nXqj5t7iu8Ap88/8C/R7n4A75MqBP40Q/Q2liSpsH1JY9PL1B8nF4qdwBf3dBI/Rt19ibnxDPCT1N1CIUnqtyWksegZ6o+Lg2Ir8KODTuhs4NYWHGxOPAl8Adhr0ElJkjQhe5HGniepPw7mxG3ssugvxwypY1DtA8+JR0gXw3bBkqRSFgFXAfdTf9zLifXA35w77qEtnvt/fqwFJ5ITD84dryRJkzQD3EX9cS4nNpK6EE5kdnxv4k13XDGJE5ck9dpqUuOc2uNaTmwGrgb2KZGINvc23l3MAueVSIQkqdPOAP6Y+uNYTrwMfIUp7ZDbb+5/rE29jQe9CJxdJBOSpC45lTTwt31b/PvAm8DXgQOKZGKA5aQXgbZWO9o1dpAuqg2HJEnzrQJ+G3iP+uPVoHibNPAfXCIRwzqMdDDvUj8xg2I78E3guCKZkCRFchSp5Hybq+E2sZX0ktLKPjkRE3lEkUxIktos0h+u20h/uB5bJBMTthKnUiRJ7RPx0/UnimSisFNwMYUkqb6Ii9eHqt7XVqfjdgpJ0vQ129efp/74kjvwd3L7+kXA9dRPcE4ULaggSSpqCamA3VPUH09y4lZ6UsCutyUVJUlFNSXsH6f++JET6+hpCfsZ4D7qX4CceJgxmipIkopzTAnGtzVJ0jhmgDupPz7kxEacVf6QiN9rLi+SCUlSjkjryp7BdWUDRVyxeW6RTEiSdifSzrJXcGfZ0NyzKUnaVaTaMm+RasscWCQTPWHVJknqt5XEqS77Dqkk/ooimeippm7zd6h/gQdFqLrNktRS9pfRBzQ3xFbqX/DcG6KVnZskqaUOJU6jnmbm1w6zU7SKOFNCNhySpMGaT76vU/+5PSh2kgb+E4tkQllOJc6iEBsOSdKHRVz0fXaRTGgk5wPXUP/GyImm4dBHimRCkmJotn0/R/3nck6sBT5dJBOaiNXADdS/UXJiM+nm37tIJiSpnZoKsE9S/zmcE7cBVxbJhIqYAe6m/o2TExuwNKSk7msG/seo/9zNiQex9HtYi4CrgPupfyPlxHpsDiGpm2aAe6n/nM2JR0il6RcXyYSmqnnrfIL6N1ZO3EF6cZGk6NYAN1P/uZoTm/CzbGctIV3cZ6h/o+XELcBlJRIhSYVdCFxH/edoTrxEWphto54eaFaevkD9Gy8nZoFzimRCkibrNGzUowCavaevUf9GHBQ7SdsczyySCUkaz8mkEug7qP+8HBQ26tF3HQJ8lVSop/aNOSiaspMnlEiEJA3pGFJV1u3Ufz4OChv1aI8+SqyGQ78NHFkkE5K0sBWk52WERj3N8/LjRTKhTjmaOG+0TcOhjxXJhCR9UMRGPccXyYQ6bRXxGg4dVCIRknpvf+I16jmpSCbUK58kTsOhV0nrGZaXSISk3lkGXA28SP3nW07MAp8qkgn12gXEaTjU7Gu14ZCkUTR1U56l/vMsJ9YClxTJhLSLi4EbqX/D58TTWNlKUr6mcuq3qP/8yonbSWWGpamaAe6h/g8gJ57ChkOS9mwRaeB/lPrPq5x4CHunqLKm4dAD1P9B+KORNIpIf8w8SmrU4x8zao2I02Y2HJL6bQ1wE/WfRzlhox61XsSFM5cWyYSktrqAtFq+9vMnJ1zQrHCarTORGg65dUbqNrc0S1PUFM+I1HDojCKZkFTLSaRGPRGKmjWNeixqps44hHRTv0P9H9igsHym1A2RyppvwbLm6riIDYdsoCHF4nNGarGILTR9M5faLWJrc2ca1Vsnk77N7aD+D3JQNN/mDiySCUmjcq2RFNhpxFmd+wrpYbOsSCYk5VpK2nYcabfROUUyIXXAauB66v9Qc+JZ4Mukh5Ck6VlK+u1FqTdyPenZJmkBM8Dd1P/BDhNW6JKmo6k4+gT1f/fDxINYglzao0gdBvcUj5BqdC+ebGqk3ovWc2RPcRtw5YRzI4V1PmlBTO0f5iTDt31pciLOCg6KtcAlk0ySFEmkkpyjhm/70ui6MCs4KCxBrl5ZRdr/H6Ek56TCt30pXxdnBReKnaQ/hk6cRPKkNopUkrNUzAJnj5tIqaNOpfuzggtFUxjouHETKbXFYcQpyTmN8G1f+qBV9G9WcKHYOpePI8bIqVRVU5LzDer/oNoYvu2r75wVXDiaEuQrRk2wNG37EackZxvCt331jbOCw4UlyNV6TUnO56n/g4kYvu2r65wVHC+aEuT7Dpl3qZglpIH/Ger/QLoQvu2ra5wVnGy8OJfPfYa5CNIk7UWqevck9X8QOXHn3PH+5xYcS068BPwjfNtXXPuS7uGXqP97yon/THpG3NmCY8mJJ+eOd6/cCyKNqynJeT/1fwA5sZ4PV+W7CLiuBceWE77tK5pos4K3AJfPO4cZ4L4WHFtOWIJcUzED3EX9Gz4nNpIeQgu9Hc8Q520/53ykmqI16nlg7ngHnc/jLTjWnGhKkEsTtRq4gfo3eE5sBq5muL+YI73tP4x9BtQ+M8SeFVzIEtJf2E+14Nhz4jbgisxzk/boDNJe9do3dE68zHgrZKO97a/Dt33V17VZwYU0O52ea8G55MQscN6I56oei1SS803SqvkDJnTu0d72b8W3fU3fauB66t//OTHKrOBCml0N327BueWEJciVZRVxSnK+TRr4Dy6RCHzbl3anT7OCgyyf+/dHqGvQVB61BLk+5ChSEZot1L9RB0VTOe/wIpn4sIhv+2cVyYT6rM+zgoM0lQ3fLXhOk4rtwDexBLmIdeNuI924xxbJxGAR3/Y/USQT6pOVOCuYK+IfUpYg7yEHs9H50qQ+iDiYTWtWcJCV+NKkFnI6e3IiPiB929cgvuBOzin42UQtEK1RT6QFbSvxbV/xOStYzum4cFIVRNzSNr8kZxS+7SsiZwWnp89bJzVFFrWpx7d9ReCsYD19Kp6kKbOsbTv4tq82ijgr2NVCV5HKJ3f5Wd0Jf4U4b5XfAv5b+tG96q8Cd1M/5znxBPC36cd16ZvFpGsbpVHP3aTfTtctBn6UtFC3ds5z4i7SWKOWiNTats9/afq2r1qcFWynaN0Td43dtVDWFEX61vwKfmsG12Zoumx33V6RXsoWilng3AnnRguIuNr8wCKZiCvad1jf9mOJNCv4DP2aFYz0UjZMzAJnTjBPmmcl7jfvmogrsX3bby9nBdvrQuK8lI0a0eozhHAk8Fukqle1L/Cg2EKqjvexIpnorv2B/wl4h/rXcFDsBP6E1BxG7XAq6ZpEmBV8jXSv718kE+1zNvBn1M/7NGMbacw6cgL5661DiVOSs3nzs8PUaE4jzl9u86+5b/v1rMRZwbY6mVSmeAf1c18r2tajIYT9SVNjr1P/Ag4Ke0yPpwsPibbXY++iI7EPRVsdQzrf7dTPfVuiefk7aIy8dt4y4pXkPLtIJrqviw8J3/bLc1awvT5KujbfoX7u2xqWIN+NZiHYc9S/QDmxFvh0kUx03wrSDyDCX26jhm/7k+esYHsdAnyVNLjVzn2UaEqQf2T4dHdHsyf8SepfkJy4je6W5Cwt0l9uk4pXSQ9G3/ZHt4y0Re5F6l/PnOjTrGDTROk16uc9ajxN+uN37yFzH9oi0sD/GPUvQE48iMVgRhXpL7dS4dv+8JwVbK9oW3cjxAZ6UgRqBriX+gnPiUdIxWqsCz+8aH+5TSN6+bY/JGcF2yta8a6mZsdVwAMtOJ6cWE9Hy0CvAW6mfoJzYhM+qEcV7S+3GrGBnrztD8FZwfaKVr57LXDpHs4hSs+BO0gvLuFdCFxL/YTmxIukqdq+lOScpGh/ubUhOvu2PyRnBdsrUr3+nEFzCenl+5kWHG9OrAUuG3BOrRSpsEvfSnJOUrS/3NoYnXnbH5Kzgu0V6dqM8iLdzFS+0ILjz4lZ4Jwhzq+aSIVd3sJGPeOYAe6h/nXsSoR92x+Ss4LtFenabGT8T2nNIuUIOxl2AtcAZ4xxvsUcC/weMUpyvkMa+A8pkYgemAFup/51zImngC8CP01ajV/7eHLi/6WbW8nOJp1b7fzmxMuke6Yvs4JnEefabCYN/EsmeP6HkMaECL1I3iONta2oPNpUf4pQ2GUbqVLbx4tkovsi/XXwEh/+yy3SvuVWv+0PyVnB9jqWOL0UpvGpNlI1w2Y8q9JwKFJhl6Yy1/FFMtF9zXqOCJ3WmofEsgXOp6lc9kYLjjf33j1hgfNpq0jlnt8h9RZYUSQT7XM0ca5NjZeySPlpSpBPpQNtpMIuO0kPz5OKZKL7uv6X22H4tl+Cs4LtFemeb8NLWaQZkuYZWKQEebTCLrPAp0okogci/uU2ztuvb/uT4axge0Wa9WrjS9kniTML2pQgXz6JE2/2TT7bghPLibXAJZM48R6K+P1rkg+JVfi2PwpnBdsr0rqXCC9lF5DW5tTOVU4066BGKkHeFHb5VgtOJCduB64c5UQVagXsNB4SvX3bH5Kzgu0VqV5/xAWva4AbqZ+7nBi6BPnlwMMtOPCcuA/43twT0wccQJxpwR3AHzLdv9wuIM6uh+eAv0968Je2dO5/K0q552tJO1j6YAnwd0kP/dp5z4k/I/aW1+8lTqXE9Xy4RPKHfIUYi74eAX4Ay6iOYhnws6QV87WvY078P8CZRTKR53Lg1t0cVxtjI/AjlKlYt/fcv3tjC84zJ26lP416FgN/izi17m8ELi6RiAoWAz8IPEr9vA6KHaRn/279bAsOcFBsIBV2sZHK8JYCf484f7ldR7v+cov0tv8o6aE0iZr1kR5w79O/WcHPA+uon/ecuBP4bJk0VNe8IG+gfp4HxT+cf/AX0e7FT8+RBq9pTHF2zV7EuTHfp90tVheRZp4eoX6ecuJ+xuszcNXcv6P2eeTEo/RrVjBSRc6HgL9BP65NhD+0tpM+cX7XbS04qN3Fqwwu7KLdW0SsXtgPEac7XsRFssO8CKwBbmrBcedE3xr1RFqbspH+tr5uFsm2teHQHcw9a1e34GDmR5u2OUUUqVHPU8R9SETcJnvpAudzAWm1fO3jzImxtjkFFGl3St+aKC2kzdtkLwD45y04kCbeAf5nUlERDe8KYi1Y+1G68Zdb9IWVZ879Z7WPKydemct1X2YFTwT+gBiLs/vWRGkYh5HGtjZtt/4VgBtacCBbgd8Ejhg1uz0XqVHP80xvy9q0Rdxa+YfEGFzemMvtAZnXIrpjgN8lRoXK14Gfp049img+Dvwr0phX+7pdB3VX90ao/tRmkaYFaxatmbZIxZXaHltob/njEiLW6+/LtZmkNpRcfxjqrmj2BWA0q7BsbQSRyiu3LdpYE76k5aRvxRFmj/p2bUpowwvAeoDrKx5AE34CyLMS+N+JMS34Lml9yWFFMhHLKuD/IMYLW+14by5Xq0bIc0T7A/8D8G3q5z7n2vwe/bk2JbTpE8C1kBYm1D6QJlwEuHuHA9+gHTfNoNhKusH96+DDTgb+iBifbKYdO+dyc/LI2Y1lH+L0UtgJ/EfglCKZ6Ic2LgL8OqQiQLUPZH70edp4V00bzzepf00GRfM554QSieiYSGs3phGzwDljZTSOpobEk9TPe+61ObdIJvqhzdsAz28Osq2FgPq25aexnLSqto03zfxoWqz618HwLiJOpb0ScT2pDkkfLAZ+GHiM+nnPiZtIBaE0mmXAf097twbfsuvBXki7vyv3pRTwvqR9tC9TP+c53HHnPAAAIABJREFUEb2bV00ribOeY9JxO6lYVV9Eqsh5N/BXy6ShFyKUAt7GLn/9N/5RCw5sUGygm82AlgI/RZyKcjfQnW5e03Y48Buk7W21r+O04wHgc+OnMIwraO/s6vxYD3w/MUpxt1GkZkD/3Z5O4meIURSkK+2A9wL+Dqkcbu2c5sQdwGeKZKL7mtoAb1P/Ok47HgN+iMl0J4wgUr3+p4Av0L0/qqYlUrfM90h/6C/oUtLbYO2DzYmorT8XAf81qRBD7RzmxDpS29HoL1w1RFrPMenYBHyJbpR7znE68KfUz3tOPEuadez6Z9WSIrUIXw98OvfEInY7uzL35CqbIX1nq52znNhA3EY9tS0l5e556l/HacfL9KtRzyriFOZqOqxar390a4AbqX8tc+JpxuiWGbHb2SWjnOgUrKYdfReK3zQ91/xmnqH+dZx2NOWe+1Kv/yhSKdwI9TncWj2+C4BrqH8tc2Ki3TKb3sYRila8T9q7+qlJnPgEnE+cm6Zvf7lNUjNr9gT1r+O04236Nbg09frfpX7uB8VW+tVLoYRIdTuK9lxpc1GD+dHsTz+pRCIynEqcm+ZN0gOtL3+5TdIi0javPu7nbwaXw8fOYgwR6/UfWSQT/XAscT7tTHWG51DivAFPu+HQKuLcNH37y23SZoC7qH8dpx19G1yWkQb+CPX6rcg5vqOp36gnN6rO8DTdziLsaS7dwSriTdOXv9wmLdJ6jklG3waXZiFnm4u67BqzwJlFMtEPkbp3tuolvA2tDXOj6WG9YkLnHql/9zbgm6SpLQ3vPOKs55hk7Jw7774MLtbr7xd7rkzIKcT57v06aX/2qIslDgZ+iRiFXXaQBv5pfQbpmtOA/4sY9/W3Se1lLyaVah733/dntGdBbWmRirq8D/wXhtjbrQ+JVKMjVM+V00gHWztpOfEKw+2L3W/un3+tBceeE7PAWZnnpg9aRbz1HAfPO4cLgetG+PfdAlw2Yt4imgHuof51zIkHSTMUGk3zaecF6l/LnAjbLfNi4nwrHVQZ6yPAPyTtr6x9rDnx/xH0pmmBo4H/jRiftL4D/AsGf9L6LOkvxkH/vv8y98/2xWWkl53a1zEnHiZVELUi52jsuVLJZ0h15GsnNCeeItXnb6rfLQF+AtjcgmPLiZtxWnBUK4BfI9ai1qOGPMeTgH8K/CfSX7z3zP3fv0C9LbM1nA/8BfWvY05s4IPPJA3HnistsIhUT34d9ROcEw+TvqVGKYd8F/BXsq+GdnUw8DXirOf4d7ieY1SR1nM8B3wZ6/WPyp4rLbSI9P3qMeonvAvx8Fw+O33TFNKs54iwv/t9XM8xjpXEWc9RtJpbT9hzpeWibbVpW2ykhzfNhERr1DNL2oKo4TX1+iN81rEw1/girTuz5wrxim3Ujs2kvgz7jJLsnltC6n0e5VvgrcDlRTLRfZGqlVqvf3z2XAkuUrnNmjeNbTyH18w2PU7965gT63Cb16gi9StpVTW3oOy50jGRfsDeNO03A9xH/euYE67nGF2kjqVNNbdPFMlEP6wizpoOP+2MINIUXsmbZn5hF+WZAe6k/nXMiY24nmNUS0i5i7K324Wc47HnSs8cSZxFPJO8aY6YRPJ66CLgeupfx5xwPcfoms86UbbpupBzPPZc6bmVxJnyGSW2k26a4yaVsJ45ne6Wn9ZfWgRcBTxA/euYE7cBVxTJRD8sJ/1W3qD+tRwUftqZgkgNh4a5aU6cZJJ6JNL90KznOLBIJrov0t5u6/WPJ2LPlb50y2yFSH/xLXTTnD3pxPTESuLMCLmeYzyribO3+xHSVtPFRTLRfRFrdNiKuaJPAzdR/0YYJq7F74GjOgr4LdJ3ttrXcVBsIfUWcH/3aM4D/pz61zEnNgBfxIWco4rWc+Um7LnSKq767rZIu0Kazzqu5xhNpM86L2FRl3FYo0MT5b7vbmkWAUWoC+F6jvGsJM5nHev1j28GuJ/619Jndcf4VhlfxEY9rucYzQrS7E6Erb4WdRnfDKl7ae1rmRMbcbY2LGu/xxOtN8Ra/BY4qkifdSzqMr7VWKNDFURcWdq3RYLRukO6v3t0kcp9W9RlfJF2bNlzpcMiTit3vWzoItLA/xj1850T7u8eXVOv/wXqX8dBYVGX8UVazGnPlR6xulQ7zAD3Uj/HOeH+7tFZr79fVhJnMac1OnqsqS8d4Rtkl6Yi1wA3Uz+nObGJNHjtXSQT3dZ81nmC+tcxJ1zPMZ6jiNO3xZ4r+q6IN27ExUgXkoog1c5hTrxImiVyEdDwItbrv7JIJvoh0h9S9lzRHq3CqasSTiPOIiAb9Ywn0havh3Bv9zgifkq1RocGOhUXr0zCyaS37R3Uz9OgeAsb9YwjUhvmp3Bv9zgiLqa2RoeGdgZx/nJt0/aVY0gzKdupn5dB8Q7p88+KIpnovkhbvJ7G9RzjiFajo4/bqVVApG5kNQtYfJQ4Fd22kV5SPl4kE90XaYtX83Jsvf7RRCuoZo0OFRHp++YGpjfNGamiW/Mt8Pgimei+ZnYnwjqZpl5/Gz+PRRCtpLo1OlRcs8I5ShOL9ZRb6BSpottO0sB/UoE89EGk2R3r9Y8vUlM1a3Ro6qLtcX6Ayb0dNxXdXmzBeeXELPCpCZ173xxCnNmdyFtk2yJSW3VrdKi6psrZM9T/QeTELcBlY55rlIpua4FLRjzXvmtmd16j/nUcFM3e7i4UyarlIuA66l/LnLBGh1onYsOhczPPrZnt+FYLjjsnbsfCLqNq7uMI9fqbzzru7R5dpF0c1uhQ6zV7ZCP85bQTuAY4cw/n0jTqebQFx5oTFnYZXbSZLPd2j+cUrNEhFXMIaQVypCpZJ+xy/DPAPS04tpx4lLQIyMIuw4u2lsXPOuNZSZxdHNboUHiHA79BjNXT24DfIe2jrX0sObEB+CIO/KNYBHwfaZdI7euYE7fi3u5xHAn8a9JvvPa1HBRbSM9MF3OqM44mTnW8tsdLWNhlHJHqWfhZZzwRa3TYqEedtYo4U3Bti1dJA/+yYZMuIFa9/g1Yr38cEWt0uJhTvfFJ4pRSrR3NIiALu4wmUjdG6/WPxxodUiDnk1bi1/4htjG2kGZLPjZydvstUjdG6/WPJ1qjHhdzSru4GLiR+j/MNoSNesYTqRtjm9tZR9Ds4niS+tcyJ27DGh3SHs0Ad1P/h1ojbNQznqZe/3eofy0HRVOv/+Aimei+pkbHY9S/ljnRNOpxMae0gNXEWag1qdgJ/BFpylrDOxT4FdK+6drXclBsAX4N93aP43Ok3h61r+UwcT3p2SZpN5qFWn1bFDgLnDOB/PVRpKqTbvEa3xrgJupfS3/v0oREWqg1yVgLXDqB/PWR9fr75ULgWupfy0neE9cAZ0wySVIkkRZqTTLuAK6aQP76aAmp5PEG6l/HnHCL13i6Pivomh/1zhHAb5J6l9f+AU4zniR9u9TwFgN/mzjdGK8j/dWq0ZxMtwf++bGV9Ew8YhLJk9roENKq5wgLtUrE7hoOabAZ4D7qX7+ccHZnPH2dFWzCuh/qnKYkZ4SFWtMI9/jnmQHupP71yon1uMVrHJG2b04jrPyp8JqSnBEWatWIrfi2vzsXkqbQa1+fnNiA9frH0TTq6eus4KB4ldRSffmI+ZWmbgnpofgs9X9AEcK3/SRSvf7NWK9/HJEa9bQh7P6p1mtKckZZqNW26Ovb/knE2Qba1Ovft0gmus9ZwfFiE754qmUWkRY+RavM1dboy9v+0cRZ8GW9/vE4KzjZeJS0HdZPT6rqe4mzQvtR0l8ft7XgWHJiI/AjdO9t/3DgG8TYBvo28EtYr39Ue5Pu4Y3Uv5Y5cRvpGfFoC44lJ+4jPYOlqYrUuW93/dVngHtacGw58RTdWGh2COkTx5vUz+mgaBZoui97NE2jnigD6UN8cBdHtM+Zt2OHQU3BBaQSlrVv+JwYNJUe/SEVhfX6+6VLL9fRPl2sBS7Zw7lII/skcSpzDbuYLuLb/kzmudXU1Ot/nvo5GxRNvf6TimSiH9YQe1ZwIc3ixRdbcOw5YQlqTcQq0lToe9S/qQfFuNvpfNufjKZe/1PUz5EPy/K6NCs4SKTti77UamSRVmhPuqCOb/ujaWZSHqd+TnLCbozj6fKs4CBNAaN3W3Bug8KGQ8oWqSRnU1L3yCKZ8G1/GDPAvQOOsS1hvf7xHEt/ZgUHaZ6XW1pwroPCEuTao0grtKfdVMe3/T1bA9xc+JwmFdbrH0+fZwUHidTE6B3g14EVRTKhUCKt0N5J+tZ4RpFMDObb/l+6ALi2BeeZExvpxjbKWpwVzHcycapaNrMjBxbJhFqtWaEdpSTnLHBOkUwMr89v+5G++24mreXYZ0Ln3jfOCo4uUl+LV7C8dW80K92fof6NlxNrgctKJGIC+vS2v4o43319oI0nUvvu2rOCg1xInJmyF0nX3RfmDmpWaD9B/RstJyIt1Ory236k775OaY7HWcFyIq2VseFQhzSNeu6n/o2VE5EXanXpbf8w4nz3dVHTeJwVnJ5Iu2UeIdXzWFwkEypuBrib+jdSTmygOwu1Ir/tLye9GLzRgmMbFG5rGo+zgnU0Jcgfo35Oc+LBueNVEJeS3pJr3zg5sQn4Mbo33bQI+Dywjvo5zomHgV8lxnff94B/A6zMvhraVTMAPUz9a5kT60i/pYizggvZm/Ts20T9HOeEhbNa7nzilOR8mX70vI/2tt/maPuCrwicFWyfZu3Fc9TPeU6sBT5dJBMayanE2ZrVlOQ8oEQiWqyZbn2S+tcgYkRa8NVGFwM3UP865sSwjXq6ImIJ8rOLZEJZVhFna9bblC3JGUW0t/3acQtxF3y1gbOC8UQqQd7UXzixSCa0W0eRVj1vpf4NMCiakpyHF8lEXNHe9qcdD+DCo3E4KxhfpBLk20k1UY4rkgkBsbZmbSPdEMcWyUR3RHrbn0Y8TNxtoG2wCmcFu+ZI0h98EUqQN3/wHVEkEz0VaWtWMyX0iSKZ6K5Ib/slYiP9WPBVSqSCTc4KjmYl8V7uDi6SiZ5oGvV8m/oXNCdmgTOLZKI/Ir3tTyKewXr943BWsH9OIc7nnTexOufQmoViz1P/AubELHBukUz010rivO2PEtbrH0/TqMdZwf46ne6WIO+lJaTSi09R/4LlxC3A5UUyoUakt/2csF7/eCK1734fZwWn4SLgOupf65ywQ+duNHvEH6f+BcqJdbhCe9oive3vLqzXPx5nBTXIDHAn9a99TmzENT8sAr4PeIj6FyQnHgL+Bq7QrmkNcBP174VhwlXBo1sC/ARxGvXcRLpHVcci0jM60pjyffRwTJkB7qL+BciJjfi21ja+7Xebs4Iah/dPS10EXE/9hOeE32vabwa4j/r3Sk64xz/PDHHad3tN2y3aurJb6ei6skjfcJuSnK7YjMG3/W5wVlClRFxDcl6RTExZpFXczZ5NS3LGFPFt/4oimYhlNc4Kajoi1pY5q0gmCltJnH3cVm3qFt/2Y3BWULVYXbaQplFPhEpu1m3uNt/228lZQbVFU0kyQgnyVleSjJRIOzf1i2/77bASZwXVThH/cG1FL4mID1d7N/dTpJfUVr/tDyniw9VZwX5ahS+pWSJOr55dJBOKJuKA1Iq3/SH5wqWoTsXPVLvVLLB6bkonN270dYGVBluJb/slRJwV7OInF43vDFyoCsDepC1WT7bgRHPiNtxipTy+7U9GxFnBPiy61PhWAzdQ/57NiYluVW2KrDzWghPLiQexyIpG49v+aJwVVF9EKla1gTGLVc0A97bgRHLiEdIMxeJRT1aaY2GaPBZeUh8tAq4iTrnq9QxZrvpTpCn02geeE0+SHkKW5NSk/VfAPdS/x3PiCeC/YTovwIvn/reemOL5jRP3kK6lNEl7Eeuz+K1kLIT/cdKK2NoHOyieAX6S9FeIVMoi4PtJb9G17/mceJByLasXAX997n+j9nnmxHrStbNRj0paQhqLIrSs3gp8aU8n8uO0fyHUy8BP045vn+qPaG/7dwKfneD5f5Y47ZedFVQN+5LGppep/xtYKHYCPzr/4M8ivR3UPrg9xevAz5G2GEm1RHrbfx+4CVgzxvmumft31D6PnHBWUG2wnDRWvU7938SeYgtp0fN33dCCg9pdvEMq2rIiL/fSVERsOHTuEOcXqVHPK7RnR4TUOAT4Ku2th3Fzc6DntuBg5scW4DeIWQFN/RHhbb+JncCfkOoe7Mmpc/9M2z8Fvo+zgorhcNJY1sbKo2cD/FILDqSJ7cDvAseMkmmpkkOAXyZV7Kv9GxoU7wH/lg82wzpu7j+LUhXxl+dyLkVxDGls207931AT/wzg2hYcyA7gD7BRj2L7GHH6DGwD/vVcRNj5s2Uutx/LvhpS+5xIGut2UP839RcAD1c+iFlS7QGpK44m9Rlo09t+1Gjq9du+W13ySeqXIF+/mLr7ZLcB60gVzaSu2Az8BOlH/gekH7mGs5OUu1OAHyBVHJS64iXSc2J7xWNYDO3Y5vMW8IvAQUVPV6rjdOBPqf87ixJ/OpczqWsOIo11b1H/d3YDwL9owYE08Srwj0mdxaSuuYD0yav276ytMTuXI6lr9iONba9S/3fWxK8CXNaCA5kfTbezj4yQaKntLgZupP7vrC1xG3DlOAmVWqrN3TLXQFoDcF8LDmZ3sQn4MWDvYbMuBfA9xOm4WSLuncuB1DV7k8auTdT/ne0u7mGX9X9X0I5tCXuKx4Efxna/6p5FpHadj1D/dzateIQhW5RKQSwmjVWPU/93tqfYAVw6/8B/oQUHNijWAZ/HB4e6Zy/gi8AG6v/OSsWGuXO0UY+6ZhFpbFpH/d/ZoPi5PZ1Em6oCLhR3AJ/Z00lIgS0Fvgw8S/3f2aTi2blzWjrBPElt8RnSmFT7dzYodjJX/W8hn6O93y3mxw3A6kEnJAW0L/AztL+96ELx8tw52KhHXbSa9jbSmx+bgKtyTyxit7Nzck9OCmR/0o6Y16j/O8uNt4CvAwcWyIdU22n0pFvmfsR5+OwErmFen2OpI5r2om9S/7e2p2jad1uvX110MvBN2r1gvomJvoS3vbfxrtHUDj9hEicutcxHST/s71D/t9bENlLvg48XPG+plmOI09ujeQlfUSIRh9G+h8+gh9KRJRIhVdaGhkPNy/bxhc9VqqF52Y7Q3XMr6XlwRJFMzNOGh8+wiXFaUl10Emla8j2m95vyc5u67FDSwP8u9cevQVG1W+Yq0uA6zYfPqNF8E7HhkLpoWu1Fbd+trmoW3L5O/fFqUOwk/d5PLJKJIbWht3FuvEpaz7C8RCKkyko1HFrLbqqHSR2wDLgaeJH641NOtPYl/HzS1GDtBOXES9hwSN21hsm0+r6dIfYPS4EsIW13j1J0ay1wSZFMTFikbmdPk24CGw6pi2ZIjT+G/V08hPX61U2LSff2t6g//uRE2G6Z30N7Ow3Oj0eBH8SGQ+qexcAPAesZ/DtYP/fP+jtQ1ywmPeMfpf54kxP30YFumYuAHyBOt7P7ccpT3XUR8JukGboNc3Ej8I25/07qoqtIz/ba40tOPEIaMzs1+xZt2sVvn5IU26TWwkwjNtGDz9ERF15cWiQTkqQSSu2GKRG9XJC+D+lF4AXqX4CcaO3WC0kSEG9L+ldI2xB7K1K3s6b4wklFMiFJGsWxWJQutAjdzpqwBrok1RepLL3dMjO0sdvZnsIuaJI0fY4THRexBaNvdpJUjjPFPVOj29mo0XzbObBIJiSpn6KtFbNb5oRFWt35Cq7ulKRxLcXdYtrFBcC11L/QOdHL/Z2SNCbrxWhBVniSpG5pKsY+Qf3ndk5YMbayUbud1YhHgC9goxVJ2tUi0kD6APWf0zlht8wWWUS6GFG6PD2IN48kQfoj7m7qP5dzYgNpNnevIpnQWKI1HArb51mSxnQxqRNl7edwTjyNn3HDiLiA5JIimZCkdjmftE2u9nM3J1zIHdgy4GrgRerfSDkxC5xdJBOSVNepxNnK/Sqp4NDyEonQdDVFJF6n/o01KJqGQycWyYQkTdcq4jTqeRsb9XTWoaSL+y71b7RB0ZSRPK5IJiSprEiNerbOHavl3HtgBelFYAv1b7zcG/OIIpmQpMk6jHiNeo4skgm1WsSGQyuKZEKSxtM06nmD+s/LQdHMsJ5QIhGK5WRSw6Ed1L8xB4UNhyS1yX7Ea9RzZpFMKLTTSG+FtW/SnGgaDu1bJBOStLCmUc/z1H8e5sQscE6RTKhTLgSuo/4NmxMvkl4E9imSCUn6oKbOyjPUf/7lxC3AZSUSoW5bA9xM/Rs4JzZiiUpJ5URr1HMHNurRBMwA91L/hs6Jh7HPgKTJmgHup/7zLSfW4zNQE9a8/T5G/Rs8J9bNHa8kjWoGuIv6z7Oc2ICzoCqseRF4kvo3fE7cClxRJBOSumo1cD31n185sRkb9WjKmhWwz1H/B5ATs8B5RTIhqSvOIM5OqJexUY8qa/bAfpv6P4jcF4GzimRCUlSRGvW8SaqFckCRTEgjWE6chkNNFaxPFMmEpChWEq9Rz8FFMiFNQKSGQ9tIFRCPLZIJSW11FKm0eKR+KIcXyYRUQMQfmA2HpG5rGvX4B4o0BStxik1SXc0nykiNevxEqc44BRfZSJouFylLLXI68bbZ2HBIiiVio55zi2RCaqGLiFVo42psOCS13RLgC8BT1H9u5MStwOVFMiEFMAPcSf0fYk5sxFKbUhs1FUofp/5zIicsVS7tYga4j/o/zJyw4ZDUHj47pA7wLV5SLmcPpQ6K9h3vFvyOJ03LRcB11P/d54Trh6QRuZJXUsMdRFIPRdzLe2aRTEj9Yw0RSVbzknpkJVYRlTSP9byl7joS+4hIGiBiwyE7ekm7F6mT6HbSi/1xRTIhKdsq4k0VHlQiEVJA+5M+7b1O/d/noGg+7Z1YJBOSRnYqcRYLvQp8FRcLqb+WkbbIvUj932NOzAJnF8mEpIk5g3jbhT5SJBNS+zTbe5+j/u8vd+A/r0gmJBWzGriB+g+QnHia9FDcu0gmpPqaSp9PUv/3lhO3AVcUyYSkqZkB7qL+AyUnNmDJUHXLItLA/xj1f1858SCW+JY6ZRFwFXA/9R8wObEem4YovhngXur/nnLiEVIJ8sVFMiGpumYa8gnqP3By4g7Si4sUyRrgZur/fnJiE35+k3plCelH/wz1H0A5sRa4rEQipAm6ELiW+r+XnHiRtADXRj1ST0VsOHROkUxIozuNODtvXsFGPZJ20TQceo36D6hBsRO4hrTdUarpZFJFvB3U/10MirdIRbgOLJIJSeEdQirQE6nh0AklEiEt4BhS9c3t1P8dDIp3SCXDVxTJhKTOaRoOfYf6D7BBsY30MD6ySCakv/RR0u8iUv8NG/VIGsnRxPlLp3ngfaxIJtRnkRr1NDNjNuqRNBGriNNwqPnWacMhjStSo56d2KhHUkGfJF7DoeUlEqFOi9io51NFMiFJ85xPWolf+8GXEy9hwyHlaepjPEv9+zYn1gKXFMmEJA1wMXAj9R+EOWHDIe1JUyHzW9S/T3PiNuDKIpmQpCHNAHdT/8GYE09hwyElTaOeR6l/X+ZE06jHHhmSWqVpOPQA9R+UOfEQPkz7bAa4h/r3YU7YqEdSCNGmU2/HhkN9sga4ifr3XU7YqEdSSBEXVF1aJBNqgwtIq+Vr32c54cJVSZ2wD+lF4AXqP1hzwi1V3RJt6+pXSNsQJakzmqIqURoO/TFwUpFMaBqOxeJVktQqTcOhN6n/4B0UTVnV40skQkVEKl/dNOqxfLWkXmkaq0RqOPTxIpnQJHg/SVIwEVur+hdbezijJEnBnQR8k1jfbA8skgnliLam5BrgjCKZkKSOiLRq+xVctT1tS3FXiSR12gXAtdR/gOeE+7bLs66EJPWMldv6raks+QT1r29OWFlSkibM2u39Ym8JSdJ32b2tHyJ1l9yA3SUlaWqiNRyyf3uei4EbqX+9cuJp/NwjSdVEXBh2SZFMxHY+aZtc7euTEy74lKQWWQZcDbxI/QEiJ2aBs4tkIpZTibPl81VSwaHlJRIhSRpPUxzmdeoPGIOiaTh0YpFMtNsq4jTqeRsb9UhSGIeSHtrvUn8AGRRNedjjimSiXSI16tk6d6yWfZakgFaQXgS2UH9AyR1wjiiSiboOI16jniOLZEKSNFURGw6tKJKJ6Woa9bxB/bwOimYm5oQSiZAk1XUyqeHQDuoPOIMicsOh/YjXqOfMIpmQJLXKaaS/9moPPjnRNBzat0gmJqtp1PM89fOWE7PAOUUyIUlqtQuB66g/EOXEi6QXgX2KZGI8TT2GZ6ifp5y4BbisRCIkSbGsAW6m/sCUExtpT+nZaI167sBGPZKk3ZgB7qX+QJUTD1O3z8AMcP+AY2xLrMeeDJKkAZq/ah+j/sCVE+vmjndaZoC7CpxHidhAe2ZLJElBNC8CT1J/IMuJW4ErimQiWQ1c34LzzInN2KhHkjSmZmX7c9Qf2HJiFjhvgud/BnF2TLyMjXokSRPW7G3/NvUHutwXgbPGON9IjXreJNVMOGCM85UkaUHLidNwqKlu94khzm8l8Rr1HDzE+UmSNJZIDYe2kSogHrvA+RxFKkEcqW/C4QucjyRJRUUcOHdtONQ06unKi4wkSVN1PLH6DHxtLt5qwfEMih1zuT0++2pIkjRlpxBn8VyEGHcxoyRJU3U6cbbPtTFmgXOHzrokSS1xEXEK6LQhbgUuHynTkiS10AxwJ/UH2LbGtEsaS5I0VTPAfdQfcNsStZsaSZI0NU2fgcepPwDXio3YqEeS1FNLgC8AT1F/QJ5WbAauBvaZQP4kSQqtaTj0PPUH6FLRNOrZd0I5kySpM6I1HMoJG/VIkpSpaTj0BvUH8FHDRj2SJI0oUp3+JnbXb0CSJI0gQsOh7aR6/ccVyoEkSb21ivTX9XvUH/Cb2EEqeXxiudOWJEkAp9KOhkOzwNmFz1WdYoeHAAAAiklEQVSSJM1zBnUaDs0C503h/CRJ0gJWAzdQfuC/DbhiSuckSZIyzQB3MfmB/0Fs1CNJUqstAq4C7mf8gf8RUqnixVM9A0mSNLKm4dATDD/wbyKVJt576kctSZImYgnwU6QmPIMG/s1z/+ySKkcqSZImbi/grwH/HngAeH0uHgB+f+6/szWv1BP/P0qr6rfMYrL1AAAAAElFTkSuQmCC" />
                                                                    </defs>
                                                                </svg>

                                                            </span>
                                                            <span class="lfa-spec-label"><?php _e('Material', 'livingfitapparel'); ?></span>
                                                            <span class="lfa-spec-line"></span>
                                                            <span class="lfa-spec-value"><?php echo esc_html($why_material); ?></span>
                                                        </div>
                                                    <?php endif; ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    <?php endif; ?>

                                    <!-- Size Chart Tab -->
                                    <?php if ($show_size_chart_tab): ?>
                                        <div class="lfa-why-tab-content <?php echo !$show_description_tab ? 'active' : ''; ?>"
                                            data-content="size-chart">
                                            <div class="lfa-size-chart-slider" id="lfa-size-chart-slider">
                                                <?php foreach ($size_chart_images as $image_id): ?>
                                                    <div class="lfa-size-chart-slide">
                                                        <?php echo wp_get_attachment_image($image_id, 'large', false, array('class' => 'lfa-size-chart-img')); ?>
                                                    </div>
                                                <?php endforeach; ?>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endif; ?>

                        <!-- Right Image -->
                        <?php if ($why_right_image): ?>
                            <div class="lfa-why-image lfa-why-image-right">
                                <?php echo wp_get_attachment_image($why_right_image, 'large', false, array('class' => 'lfa-why-img')); ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </section>

        <?php endif; ?>

        <!-- Section 3: Reviews -->
        <?php
        $reviews_count = intval(lfa_get('single_product.reviews_count', 10));
        if (wc_review_ratings_enabled()):
            ?>
            <section class="lfa-product-section-3" id="reviews">
                <div class="container">

                    <?php
                    // Get reviews
                    $args = array(
                        'post_id' => $product->get_id(),
                        'status' => 'approve',
                        'type' => 'review',
                        'number' => $reviews_count,
                        'orderby' => 'comment_date',
                        'order' => 'DESC'
                    );
                    $reviews = get_comments($args);
                    $review_count = count($reviews);
                    $average_rating = $product->get_average_rating();
                    $total_review_count = $product->get_review_count();

                    if ($review_count > 0):
                        // Calculate rating distribution from ALL reviews, not just displayed ones
                        $all_reviews_args = array(
                            'post_id' => $product->get_id(),
                            'status' => 'approve',
                            'type' => 'review',
                            'number' => 0, // Get all reviews
                            'orderby' => 'comment_date',
                            'order' => 'DESC'
                        );
                        $all_reviews = get_comments($all_reviews_args);
                        $rating_distribution = array(5 => 0, 4 => 0, 3 => 0, 2 => 0, 1 => 0);
                        foreach ($all_reviews as $review) {
                            $rating = intval(get_comment_meta($review->comment_ID, 'rating', true));
                            if ($rating >= 1 && $rating <= 5) {
                                $rating_distribution[$rating]++;
                            }
                        }
                        ?>

                        <!-- Review Summary -->
                        <div class="lfa-reviews-summary">
                            <!-- First Row: Rating Number, Stars, Based on X reviews -->
                            <div class="lfa-reviews-summary-top">
                                <div class="lfa-reviews-rating">
                                    <span class="lfa-reviews-rating-number"><?php echo number_format($average_rating, 2); ?></span>
                                    <div class="lfa-reviews-stars">
                                        <?php
                                        for ($i = 1; $i <= 5; $i++) {
                                            if ($i <= $average_rating) {
                                                echo '<span class="star filled">★</span>';
                                            } else {
                                                echo '<span class="star">★</span>';
                                            }
                                        }
                                        ?>
                                    </div>
                                </div>
                                <div class="lfa-reviews-count-text">
                                    <?php printf(__('Based on %d reviews', 'livingfitapparel'), $total_review_count); ?>
                                </div>
                            </div>

                            <!-- Rating Distribution Bars -->
                            <div class="lfa-reviews-distribution">
                                <?php for ($i = 5; $i >= 1; $i--):
                                    $count = $rating_distribution[$i];
                                    $percentage = $total_review_count > 0 ? ($count / $total_review_count) * 100 : 0;
                                    ?>
                                    <div class="lfa-rating-bar">
                                        <div class="lfa-rating-bar-fill" style="width: <?php echo esc_attr($percentage); ?>%"></div>
                                    </div>
                                <?php endfor; ?>
                            </div>

                            <!-- Sort and Filter on Right -->
                            <div class="lfa-review-controls">
                                <select class="lfa-reviews-sort">
                                    <option value="recent"><?php _e('SORT BY: Most Recent', 'livingfitapparel'); ?></option>
                                    <option value="oldest"><?php _e('SORT BY: Oldest', 'livingfitapparel'); ?></option>
                                    <option value="highest"><?php _e('SORT BY: Highest Rating', 'livingfitapparel'); ?></option>
                                    <option value="lowest"><?php _e('SORT BY: Lowest Rating', 'livingfitapparel'); ?></option>
                                </select>
                                <select class="lfa-reviews-filter">
                                    <option value="all"><?php _e('FILTER BY: All Rating', 'livingfitapparel'); ?></option>
                                    <option value="5"><?php _e('FILTER BY: 5 Stars', 'livingfitapparel'); ?></option>
                                    <option value="4"><?php _e('FILTER BY: 4 Stars', 'livingfitapparel'); ?></option>
                                    <option value="3"><?php _e('FILTER BY: 3 Stars', 'livingfitapparel'); ?></option>
                                    <option value="2"><?php _e('FILTER BY: 2 Stars', 'livingfitapparel'); ?></option>
                                    <option value="1"><?php _e('FILTER BY: 1 Star', 'livingfitapparel'); ?></option>
                                </select>
                            </div>
                        </div>

                        <!-- Reviews List -->
                        <div class="lfa-reviews-list">
                            <?php foreach ($reviews as $review):
                                $rating = intval(get_comment_meta($review->comment_ID, 'rating', true));
                                $avatar = get_avatar($review->comment_author_email, 48);
                                $date = get_comment_date('jS M Y', $review->comment_ID);
                                ?>
                                <div class="lfa-review-item" data-rating="<?php echo esc_attr($rating); ?>" data-timestamp="<?php echo esc_attr(strtotime($review->comment_date)); ?>">
                                    <div class="lfa-review-header">
                                        <div class="lfa-review-author-info">
                                            <?php echo $avatar; ?>
                                            <div class="lfa-review-author-details">
                                                <div class="lfa-review-author-name"><?php echo esc_html($review->comment_author); ?>
                                                </div>
                                                <div class="lfa-review-stars">
                                                    <?php
                                                    for ($i = 1; $i <= 5; $i++) {
                                                        if ($i <= $rating) {
                                                            echo '<span class="star filled">★</span>';
                                                        } else {
                                                            echo '<span class="star">★</span>';
                                                        }
                                                    }
                                                    ?>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="lfa-review-date"><?php echo esc_html($date); ?></div>
                                    </div>
                                    <div class="lfa-review-content">
                                        <?php echo wp_kses_post($review->comment_content); ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>

                    <?php endif; ?>

                    <!-- Write Review Button -->
                    <div class="lfa-reviews-write">
                        <button type="button" class="lfa-write-review-btn" id="lfa-write-review-trigger">
                            <?php _e('WRITE A REVIEW', 'livingfitapparel'); ?>
                        </button>
                    </div>

                    <!-- Review Form (initially hidden) -->
                    <div class="lfa-review-form-wrapper" id="lfa-review-form-wrapper" style="display: none;">
                        <?php
                        if (comments_open($product->get_id())) {
                            ?>
                            <form class="lfa-review-form" id="lfa-review-form" method="post" action="<?php echo esc_url(site_url('/wp-comments-post.php')); ?>">
                                <h3 class="lfa-review-form-title"><?php _e('Write a Review', 'livingfitapparel'); ?></h3>
                                
                                <!-- Star Rating Input -->
                                <div class="lfa-review-rating-field">
                                    <label class="lfa-review-rating-label"><?php _e('Your Rating', 'livingfitapparel'); ?></label>
                                    <div class="lfa-review-rating-stars" id="lfa-review-rating-stars">
                                        <input type="hidden" name="rating" id="lfa-rating-value" value="0" required>
                                        <?php for ($i = 1; $i <= 5; $i++): ?>
                                            <button type="button" class="lfa-rating-star" data-rating="<?php echo $i; ?>" aria-label="<?php printf(__('Rate %d out of 5', 'livingfitapparel'), $i); ?>">
                                                <span class="star">★</span>
                                            </button>
                                        <?php endfor; ?>
                                    </div>
                                    <span class="lfa-rating-error" style="display: none; color: #d63638; font-size: 12px; margin-top: 5px;"><?php _e('Please select a rating', 'livingfitapparel'); ?></span>
                                </div>
                                
                                <?php
                                // Get the comment form fields
                                $commenter = wp_get_current_commenter();
                                $req = get_option('require_name_email');
                                $aria_req = ($req ? " aria-required='true'" : '');
                                
                                // Comment field
                                ?>
                                <div class="lfa-review-comment-field">
                                    <label for="comment"><?php _e('Your Review', 'livingfitapparel'); ?><?php if ($req): ?> <span class="required">*</span><?php endif; ?></label>
                                    <textarea id="comment" name="comment" cols="45" rows="8" required aria-required="true" placeholder="<?php esc_attr_e('Write your review here...', 'livingfitapparel'); ?>"></textarea>
                                </div>
                                
                                <?php if (!is_user_logged_in()): ?>
                                <div class="lfa-review-author-fields">
                                    <div class="lfa-review-author-field">
                                        <label for="author"><?php _e('Name', 'livingfitapparel'); ?><?php if ($req): ?> <span class="required">*</span><?php endif; ?></label>
                                        <input id="author" name="author" type="text" value="<?php echo esc_attr($commenter['comment_author']); ?>" size="30"<?php echo $aria_req; ?> placeholder="<?php esc_attr_e('Your name', 'livingfitapparel'); ?>" />
                                    </div>
                                    <div class="lfa-review-email-field">
                                        <label for="email"><?php _e('Email', 'livingfitapparel'); ?><?php if ($req): ?> <span class="required">*</span><?php endif; ?></label>
                                        <input id="email" name="email" type="email" value="<?php echo esc_attr($commenter['comment_author_email']); ?>" size="30"<?php echo $aria_req; ?> placeholder="<?php esc_attr_e('your@email.com', 'livingfitapparel'); ?>" />
                                    </div>
                                </div>
                                <?php endif; ?>
                                
                                <input type="hidden" name="comment_post_ID" value="<?php echo esc_attr($product->get_id()); ?>" />
                                <input type="hidden" name="comment_parent" value="0" />
                                <?php wp_nonce_field('woocommerce-review_rating', 'woocommerce-review-rating-nonce'); ?>
                                
                                <div class="lfa-review-submit-wrapper">
                                    <button type="submit" class="lfa-review-submit" id="lfa-review-submit">
                                        <?php _e('Submit Review', 'livingfitapparel'); ?>
                                    </button>
                                </div>
                            </form>
                            <?php
                        }
                        ?>
                    </div>
                </div>
            </section>
        <?php endif; ?>

        <!-- Section 4: Upsells -->
        <?php
        $section4_title = lfa_get('single_product.section4_title', __('More For You', 'livingfitapparel'));
        $upsell_ids = $product->get_upsell_ids();
        
        if (!empty($section4_title) && !empty($upsell_ids)):
            ?>
            <section class="lfa-product-section-4">
                <div class="container">
                    <div class="lfa-sec-head">
                        <h2><?php echo esc_html($section4_title); ?></h2>
                        <?php if (function_exists('wc_get_page_id')): ?>
                            <a class="lfa-viewall"
                                href="<?php echo esc_url(get_permalink(wc_get_page_id('shop'))); ?>"><?php _e('VIEW ALL', 'livingfitapparel'); ?></a>
                        <?php endif; ?>
                    </div>
                    <div id="lfa-upsells-slider">
                        <?php
                        // Limit to 8 upsells
                        $upsell_ids = array_slice($upsell_ids, 0, 8);
                        echo do_shortcode('[products ids="' . implode(',', $upsell_ids) . '" columns="4"]');
                        ?>
                    </div>
                </div>
            </section>
        <?php endif; ?>

        </div>
        <!-- End of Actual Content -->

    </div>

    <?php
}

/**
 * Hook: woocommerce_after_main_content.
 *
 * @hooked woocommerce_output_content_wrapper_end - 10 (outputs closing divs for the content)
 */
do_action('woocommerce_after_main_content');

get_footer('shop');
