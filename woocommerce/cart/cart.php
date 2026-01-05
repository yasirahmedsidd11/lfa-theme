<?php
/**
 * Cart Page
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/cart/cart.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 10.1.0
 */

defined('ABSPATH') || exit;

do_action('woocommerce_before_cart');

$cart_count = WC()->cart->get_cart_contents_count();
?>

<a href="<?php echo esc_url(apply_filters('woocommerce_return_to_shop_redirect', wc_get_page_permalink('shop'))); ?>"
    class="lfa-continue-shopping">
    <svg width="17" height="8" viewBox="0 0 17 8" fill="none" xmlns="http://www.w3.org/2000/svg">
        <path
            d="M0.146446 3.32809C-0.0488148 3.52335 -0.0488148 3.83993 0.146446 4.0352L3.32843 7.21718C3.52369 7.41244 3.84027 7.41244 4.03553 7.21718C4.2308 7.02191 4.2308 6.70533 4.03553 6.51007L1.20711 3.68164L4.03553 0.853215C4.2308 0.657952 4.2308 0.34137 4.03553 0.146108C3.84027 -0.0491544 3.52369 -0.0491543 3.32843 0.146108L0.146446 3.32809ZM16.5 3.68164L16.5 3.18164L0.5 3.18164L0.5 3.68164L0.5 4.18164L16.5 4.18164L16.5 3.68164Z"
            fill="black" />
    </svg>


    <?php esc_html_e('CONTINUE SHOPPING', 'woocommerce'); ?>
</a>

