<?php
/**
 * Quick View AJAX Handler
 *
 * @package livingfitapparel
 */

if (!defined('ABSPATH')) exit;

/**
 * AJAX handler for quick view
 */
add_action('wp_ajax_lfa_get_quick_view', 'lfa_get_quick_view_handler');
add_action('wp_ajax_nopriv_lfa_get_quick_view', 'lfa_get_quick_view_handler');

function lfa_get_quick_view_handler() {
    // Verify nonce
    check_ajax_referer('lfa-nonce', 'nonce');
    
    $product_id = isset($_POST['product_id']) ? intval($_POST['product_id']) : 0;
    
    if (!$product_id) {
        wp_send_json_error(array('message' => 'Invalid product ID'));
        return;
    }
    
    $product = wc_get_product($product_id);
    
    if (!$product) {
        wp_send_json_error(array('message' => 'Product not found'));
        return;
    }
    
    // Start output buffering
    ob_start();
    
    // Set global product for template
    global $product;
    $product = wc_get_product($product_id);
    
    // Load the quick view content template
    $template_path = get_template_directory() . '/woocommerce/quick-view-content.php';
    
    if (file_exists($template_path)) {
        include $template_path;
    } else {
        echo '<div class="lfa-quick-view-error">Template file not found: ' . esc_html($template_path) . '</div>';
    }
    
    $html = ob_get_clean();
    
    wp_send_json_success(array('data' => $html));
}

/**
 * AJAX handler for finding variation by attributes
 */
add_action('wp_ajax_lfa_find_variation', 'lfa_find_variation_handler');
add_action('wp_ajax_nopriv_lfa_find_variation', 'lfa_find_variation_handler');

function lfa_find_variation_handler() {
    check_ajax_referer('lfa-nonce', 'nonce');
    
    $product_id = isset($_POST['product_id']) ? intval($_POST['product_id']) : 0;
    $attributes = isset($_POST['attributes']) ? $_POST['attributes'] : array();
    
    if (!$product_id) {
        wp_send_json_error(array('message' => 'Invalid product ID'));
        return;
    }
    
    $product = wc_get_product($product_id);
    
    if (!$product || !$product->is_type('variable')) {
        wp_send_json_error(array('message' => 'Product not found or not variable'));
        return;
    }
    
    // Find matching variation
    $data_store = WC_Data_Store::load('product');
    $variation_id = $data_store->find_matching_product_variation($product, $attributes);
    
    if ($variation_id) {
        $variation = wc_get_product($variation_id);
        if ($variation && $variation->is_purchasable()) {
            wp_send_json_success(array(
                'variation_id' => $variation_id,
                'price_html' => $variation->get_price_html(),
                'stock_status' => $variation->get_stock_status(),
                'is_in_stock' => $variation->is_in_stock(),
                'stock_quantity' => $variation->get_stock_quantity()
            ));
        } else {
            wp_send_json_error(array('message' => 'Variation not purchasable'));
        }
    } else {
        wp_send_json_error(array('message' => 'Variation not found'));
    }
}

/**
 * AJAX handler for adding product to cart
 */
add_action('wp_ajax_lfa_add_to_cart', 'lfa_add_to_cart_handler');
add_action('wp_ajax_nopriv_lfa_add_to_cart', 'lfa_add_to_cart_handler');

function lfa_add_to_cart_handler() {
    check_ajax_referer('lfa-nonce', 'nonce');
    
    $product_id = isset($_POST['product_id']) ? intval($_POST['product_id']) : 0;
    $variation_id = isset($_POST['variation_id']) ? intval($_POST['variation_id']) : 0;
    $quantity = isset($_POST['quantity']) ? intval($_POST['quantity']) : 1;
    
    if (!$product_id) {
        wp_send_json_error(array('message' => 'Invalid product ID'));
        return;
    }
    
    $product = wc_get_product($product_id);
    
    if (!$product) {
        wp_send_json_error(array('message' => 'Product not found'));
        return;
    }
    
    // For variable products, use variation ID
    if ($product->is_type('variable') && $variation_id > 0) {
        $variation = wc_get_product($variation_id);
        if (!$variation) {
            wp_send_json_error(array('message' => 'Variation not found'));
            return;
        }
        
        $cart_item_key = WC()->cart->add_to_cart($product_id, $quantity, $variation_id);
    } else {
        // Simple product
        $cart_item_key = WC()->cart->add_to_cart($product_id, $quantity);
    }
    
    if ($cart_item_key) {
        // Return cart fragments for update
        wp_send_json_success(array(
            'message' => __('Product added to cart', 'livingfitapparel'),
            'cart_hash' => WC()->cart->get_cart_hash(),
            'fragments' => apply_filters('woocommerce_add_to_cart_fragments', array())
        ));
    } else {
        $notices = wc_get_notices('error');
        $message = !empty($notices) ? $notices[0]['notice'] : __('Failed to add product to cart', 'livingfitapparel');
        wp_send_json_error(array('message' => $message));
    }
}

