<?php
/**
 * Template Name: Find Us
 *
 * @package LivingFitApparel
 */

defined('ABSPATH') || exit;

get_header();
?>

<main class="lfa-find-us-page">
    <div class="container">
        <div class="lfa-find-us-wrapper">
            <?php echo do_shortcode('[ASL_STORELOCATOR]'); ?>
        </div>
    </div>
</main>

<?php
get_footer();

