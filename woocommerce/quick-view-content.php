<?php
/**
 * Quick View Content Template
 * This template is loaded via AJAX into the quick view modal
 *
 * @package livingfitapparel
 */

defined('ABSPATH') || exit;

global $product;

if (!$product) {
    return;
}

// Get product images
$image_ids = array();
$main_image_id = $product->get_image_id();
if ($main_image_id) {
    $image_ids[] = $main_image_id;
}
$gallery_ids = $product->get_gallery_image_ids();
if (!empty($gallery_ids)) {
    $image_ids = array_merge($image_ids, $gallery_ids);
}

// Get selected color from global variable (passed from AJAX)
global $lfa_selected_color;
$selected_color_slug = isset($lfa_selected_color) ? $lfa_selected_color : '';

// Get product attributes for variations
$attributes = $product->get_attributes();
$is_variable = $product->is_type('variable');
$is_composite = $product->is_type('composite');
$selected_variation_id = 0;
$selected_attributes = array();

// Get variations data for color swatches (with all image data)
$variations_data = array();
if ($is_variable) {
    $variation_ids = $product->get_children();
    foreach ($variation_ids as $variation_id) {
        $variation_obj = wc_get_product($variation_id);
        if ($variation_obj && $variation_obj->is_purchasable()) {
            $variation_attributes = $variation_obj->get_attributes();
            $color_slug = '';
            
            if (isset($variation_attributes['pa_color'])) {
                $color_slug = $variation_attributes['pa_color'];
            } elseif (isset($variation_attributes['attribute_pa_color'])) {
                $color_slug = $variation_attributes['attribute_pa_color'];
            } else {
                $variation_data = $variation_obj->get_data();
                if (isset($variation_data['attributes']['pa_color'])) {
                    $color_slug = $variation_data['attributes']['pa_color'];
                } elseif (isset($variation_data['attributes']['attribute_pa_color'])) {
                    $color_slug = $variation_data['attributes']['attribute_pa_color'];
                }
            }
            
            if ($color_slug) {
                $variation_image_id = $variation_obj->get_image_id();
                if ($variation_image_id) {
                    $variation_image_url = wp_get_attachment_image_url($variation_image_id, 'woocommerce_single');
                    $variation_image_srcset = wp_get_attachment_image_srcset($variation_image_id, 'woocommerce_single');
                    $variation_image_sizes = wp_get_attachment_image_sizes($variation_image_id, 'woocommerce_single');
                    $image_meta = wp_get_attachment_metadata($variation_image_id);
                    $variation_image_width = isset($image_meta['width']) ? $image_meta['width'] : '';
                    $variation_image_height = isset($image_meta['height']) ? $image_meta['height'] : '';
                    
                    $variations_data[$color_slug] = array(
                        'image_id' => $variation_image_id,
                        'image_url' => $variation_image_url,
                        'image_srcset' => $variation_image_srcset,
                        'image_sizes' => $variation_image_sizes,
                        'image_width' => $variation_image_width,
                        'image_height' => $variation_image_height,
                        'variation_id' => $variation_id
                    );
                }
            }
        }
    }
}

// Stock status
$stock_status = $product->get_stock_status();
$stock_quantity = $product->get_stock_quantity();
$is_in_stock = $product->is_in_stock();
$is_purchasable = $product->is_purchasable();
?>

