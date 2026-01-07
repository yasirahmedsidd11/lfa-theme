<?php
/**
 * Template Name: Checkout
 *
 * @package LivingFitApparel
 */

defined('ABSPATH') || exit;

get_header();
?>

<main class="lfa-checkout-page">
    <div class="container">
        <div class="lfa-checkout-page-wrapper">
            <?php
            // Display WooCommerce checkout
            if (class_exists('WooCommerce')) {
                echo do_shortcode('[woocommerce_checkout]');
            } else {
                ?>
                <div class="lfa-checkout-error">
                    <p><?php _e('WooCommerce is required for checkout.', 'livingfitapparel'); ?></p>
                </div>
                <?php
            }
            ?>
        </div>
    </div>
</main>

<?php
get_footer();