<div class="lfa-cart-layout">
    <div class="lfa-cart-left">
        <div class="lfa-cart-box">
            <div class="lfa-cart-box-header">
                <h2 class="lfa-cart-title"><?php printf(esc_html__('CART (%d)', 'woocommerce'), $cart_count); ?>
                </h2>
                <a href="<?php echo esc_url(wc_get_cart_url()); ?>?empty-cart=1" class="lfa-cart-clear"
                    aria-label="<?php esc_attr_e('Clear cart', 'woocommerce'); ?>">×</a>
            </div>

            <form class="woocommerce-cart-form" action="<?php echo esc_url(wc_get_cart_url()); ?>" method="post">
                <?php do_action('woocommerce_before_cart_table'); ?>

                <div class="lfa-cart-items">
                    <?php do_action('woocommerce_before_cart_contents'); ?>

                    <?php
                    foreach (WC()->cart->get_cart() as $cart_item_key => $cart_item) {
                        $_product = apply_filters('woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key);
                        $product_id = apply_filters('woocommerce_cart_item_product_id', $cart_item['product_id'], $cart_item, $cart_item_key);
                        $product_name = apply_filters('woocommerce_cart_item_name', $_product->get_name(), $cart_item, $cart_item_key);

                        if ($_product && $_product->exists() && $cart_item['quantity'] > 0 && apply_filters('woocommerce_cart_item_visible', true, $cart_item, $cart_item_key)) {
                            $product_permalink = apply_filters('woocommerce_cart_item_permalink', $_product->is_visible() ? $_product->get_permalink($cart_item) : '', $cart_item, $cart_item_key);
                            ?>
                            <div
                                class="lfa-cart-item <?php echo esc_attr(apply_filters('woocommerce_cart_item_class', 'cart_item', $cart_item, $cart_item_key)); ?>">
                                <div class="lfa-cart-item-col-1">
                                    <div class="lfa-cart-item-image">
                                        <?php
                                        $thumbnail = apply_filters('woocommerce_cart_item_thumbnail', $_product->get_image(), $cart_item, $cart_item_key);
                                        if (!$product_permalink) {
                                            echo $thumbnail; // PHPCS: XSS ok.
                                        } else {
                                            printf('<a href="%s">%s</a>', esc_url($product_permalink), $thumbnail); // PHPCS: XSS ok.
                                        }
                                        ?>
                                    </div>
                                </div>
                                <div class="lfa-cart-item-col-2">
                                    <h3 class="lfa-cart-item-name">
                                        <?php
                                        // Get product name without attributes - use parent product name for variations
                                        if ($_product->is_type('variation')) {
                                            $parent_id = $_product->get_parent_id();
                                            if ($parent_id) {
                                                $parent_product = wc_get_product($parent_id);
                                                $display_name = $parent_product ? $parent_product->get_name() : $_product->get_name();
                                            } else {
                                                $display_name = $_product->get_name();
                                            }
                                        } else {
                                            $display_name = $_product->get_name();
                                        }
                                        // Strip any HTML that might contain attributes
                                        $display_name = wp_strip_all_tags($display_name);
                                        if (!$product_permalink) {
                                            echo esc_html($display_name);
                                        } else {
                                            printf('<a href="%s">%s</a>', esc_url($product_permalink), esc_html($display_name));
                                        }
                                        ?>
                                    </h3>
                                    <?php
                                    // Extract attribute values and format them with "/"
                                    $attributes = array();
                                    if (!empty($cart_item['variation'])) {
                                        foreach ($cart_item['variation'] as $key => $value) {
                                            if (!empty($value)) {
                                                $taxonomy = str_replace('attribute_', '', $key);
                                                $term = get_term_by('slug', $value, $taxonomy);
                                                if ($term) {
                                                    $attributes[] = $term->name;
                                                } else {
                                                    $attributes[] = $value;
                                                }
                                            }
                                        }
                                    }
                                    if (!empty($attributes)) {
                                        ?>
                                        <div class="lfa-cart-item-attributes">
                                            <span
                                                class="lfa-cart-item-attr"><?php echo esc_html(implode(' / ', $attributes)); ?></span>
                                        </div>
                                        <?php
                                    }
                                    ?>
                                    <div class="lfa-cart-item-quantity">
                                        <?php
                                        if ($_product->is_sold_individually()) {
                                            $min_quantity = 1;
                                            $max_quantity = 1;
                                        } else {
                                            $min_quantity = 0;
                                            $max_quantity = $_product->get_max_purchase_quantity();
                                        }
                                        ?>
                                        <div class="lfa-quantity-control">
                                            <button type="button" class="lfa-quantity-minus"
                                                data-cart-item-key="<?php echo esc_attr($cart_item_key); ?>"
                                                data-min="<?php echo esc_attr($min_quantity); ?>"
                                                aria-label="<?php esc_attr_e('Decrease quantity', 'woocommerce'); ?>">−</button>
                                            <input type="number" class="lfa-quantity-input"
                                                data-cart-item-key="<?php echo esc_attr($cart_item_key); ?>"
                                                name="cart[<?php echo esc_attr($cart_item_key); ?>][qty]"
                                                value="<?php echo esc_attr($cart_item['quantity']); ?>"
                                                min="<?php echo esc_attr($min_quantity); ?>"
                                                max="<?php echo esc_attr($max_quantity); ?>" readonly
                                                aria-label="<?php esc_attr_e('Quantity', 'woocommerce'); ?>">
                                            <button type="button" class="lfa-quantity-plus"
                                                data-cart-item-key="<?php echo esc_attr($cart_item_key); ?>"
                                                data-max="<?php echo esc_attr($max_quantity); ?>"
                                                aria-label="<?php esc_attr_e('Increase quantity', 'woocommerce'); ?>">+</button>
                                        </div>
                                    </div>
                                </div>
                                <div class="lfa-cart-item-col-3">
                                    <div class="lfa-cart-item-price">
                                        <?php
                                        echo apply_filters('woocommerce_cart_item_price', WC()->cart->get_product_price($_product), $cart_item, $cart_item_key); // PHPCS: XSS ok.
                                        ?>
                                    </div>
                                    <div class="lfa-cart-item-remove">
                                        <?php
                                        echo apply_filters( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                                            'woocommerce_cart_item_remove_link',
                                            sprintf(
                                                '<a href="%s" class="lfa-remove-button remove" aria-label="%s" data-product_id="%s" data-product_sku="%s">%s</a>',
                                                esc_url(wc_get_cart_remove_url($cart_item_key)),
                                                /* translators: %s is the product name */
                                                esc_attr(sprintf(__('Remove %s from cart', 'woocommerce'), wp_strip_all_tags($product_name))),
                                                esc_attr($product_id),
                                                esc_attr($_product->get_sku()),
                                                esc_html__('Remove', 'woocommerce')
                                            ),
                                            $cart_item_key
                                        );
                                        ?>
                                    </div>
                                </div>
                            </div>
                            <?php
                        }
                    }
                    ?>

                    <?php do_action('woocommerce_cart_contents'); ?>

                    <?php if (wc_coupons_enabled()) { ?>
                        <div class="lfa-cart-coupon">
                            <label for="coupon_code"
                                class="screen-reader-text"><?php esc_html_e('Coupon:', 'woocommerce'); ?></label>
                            <input type="text" name="coupon_code" class="input-text" id="coupon_code" value=""
                                placeholder="<?php esc_attr_e('Coupon code', 'woocommerce'); ?>" />
                            <button type="submit"
                                class="button<?php echo esc_attr(wc_wp_theme_get_element_class_name('button') ? ' ' . wc_wp_theme_get_element_class_name('button') : ''); ?>"
                                name="apply_coupon"
                                value="<?php esc_attr_e('Apply coupon', 'woocommerce'); ?>"><?php esc_html_e('Apply coupon', 'woocommerce'); ?></button>
                            <?php do_action('woocommerce_cart_coupon'); ?>
                        </div>
                    <?php } ?>

                    <?php do_action('woocommerce_cart_actions'); ?>

                    <?php wp_nonce_field('woocommerce-cart', 'woocommerce-cart-nonce'); ?>

                    <?php do_action('woocommerce_after_cart_contents'); ?>
                </div>

                <?php do_action('woocommerce_after_cart_table'); ?>
            </form>
        </div>
    </div>

    <div class="lfa-cart-right">
        <?php do_action('woocommerce_before_cart_collaterals'); ?>

        <?php
        /**
         * Cart collaterals hook.
         *
         * @hooked woocommerce_cross_sell_display
         * @hooked woocommerce_cart_totals - 10
         */
        do_action('woocommerce_cart_collaterals');
        ?>
    </div>
</div>

<?php do_action('woocommerce_after_cart'); ?>