<div class="lfa-quick-view-wrapper">
    <div class="lfa-quick-view-columns">
        <!-- Left Column: Image Gallery -->
        <div class="lfa-quick-view-images">
            <?php if (!empty($image_ids)): ?>
                <div class="lfa-quick-view-slider">
                    <?php foreach ($image_ids as $img_id): ?>
                        <div class="lfa-quick-view-slide">
                            <?php echo wp_get_attachment_image($img_id, 'woocommerce_single', false, array('class' => 'lfa-quick-view-image')); ?>
                        </div>
                    <?php endforeach; ?>
                </div>
                <div class="lfa-quick-view-slider-nav"></div>
            <?php else: ?>
                <div class="lfa-quick-view-no-image">
                    <?php echo wc_placeholder_img('woocommerce_single'); ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Right Column: Product Details -->
        <div class="lfa-quick-view-details">
            <!-- Product Title -->
            <h2 class="lfa-quick-view-title"><?php echo esc_html($product->get_name()); ?></h2>

            <!-- Star Rating and Review Count -->
            <?php
            $rating_count = $product->get_rating_count();
            $average_rating = $product->get_average_rating();
            if ($rating_count > 0):
                // Round average rating to nearest integer for star display
                $rating_rounded = round($average_rating);
                // Generate custom stars: filled stars + empty stars
                $filled_stars = str_repeat('★', $rating_rounded);
                $empty_stars = str_repeat('☆', max(0, 5 - $rating_rounded));
                $stars = $filled_stars . $empty_stars;
            ?>
                <div class="lfa-quick-view-rating">
                    <span class="lfa-quick-view-stars"><?php echo esc_html($stars); ?></span>
                    <span class="lfa-quick-view-review-count">
                        <?php printf(_n('%d Review', '%d Reviews', $rating_count, 'livingfitapparel'), $rating_count); ?>
                    </span>
                </div>
            <?php endif; ?>

            <!-- Product Price -->
            <div class="lfa-quick-view-price">
                <?php echo $product->get_price_html(); ?>
            </div>

            <!-- Composite Product Notice -->
            <?php if ($is_composite): ?>
                <div class="lfa-quick-view-composite-notice">
                    <p><?php esc_html_e('This is a customizable product bundle. Click "View Product" to configure your bundle and add it to your cart.', 'livingfitapparel'); ?></p>
                </div>
            <?php endif; ?>

            <!-- Product Variations -->
            <?php if ($is_variable && !empty($attributes) && !$is_composite): ?>
                <form class="lfa-quick-view-variations-form" data-product-id="<?php echo esc_attr($product->get_id()); ?>">
                    <?php
                    foreach ($attributes as $attribute_name => $attribute):
                        $attribute_taxonomy = wc_attribute_taxonomy_name_by_id(wc_attribute_taxonomy_id_by_name($attribute_name));
                        $attribute_label = wc_attribute_label($attribute_name);
                        $attribute_name_clean = sanitize_title($attribute_name);
                        // Format attribute name for WooCommerce: use taxonomy name with 'attribute_' prefix if it's a taxonomy
                        // WooCommerce expects format: 'attribute_pa_size' or 'pa_size'
                        if ($attribute_taxonomy) {
                            $woo_attribute_name = 'attribute_' . $attribute_taxonomy;
                        } else {
                            $woo_attribute_name = 'attribute_' . $attribute_name_clean;
                        }
                        
                        // Check if it's color attribute
                        $is_color = (strpos($attribute_name, 'color') !== false || $attribute_taxonomy === 'pa_color');
                        
                        if ($is_color):
                            // Color swatches
                            $color_terms = wp_get_post_terms($product->get_id(), $attribute_taxonomy ? $attribute_taxonomy : 'pa_color', array('fields' => 'all'));
                            if (!empty($color_terms)):
                    ?>
                                <div class="lfa-quick-view-variation lfa-quick-view-variation-color">
                                    <label class="lfa-quick-view-variation-label">
                                        <?php echo esc_html($attribute_label); ?>:
                                        <span class="lfa-quick-view-selected-value"></span>
                                    </label>
                                    <div class="lfa-quick-view-color-swatches">
                                        <?php foreach ($color_terms as $term):
                                            $color_slug = $term->slug;
                                            $color_name = $term->name;
                                            
                                            // Get color hex
                                            $color_hex = get_term_meta($term->term_id, 'color', true);
                                            if (empty($color_hex)) {
                                                $color_hex = get_term_meta($term->term_id, 'product_attribute_color', true);
                                            }
                                            if (empty($color_hex)) {
                                                $color_hex = get_term_meta($term->term_id, 'pa_color', true);
                                            }
                                            if (empty($color_hex)) {
                                                $color_hex = lfa_get_color_hex_from_name($color_name);
                                            }
                                            
                                            // Get variation image data
                                            $variation_image_url = '';
                                            $variation_image_id = '';
                                            $variation_image_srcset = '';
                                            $variation_image_sizes = '';
                                            $variation_image_width = '';
                                            $variation_image_height = '';
                                            if (isset($variations_data[$color_slug])) {
                                                $variation_image_url = $variations_data[$color_slug]['image_url'];
                                                $variation_image_id = isset($variations_data[$color_slug]['image_id']) ? $variations_data[$color_slug]['image_id'] : '';
                                                $variation_image_srcset = isset($variations_data[$color_slug]['image_srcset']) ? $variations_data[$color_slug]['image_srcset'] : '';
                                                $variation_image_sizes = isset($variations_data[$color_slug]['image_sizes']) ? $variations_data[$color_slug]['image_sizes'] : '';
                                                $variation_image_width = isset($variations_data[$color_slug]['image_width']) ? $variations_data[$color_slug]['image_width'] : '';
                                                $variation_image_height = isset($variations_data[$color_slug]['image_height']) ? $variations_data[$color_slug]['image_height'] : '';
                                            }
                                            
                                            // Check if this is the selected color
                                            $is_selected = ($selected_color_slug && $color_slug === $selected_color_slug);
                                        ?>
                                            <button type="button" 
                                                    class="lfa-quick-view-color-swatch <?php echo empty($variation_image_url) ? 'lfa-quick-view-color-swatch-no-image' : ''; ?> <?php echo $is_selected ? 'selected' : ''; ?>"
                                                    data-attribute-name="<?php echo esc_attr($woo_attribute_name); ?>"
                                                    data-attribute-value="<?php echo esc_attr($color_slug); ?>"
                                                    data-attribute-label="<?php echo esc_attr($color_name); ?>"
                                                    data-image-url="<?php echo esc_url($variation_image_url); ?>"
                                                    <?php if ($variation_image_id): ?>data-image-id="<?php echo esc_attr($variation_image_id); ?>"<?php endif; ?>
                                                    <?php if ($variation_image_srcset): ?>data-image-srcset="<?php echo esc_attr($variation_image_srcset); ?>"<?php endif; ?>
                                                    <?php if ($variation_image_sizes): ?>data-image-sizes="<?php echo esc_attr($variation_image_sizes); ?>"<?php endif; ?>
                                                    <?php if ($variation_image_width): ?>data-image-width="<?php echo esc_attr($variation_image_width); ?>"<?php endif; ?>
                                                    <?php if ($variation_image_height): ?>data-image-height="<?php echo esc_attr($variation_image_height); ?>"<?php endif; ?>
                                                    style="background-color: <?php echo esc_attr($color_hex ? $color_hex : '#ccc'); ?>;"
                                                    aria-label="<?php echo esc_attr(sprintf(__('Select %s', 'livingfitapparel'), $color_name)); ?>">
                                            </button>
                                            <?php if ($is_selected): ?>
                                            <script type="text/javascript">
                                            jQuery(document).ready(function($) {
                                                // Set selected value display
                                                $('.lfa-quick-view-color-swatch.selected').closest('.lfa-quick-view-variation-color').find('.lfa-quick-view-selected-value').text('<?php echo esc_js($color_name); ?>');
                                            });
                                            </script>
                                            <?php endif; ?>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                    <?php
                            endif;
                        else:
                            // Regular attribute buttons (not dropdowns)
                            $attribute_options = $attribute->get_options();
                            if (!empty($attribute_options)):
                    ?>
                                <div class="lfa-quick-view-variation lfa-quick-view-variation-buttons">
                                    <label class="lfa-quick-view-variation-label">
                                        <?php echo esc_html($attribute_label); ?>:
                                        <span class="lfa-quick-view-selected-value"></span>
                                    </label>
                                    <div class="lfa-quick-view-attribute-buttons">
                                        <?php
                                        foreach ($attribute_options as $option):
                                            $term = get_term($option);
                                            if ($term && !is_wp_error($term)):
                                                $option_value = $term->slug;
                                                $option_label = $term->name;
                                            else:
                                                $option_value = $option;
                                                $option_label = $option;
                                            endif;
                                        ?>
                                            <button type="button" 
                                                    class="lfa-quick-view-attribute-btn"
                                                    data-attribute-name="<?php echo esc_attr($woo_attribute_name); ?>"
                                                    data-attribute-value="<?php echo esc_attr($option_value); ?>"
                                                    data-attribute-label="<?php echo esc_attr($option_label); ?>">
                                                <?php echo esc_html($option_label); ?>
                                            </button>
                                        <?php endforeach; ?>
                                    </div>
                                    <input type="hidden" 
                                           name="attribute_<?php echo esc_attr($attribute_name_clean); ?>" 
                                           class="lfa-quick-view-variation-select"
                                           data-attribute-name="<?php echo esc_attr($woo_attribute_name); ?>"
                                           value="">
                                </div>
                    <?php
                            endif;
                        endif;
                    endforeach;
                    ?>
                    
                    <input type="hidden" name="product_id" value="<?php echo esc_attr($product->get_id()); ?>">
                    <input type="hidden" name="variation_id" class="lfa-quick-view-variation-id" value="0">
                </form>
            <?php endif; ?>

            <!-- Add to Cart Button -->
            <div class="lfa-quick-view-add-to-cart">
                <?php
                // Composite products - show "View Product" button
                if ($is_composite):
                ?>
                    <a href="<?php echo esc_url($product->get_permalink()); ?>" 
                       class="button lfa-quick-view-view-product-btn">
                        <?php esc_html_e('View Product', 'livingfitapparel'); ?>
                    </a>
                <?php else:
                    // Regular products, variable products, etc.
                    $button_text = __('Add to cart', 'livingfitapparel');
                    $button_disabled = false;
                    $button_class = 'button lfa-quick-view-atc-btn';
                    
                    if (!$is_purchasable) {
                        $button_text = __('Read more', 'livingfitapparel');
                        $button_class .= ' lfa-quick-view-read-more';
                    } elseif (!$is_in_stock || $stock_status === 'outofstock') {
                        $button_text = __('Sold out', 'livingfitapparel');
                        $button_disabled = true;
                        $button_class .= ' lfa-quick-view-sold-out';
                    } elseif ($is_variable) {
                        $button_disabled = true;
                        $button_class .= ' lfa-quick-view-disabled';
                    }
                    
                    if ($is_variable && !$button_disabled):
                    ?>
                        <button type="button" 
                                class="<?php echo esc_attr($button_class); ?>" 
                                <?php echo $button_disabled ? 'disabled' : ''; ?>
                                data-product-id="<?php echo esc_attr($product->get_id()); ?>">
                            <?php echo esc_html($button_text); ?>
                        </button>
                    <?php else: ?>
                        <a href="<?php echo esc_url($product->add_to_cart_url()); ?>" 
                           class="<?php echo esc_attr($button_class); ?>"
                           <?php echo $button_disabled ? 'onclick="return false;" style="pointer-events: none; opacity: 0.5;"' : ''; ?>>
                            <?php echo esc_html($button_text); ?>
                        </a>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
