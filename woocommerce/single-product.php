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

defined( 'ABSPATH' ) || exit;

get_header( 'shop' );

/**
 * Hook: woocommerce_before_main_content.
 *
 * @hooked woocommerce_output_content_wrapper - 10 (outputs opening divs for the content)
 * @hooked woocommerce_breadcrumb - 20
 */
do_action( 'woocommerce_before_main_content' );

while ( have_posts() ) {
    the_post();
    global $product;
    ?>
    
    <div id="product-<?php the_ID(); ?>" <?php wc_product_class( 'lfa-single-product', $product ); ?>>
        
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
                                            <a href="#reviews"><?php printf(_n('%d Customer Review', '%d Customer Reviews', $review_count, 'livingfitapparel'), $review_count); ?></a>
                                        </span>
                                        <span class="lfa-reviews-separator">|</span>
                                        <span class="lfa-add-review">
                                            <a href="#reviews"><?php _e('Add a review', 'livingfitapparel'); ?></a>
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
                            
                            <!-- Add to Cart Section -->
                            <?php if (!$product->is_type('variable')): ?>
                                <div class="lfa-product-add-to-cart-section">
                                    <form class="cart" action="<?php echo esc_url(apply_filters('woocommerce_add_to_cart_form_action', $product->get_permalink())); ?>" method="post" enctype='multipart/form-data'>
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
                                            <button type="submit" name="add-to-cart" value="<?php echo esc_attr($product->get_id()); ?>" class="lfa-add-to-cart-btn single_add_to_cart_button button alt">
                                                <span class="lfa-cart-btn-text"><?php _e('ADD TO CART', 'livingfitapparel'); ?></span>
                                                <span class="lfa-cart-btn-price"><?php echo $product->get_price_html(); ?></span>
                                            </button>
                                            <button type="button" class="lfa-wishlist-btn">
                                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                                                    <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path>
                                                </svg>
                                            </button>
                                        </div>
                                        
                                        <?php do_action('woocommerce_after_add_to_cart_button'); ?>
                                    </form>
                                    
                                    <!-- Buy It Now Button -->
                                    <a href="<?php echo esc_url(wc_get_checkout_url() . '?add-to-cart=' . $product->get_id()); ?>" class="lfa-buy-now-btn">
                                        <?php _e('BUY IT NOW', 'livingfitapparel'); ?>
                                    </a>
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
        ?>
        
        <!-- Section 2: Why You Need This -->
        <section class="lfa-product-section-2">
            <div class="container">
                <h2 class="lfa-section-title"><?php _e('Why you need this', 'livingfitapparel'); ?></h2>
                
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
                                    <button type="button" class="lfa-why-tab active" data-tab="description"><?php _e('DESCRIPTION', 'livingfitapparel'); ?></button>
                                <?php endif; ?>
                                <?php if ($show_description_tab && $show_size_chart_tab): ?>
                                    <span class="lfa-why-tab-separator">|</span>
                                <?php endif; ?>
                                <?php if ($show_size_chart_tab): ?>
                                    <button type="button" class="lfa-why-tab <?php echo !$show_description_tab ? 'active' : ''; ?>" data-tab="size-chart"><?php _e('SIZE CHART', 'livingfitapparel'); ?></button>
                                <?php endif; ?>
                            </div>
                            
                            <!-- Tab Contents -->
                            <div class="lfa-why-tab-contents">
                                <!-- Description Tab -->
                                <?php if ($show_description_tab): ?>
                                    <div class="lfa-why-tab-content active" data-content="description">
                                        <?php if ($product->get_short_description()): ?>
                                            <div class="lfa-why-description">
                                                <?php echo apply_filters('woocommerce_short_description', $product->get_short_description()); ?>
                                            </div>
                                        <?php endif; ?>
                                        
                                        <?php if ($why_length || $why_material): ?>
                                            <div class="lfa-why-specs">
                                                <?php if ($why_length): ?>
                                                    <div class="lfa-why-spec-item">
                                                        <span class="lfa-spec-icon">
                                                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                                <rect x="3" y="3" width="18" height="18" rx="2" ry="2"/>
                                                                <line x1="3" y1="9" x2="21" y2="9"/>
                                                                <line x1="9" y1="21" x2="9" y2="9"/>
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
                                                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                                                <path d="M4 4h16v16H4z"/>
                                                                <path d="M4 4l8 8 8-8"/>
                                                                <path d="M4 20l8-8 8 8"/>
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
                                    <div class="lfa-why-tab-content <?php echo !$show_description_tab ? 'active' : ''; ?>" data-content="size-chart">
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
        
        <?php
        /**
         * Hook: woocommerce_after_single_product_summary.
         *
         * @hooked woocommerce_output_product_data_tabs - 10
         * @hooked woocommerce_upsell_display - 15
         * @hooked woocommerce_output_related_products - 20
         */
        do_action( 'woocommerce_after_single_product_summary' );
        ?>
        
    </div>
    
    <?php
}

/**
 * Hook: woocommerce_after_main_content.
 *
 * @hooked woocommerce_output_content_wrapper_end - 10 (outputs closing divs for the content)
 */
do_action( 'woocommerce_after_main_content' );

get_footer( 'shop' );
