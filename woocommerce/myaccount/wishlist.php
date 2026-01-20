<?php
/**
 * My Wishlist
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/myaccount/wishlist.php.
 *
 * @see     https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 2.6.0
 */

defined('ABSPATH') || exit;

do_action('woocommerce_before_account_wishlist'); ?>

<div class="woocommerce-MyAccount-content">
    <h2 class="woocommerce-MyAccount-content-title" style="border:0; padding-inline:0;">WISHLIST</h2>

    <?php
    // Check for TI WooCommerce Wishlist plugin
    if (defined('TINVWL_VERSION') || class_exists('TInvWL_Public_Wishlist_View')) {
        // Get user's default wishlist
        $wishlist = null;

        // Try multiple methods to get wishlist
        if (class_exists('TInvWL_Wishlist')) {
            $wl = new TInvWL_Wishlist();
            $wishlists = $wl->get_by_user(get_current_user_id());

            if (!empty($wishlists) && is_array($wishlists)) {
                // Get the first/default wishlist
                $wishlist = reset($wishlists);
            }
        }

        // Alternative: Try using tinv_wishlist_get helper function if available
        if (!$wishlist && function_exists('tinv_wishlist_get')) {
            $wishlist = tinv_wishlist_get();
        }

        // If we have a wishlist, get products
        if ($wishlist && isset($wishlist['ID'])) {
            $wishlist_id = $wishlist['ID'];

            // Get products from wishlist
            if (class_exists('TInvWL_Product')) {
                $wl_product = new TInvWL_Product($wishlist);
                $products = $wl_product->get_wishlist(array('count' => 999));

                if (!empty($products) && is_array($products)) {
                    ?>
                    <div class="tinv-wishlist">
                        <ul class="products">
                            <?php
                            foreach ($products as $wl_product_data) {
                                $product_id = isset($wl_product_data['product_id']) ? absint($wl_product_data['product_id']) : 0;
                                $variation_id = isset($wl_product_data['variation_id']) ? absint($wl_product_data['variation_id']) : 0;
                                $wishlist_product_id = isset($wl_product_data['ID']) ? absint($wl_product_data['ID']) : 0;

                                // Get product from data array if available (TI Wishlist stores it there)
                                if (isset($wl_product_data['data']) && is_a($wl_product_data['data'], 'WC_Product')) {
                                    $product = $wl_product_data['data'];
                                } else {
                                    // Fallback: Get product by ID
                                    $product = $variation_id > 0 ? wc_get_product($variation_id) : wc_get_product($product_id);
                                }

                                if (!$product || !$product->is_visible()) {
                                    continue;
                                }

                                // Get product attributes for variations and composite products
                                $attributes = array();
                                $is_composite = $product->is_type('composite');

                                // Handle composite products
                                if ($is_composite) {
                                    // Get composite configuration from meta
                                    $composite_config = isset($wl_product_data['meta']) ? $wl_product_data['meta'] : array();
                                    
                                    if (!empty($composite_config) && is_array($composite_config)) {
                                        // Try to extract component selections
                                        foreach ($composite_config as $key => $value) {
                                            // Look for component selection data
                                            if (strpos($key, 'wccp_component') !== false || 
                                                strpos($key, 'component_') !== false) {
                                                
                                                // Get component product
                                                if (is_numeric($value)) {
                                                    $component_product = wc_get_product($value);
                                                    if ($component_product) {
                                                        $attributes[] = $component_product->get_name();
                                                    }
                                                }
                                            }
                                        }
                                    }
                                    
                                    // Don't show anything if no components found - removed per user request
                                }
                                // Handle variable products
                                elseif ($variation_id > 0) {
                                    $variation_product = wc_get_product($variation_id);
                                    if ($variation_product && $variation_product->is_type('variation')) {
                                        // Get variation attributes
                                        $variation_attributes = $variation_product->get_variation_attributes();
                                        if (!empty($variation_attributes)) {
                                            foreach ($variation_attributes as $key => $value) {
                                                if (!empty($value)) {
                                                    // Remove 'attribute_' prefix if present
                                                    $taxonomy = str_replace('attribute_', '', $key);

                                                    // Get term name
                                                    $term = get_term_by('slug', $value, $taxonomy);
                                                    if ($term && !is_wp_error($term)) {
                                                        $attributes[] = $term->name;
                                                    } else {
                                                        $attributes[] = $value;
                                                    }
                                                }
                                            }
                                        }
                                    }
                                }
                                // Note: If variation_id is 0, it means a variable product was added without selecting a variation
                                // In this case, we cannot show attributes because no specific variation was chosen
            
                                // Build remove URL for TI Wishlist
                                $remove_url = '';
                                if ($wishlist_product_id > 0) {
                                    $base_url = wc_get_page_permalink('myaccount') . 'wishlist/';
                                    $remove_url = add_query_arg(array(
                                        'tinvwl-remove' => $wishlist_product_id,
                                        '_wpnonce' => wp_create_nonce('tinvwl_remove_' . $wishlist_product_id),
                                    ), $base_url);
                                }
                                // Determine product type class
                                $product_type_class = '';
                                if ($product->is_type('composite')) {
                                    $product_type_class = 'product-type-composite';
                                } elseif ($product->is_type('variation') || $product->is_type('variable')) {
                                    $product_type_class = 'product-type-variable';
                                } else {
                                    $product_type_class = 'product-type-simple';
                                }
                                ?>
                                <li>
                                    <div class="product <?php echo esc_attr($product_type_class); ?>">
                                        <div class="thumb">
                                            <?php if (!empty($remove_url)): ?>
                                                <a href="<?php echo esc_url($remove_url); ?>" class="lfa-wishlist-remove"
                                                    data-product-id="<?php echo esc_attr($wishlist_product_id); ?>"
                                                    aria-label="<?php esc_attr_e('Remove from wishlist', 'woocommerce'); ?>"
                                                    title="<?php esc_attr_e('Remove', 'woocommerce'); ?>">
                                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none"
                                                        xmlns="http://www.w3.org/2000/svg">
                                                        <path d="M18 6L6 18M6 6L18 18" stroke="#000000" stroke-width="2" stroke-linecap="round"
                                                            stroke-linejoin="round" />
                                                    </svg>
                                                </a>
                                            <?php endif; ?>
                                            <a href="<?php echo esc_url($product->get_permalink()); ?>">
                                                <?php echo $product->get_image('woocommerce_thumbnail'); ?>
                                            </a>
                                        </div>
                                        <div class="title">
                                            <a href="<?php echo esc_url($product->get_permalink()); ?>">
                                                <?php
                                                // Get parent product name for variations
                                                if ($product->is_type('variation')) {
                                                    $parent_id = $product->get_parent_id();
                                                    if ($parent_id) {
                                                        $parent_product = wc_get_product($parent_id);
                                                        if ($parent_product) {
                                                            echo esc_html($parent_product->get_name());
                                                        } else {
                                                            echo esc_html($product->get_name());
                                                        }
                                                    } else {
                                                        echo esc_html($product->get_name());
                                                    }
                                                }
                                                // Composite products
                                                elseif ($product->is_type('composite')) {
                                                    echo esc_html($product->get_name());
                                                }
                                                // All other product types
                                                else {
                                                    echo esc_html($product->get_name());
                                                }
                                                ?>
                                            </a>
                                        </div>
                                        <?php
                                        // Display attributes if we have them
                                        if (!empty($attributes)): ?>
                                            <div class="attributes">
                                                <?php echo esc_html(implode(' / ', $attributes)); ?>
                                            </div>
                                        <?php endif; ?>
                                        <div class="price">
                                            <?php echo $product->get_price_html(); ?>
                                        </div>
                                    </div>
                                </li>
                                <?php
                            }
                            ?>
                        </ul>
                    </div>
                    <?php
                } else {
                    ?>
                    <div
                        class="woocommerce-message woocommerce-message--info woocommerce-Message woocommerce-Message--info woocommerce-info">
                        <?php esc_html_e('No products in your wishlist.', 'woocommerce'); ?>
                    </div>
                    <?php
                }
            } else {
                ?>
                <div
                    class="woocommerce-message woocommerce-message--info woocommerce-Message woocommerce-Message--info woocommerce-info">
                    <?php esc_html_e('No products in your wishlist.', 'woocommerce'); ?>
                </div>
                <?php
            }
        } else {
            ?>
            <div
                class="woocommerce-message woocommerce-message--info woocommerce-Message woocommerce-Message--info woocommerce-info">
                <?php esc_html_e('No products in your wishlist.', 'woocommerce'); ?>
            </div>
            <?php
        }
    }
    // Check for YITH WooCommerce Wishlist plugin
    elseif (function_exists('yith_wcwl_get_wishlist_items')) {
        $wishlist_items = yith_wcwl_get_wishlist_items();

        if (!empty($wishlist_items)) {
            ?>
            <div class="woocommerce-wishlist">
                <ul class="products">
                    <?php
                    foreach ($wishlist_items as $item) {
                        $product_id = isset($item['product_id']) ? $item['product_id'] : 0;
                        $product = wc_get_product($product_id);

                        if (!$product || !$product->is_visible()) {
                            continue;
                        }
                        ?>
                        <li>
                            <div class="product">
                                <div class="thumb">
                                    <a href="<?php echo esc_url($product->get_permalink()); ?>">
                                        <?php echo $product->get_image('woocommerce_thumbnail'); ?>
                                    </a>
                                </div>
                                <div class="title">
                                    <a href="<?php echo esc_url($product->get_permalink()); ?>">
                                        <?php echo esc_html($product->get_name()); ?>
                                    </a>
                                </div>
                                <div class="price">
                                    <?php echo $product->get_price_html(); ?>
                                </div>
                            </div>
                        </li>
                        <?php
                    }
                    ?>
                </ul>
            </div>
            <?php
        } else {
            ?>
            <div
                class="woocommerce-message woocommerce-message--info woocommerce-Message woocommerce-Message--info woocommerce-info">
                <?php esc_html_e('No products in your wishlist.', 'woocommerce'); ?>
            </div>
            <?php
        }
    }
    // Fallback: No wishlist plugin detected
    else {
        ?>
        <div
            class="woocommerce-message woocommerce-message--info woocommerce-Message woocommerce-Message--info woocommerce-info">
            <?php esc_html_e('Wishlist functionality requires a wishlist plugin. Please install a compatible wishlist plugin.', 'woocommerce'); ?>
        </div>
        <?php
    }
    ?>
</div>

<?php do_action('woocommerce_after_account_wishlist'); ?>