(function($) {
    // Get wrapper reference - available to all code
    var $wrapper = $('.lfa-quick-view-wrapper');
    
    // Slider initialization is now handled in sliders.js
    
    // Handle variation selection for variable products
    var $form = $wrapper.find('.lfa-quick-view-variations-form');
    if ($form.length) {
        var productId = $form.data('product-id');
        var selectedAttributes = {};
        var $atcBtn = $wrapper.find('.lfa-quick-view-atc-btn');
        
        // Store original gallery images data
        var originalImages = [];
        var $slider = $wrapper.find('.lfa-quick-view-slider');
        $slider.find('.lfa-quick-view-slide img').each(function(index) {
            var $img = $(this);
            originalImages[index] = {
                src: $img.attr('src'),
                srcset: $img.attr('srcset') || '',
                sizes: $img.attr('sizes') || '',
                width: $img.attr('width') || '',
                height: $img.attr('height') || ''
            };
        });
        
        // Function to restore original gallery images
        function restoreOriginalImages() {
            $slider.find('.lfa-quick-view-slide img').each(function(index) {
                if (originalImages[index]) {
                    var $img = $(this);
                    var orig = originalImages[index];
                    $img.attr('src', orig.src);
                    if (orig.srcset) {
                        $img.attr('srcset', orig.srcset);
                    } else {
                        $img.removeAttr('srcset');
                    }
                    if (orig.sizes) {
                        $img.attr('sizes', orig.sizes);
                    } else {
                        $img.removeAttr('sizes');
                    }
                    if (orig.width) {
                        $img.attr('width', orig.width);
                    }
                    if (orig.height) {
                        $img.attr('height', orig.height);
                    }
                }
            });
            if ($slider.hasClass('slick-initialized')) {
                $slider.slick('slickGoTo', 0);
                $slider.slick('refresh');
            }
        }
        
        // Color swatch selection
        $wrapper.on('click', '.lfa-quick-view-color-swatch', function() {
            var $swatch = $(this);
            var attributeName = $swatch.data('attribute-name');
            var attributeValue = $swatch.data('attribute-value');
            var attributeLabel = $swatch.data('attribute-label');
            var imageUrl = $swatch.data('image-url');
            var imageSrcset = $swatch.data('image-srcset') || '';
            var imageSizes = $swatch.data('image-sizes') || '';
            var imageWidth = $swatch.data('image-width') || '';
            var imageHeight = $swatch.data('image-height') || '';
            
            // Update selected state
            $wrapper.find('.lfa-quick-view-color-swatch').removeClass('selected');
            $swatch.addClass('selected');
            
            // Update selected value display
            $swatch.closest('.lfa-quick-view-variation-color').find('.lfa-quick-view-selected-value').text(attributeLabel);
            
            // Store selected attribute - use the attribute name as-is (already formatted in PHP)
            selectedAttributes[attributeName] = attributeValue;
            
            // Update slider images: variation image first, then all gallery images
            if (imageUrl) {
                var $slider = $wrapper.find('.lfa-quick-view-slider');
                if ($slider.hasClass('slick-initialized')) {
                    // Update only the first slide with variation image
                    var $firstSlide = $slider.find('.lfa-quick-view-slide').first();
                    var $firstImg = $firstSlide.find('img');
                    if ($firstImg.length) {
                        $firstImg.attr('src', imageUrl);
                        if (imageSrcset) {
                            $firstImg.attr('srcset', imageSrcset);
                        } else {
                            $firstImg.removeAttr('srcset');
                        }
                        if (imageSizes) {
                            $firstImg.attr('sizes', imageSizes);
                        } else {
                            $firstImg.removeAttr('sizes');
                        }
                        if (imageWidth) {
                            $firstImg.attr('width', imageWidth);
                        }
                        if (imageHeight) {
                            $firstImg.attr('height', imageHeight);
                        }
                    }
                    // Keep all other slides as original gallery images (they're already correct)
                    // Go to first slide
                    $slider.slick('slickGoTo', 0);
                    $slider.slick('refresh');
                } else {
                    // If slider not initialized yet, update first image only
                    var $firstImg = $wrapper.find('.lfa-quick-view-slide').first().find('img');
                    if ($firstImg.length) {
                        $firstImg.attr('src', imageUrl);
                        if (imageSrcset) {
                            $firstImg.attr('srcset', imageSrcset);
                        }
                        if (imageSizes) {
                            $firstImg.attr('sizes', imageSizes);
                        }
                        if (imageWidth) {
                            $firstImg.attr('width', imageWidth);
                        }
                        if (imageHeight) {
                            $firstImg.attr('height', imageHeight);
                        }
                    }
                }
            } else {
                // No variation image, restore original images
                restoreOriginalImages();
            }
            
            // Check for matching variation
            checkVariation();
        });
        
        // Attribute button selection (for non-color attributes)
        $wrapper.on('click', '.lfa-quick-view-attribute-btn', function() {
            var $btn = $(this);
            var $variation = $btn.closest('.lfa-quick-view-variation-buttons');
            var attributeName = $btn.data('attribute-name');
            var attributeValue = $btn.data('attribute-value');
            var attributeLabel = $btn.data('attribute-label');
            
            // Update selected state
            $variation.find('.lfa-quick-view-attribute-btn').removeClass('selected');
            $btn.addClass('selected');
            
            // Update hidden input
            $variation.find('.lfa-quick-view-variation-select').val(attributeValue);
            
            // Update selected value display
            $variation.find('.lfa-quick-view-selected-value').text(attributeLabel);
            
            // Store selected attribute - use the attribute name as-is (already formatted in PHP)
            selectedAttributes[attributeName] = attributeValue;
            
            // Check for matching variation
            checkVariation();
        });
        
        // Dropdown selection (fallback, if any remain)
        $wrapper.on('change', '.lfa-quick-view-variation-select', function() {
            var $select = $(this);
            var attributeName = $select.data('attribute-name');
            var attributeValue = $select.val();
            
            if (attributeValue) {
                // Use the attribute name as-is (already formatted in PHP)
                selectedAttributes[attributeName] = attributeValue;
            } else {
                delete selectedAttributes[attributeName];
            }
            
            checkVariation();
        });
        
        // Handle pre-selected color on page load
        // Wait for slider to be initialized first
        setTimeout(function() {
            var $preSelectedSwatch = $wrapper.find('.lfa-quick-view-color-swatch.selected');
            if ($preSelectedSwatch.length) {
                // Store original images first if not already stored
                if (originalImages.length === 0) {
                    $slider.find('.lfa-quick-view-slide img').each(function(index) {
                        var $img = $(this);
                        originalImages[index] = {
                            src: $img.attr('src'),
                            srcset: $img.attr('srcset') || '',
                            sizes: $img.attr('sizes') || '',
                            width: $img.attr('width') || '',
                            height: $img.attr('height') || ''
                        };
                    });
                }
                // Trigger click to update images and selected value
                $preSelectedSwatch.trigger('click');
            }
        }, 500);
        
        // Check if all required attributes are selected and find matching variation
        function checkVariation() {
            // Count unique attributes (each variation group counts as one)
            var $variationGroups = $wrapper.find('.lfa-quick-view-variation');
            var requiredAttributes = $variationGroups.length;
            var selectedCount = Object.keys(selectedAttributes).length;
            
            if (selectedCount === requiredAttributes && requiredAttributes > 0) {
                // Find matching variation via AJAX
                $.ajax({
                    url: LFA.ajaxUrl,
                    type: 'POST',
                    data: {
                        action: 'lfa_find_variation',
                        product_id: productId,
                        attributes: selectedAttributes,
                        nonce: LFA.nonce
                    },
                    success: function(response) {
                        if (response.success && response.data.variation_id) {
                            $wrapper.find('.lfa-quick-view-variation-id').val(response.data.variation_id);
                            $atcBtn.removeClass('lfa-quick-view-disabled').prop('disabled', false);
                            // Remove inline styles that might block pointer events
                            $atcBtn.css({
                                'pointer-events': 'auto',
                                'opacity': ''
                            });
                            
                            // Update price if available
                            if (response.data.price_html) {
                                $wrapper.find('.lfa-quick-view-price').html(response.data.price_html);
                            }
                            
                            // Update stock status
                            if (response.data.stock_status === 'outofstock' || !response.data.is_in_stock) {
                                $atcBtn.addClass('lfa-quick-view-sold-out').text('<?php esc_attr_e('Sold out', 'livingfitapparel'); ?>').prop('disabled', true).css('pointer-events', 'none');
                            } else {
                                $atcBtn.removeClass('lfa-quick-view-sold-out').text('<?php esc_attr_e('Add to cart', 'livingfitapparel'); ?>').prop('disabled', false).css('pointer-events', 'auto');
                            }
                        } else {
                            $atcBtn.addClass('lfa-quick-view-disabled').prop('disabled', true).css('pointer-events', 'none');
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('Error finding variation:', error);
                        $atcBtn.addClass('lfa-quick-view-disabled').prop('disabled', true).css('pointer-events', 'none');
                    }
                });
            } else {
                $atcBtn.addClass('lfa-quick-view-disabled').prop('disabled', true).css('pointer-events', 'none');
                $wrapper.find('.lfa-quick-view-variation-id').val(0);
            }
        }
        
        // Handle add to cart for variable products
        $wrapper.on('click', '.lfa-quick-view-atc-btn', function(e) {
            e.preventDefault();
            var $btn = $(this);
            // Check if button is disabled or has pointer-events: none
            if ($btn.prop('disabled') || $btn.hasClass('lfa-quick-view-disabled') || $btn.css('pointer-events') === 'none') {
                return;
            }
            var variationId = $wrapper.find('.lfa-quick-view-variation-id').val();
            if (!variationId || variationId == 0) {
                return;
            }
            
            var originalText = $btn.text();
            $btn.prop('disabled', true).text('<?php esc_attr_e('Adding...', 'livingfitapparel'); ?>');
            
            $.ajax({
                url: LFA.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'lfa_add_to_cart',
                    product_id: productId,
                    variation_id: variationId,
                    quantity: 1,
                    nonce: LFA.nonce
                },
                success: function(response) {
                    if (response.success) {
                        $btn.text('<?php esc_attr_e('Added!', 'livingfitapparel'); ?>');
                        // Trigger cart update event
                        $(document.body).trigger('added_to_cart', [response.data.fragments, response.data.cart_hash, $btn]);
                        setTimeout(function() {
                            $btn.prop('disabled', false).css('pointer-events', 'auto').text(originalText);
                        }, 1000);
                    } else {
                        alert(response.data.message || '<?php esc_attr_e('Error adding product to cart', 'livingfitapparel'); ?>');
                        $btn.prop('disabled', false).css('pointer-events', 'auto').text(originalText);
                    }
                },
                error: function() {
                    alert('<?php esc_attr_e('Error adding product to cart', 'livingfitapparel'); ?>');
                    $btn.prop('disabled', false).css('pointer-events', 'auto').text(originalText);
                }
            });
        });
    }
})(jQuery);
</script>
