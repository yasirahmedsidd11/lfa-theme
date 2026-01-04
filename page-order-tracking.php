<?php
/**
 * Template Name: Order Tracking
 *
 * @package LivingFitApparel
 */

defined('ABSPATH') || exit;

get_header();

// Change WooCommerce order tracking button text from "Track" to "ORDER TRACK"
add_filter('gettext', function($translated_text, $text, $domain) {
    // Only change text on order tracking page and if it's the "Track" button
    if (is_page_template('page-order-tracking.php') && $text === 'Track' && $domain === 'woocommerce') {
        return 'ORDER TRACK';
    }
    return $translated_text;
}, 20, 3);
?>

<main class="lfa-order-tracking-page">
    <div class="container">
        <div class="lfa-order-tracking-wrapper">
            <header class="lfa-order-tracking-header">
                <h1 class="lfa-order-tracking-title">
                    <span class="lfa-tab-indicator">•</span>
                    <?php _e('TRACK YOUR ORDER', 'livingfitapparel'); ?>
                </h1>
            </header>

            <div class="lfa-order-tracking-content">
                <?php
                // Display WooCommerce order tracking shortcode
                if (class_exists('WooCommerce')) {
                    echo do_shortcode('[woocommerce_order_tracking]');
                } else {
                    ?>
                    <div class="lfa-order-tracking-error">
                        <p><?php _e('WooCommerce is required for order tracking.', 'livingfitapparel'); ?></p>
                    </div>
                    <?php
                }
                ?>
            </div>
        </div>
    </div>
</main>

<?php
get_footer();

