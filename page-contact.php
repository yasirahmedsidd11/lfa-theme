<?php
/**
 * Template Name: Contact
 *
 * @package LivingFitApparel
 */

defined('ABSPATH') || exit;

get_header();

// Get selected Contact Form 7 form ID
$form_id = get_post_meta(get_the_ID(), '_lfa_contact_form_id', true);
?>

<main class="lfa-contact-page">
    <div class="container">
        <?php
        while (have_posts()) {
            the_post();
            ?>
            <header class="lfa-contact-header">
                <h1 class="lfa-contact-title"><?php _e('CONTACT US', 'livingfitapparel'); ?></h1>
                <?php if (get_the_excerpt() || get_the_content()): ?>
                    <p class="lfa-contact-description">
                        <?php 
                        if (get_the_excerpt()) {
                            echo esc_html(get_the_excerpt());
                        } else {
                            echo esc_html(get_the_content());
                        }
                        ?>
                    </p>
                <?php else: ?>
                    <p class="lfa-contact-description"><?php _e('Learn more about your product or order', 'livingfitapparel'); ?></p>
                <?php endif; ?>
            </header>

            <div class="lfa-contact-form-wrapper">
                <?php
                if (!empty($form_id) && class_exists('WPCF7_ContactForm')) {
                    // Display the selected Contact Form 7 form
                    echo do_shortcode('[contact-form-7 id="' . esc_attr($form_id) . '"]');
                } else {
                    // Fallback message if no form is selected
                    ?>
                    <div class="lfa-contact-no-form">
                        <p><?php _e('No contact form has been selected. Please select a Contact Form 7 form in the page editor.', 'livingfitapparel'); ?></p>
                    </div>
                    <?php
                }
                ?>
            </div>
            <?php
        }
        ?>
    </div>
</main>

<?php
get_footer();

