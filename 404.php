<?php
/**
 * The template for displaying 404 pages (not found)
 *
 * @package LivingFitApparel
 */

defined('ABSPATH') || exit;

get_header();

// Get 404 page settings from options
$page_title = lfa_get('404.title', '404');
$description = lfa_get('404.description', 'AN UNEXPECTED ERROR OCCURED - DISCOVER OUR PRODUCTS OR - CONTACT US IF YOU NEED ASSISTANCE');
?>

<main class="lfa-404-page">
    <div class="container">
        <div class="lfa-404-content">
            <header class="lfa-404-header">
                <h1 class="lfa-404-title"><?php echo esc_html($page_title); ?></h1>
                <?php if (!empty($description)): ?>
                    <div class="lfa-404-description"><?php echo wp_kses_post($description); ?></div>
                <?php endif; ?>
            </header>
        </div>
    </div>

    <?php if (class_exists('WooCommerce')): ?>
        <section class="lfa-featured container" id="lfa-404-featured-slider">
            <?php echo do_shortcode('[products limit="8" columns="4" visibility="featured"]'); ?>
        </section>
    <?php endif; ?>
</main>

<?php
get_footer();
