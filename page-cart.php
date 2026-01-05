<?php
/**
 * Template Name: Cart
 *
 * @package LivingFitApparel
 */

defined('ABSPATH') || exit;

get_header();
?>

<main class="lfa-cart-page">
    <div class="container">
        <div class="lfa-cart-wrapper">
           <div class="container">
           <div class="lfa-cart-content">
                <?php
                // Display WooCommerce cart
                if (class_exists('WooCommerce')) {
                    echo do_shortcode('[woocommerce_cart]');
                } else {
                    ?>
                    <div class="lfa-cart-error">
                        <p><?php _e('WooCommerce is required for the shopping cart.', 'livingfitapparel'); ?></p>
                    </div>
                    <?php
                }
                ?>
            </div>
           </div>
        </div>
    </div>
</main>

<?php
get_footer();

