<?php
/**
 * Template Name: Shipping Policy
 * 
 * Template for Shipping Policy page
 */

defined('ABSPATH') || exit;

get_header();
?>

<main class="lfa-policy-page">
    <div class="container">
        <div class="lfa-policy-content">
            <?php
            while (have_posts()) {
                the_post();
                ?>
                <header class="lfa-policy-header">
                    <h1 class="lfa-policy-title"><?php the_title(); ?></h1>
                </header>

                <div class="lfa-policy-body">
                    <?php
                    // Display page content if it exists
                    if (get_the_content()) {
                        the_content();
                    } else {
                        // Dummy content for Shipping Policy
                        ?>
                        <div class="lfa-policy-section">
                            <p>
                                All orders are passed on to Leopards Courier Service; the estimated delivery time is 2-3 working
                                days after an order is placed (subject to weather changes in the city).
                            </p>
                        </div>


                        <?php
                    }
                    ?>
                </div>
                <?php
            }
            ?>
        </div>
    </div>
</main>

<?php
get_footer();

