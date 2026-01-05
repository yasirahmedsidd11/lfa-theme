<?php
/**
 * Template Name: Return & Exchange Policy
 * 
 * Template for Return & Exchange Policy page
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
                        // Dummy content for Return & Exchange Policy
                        ?>
                        <div class="lfa-policy-section">
                            <h2>Exchange Policy</h2>
                            <p>Our policy lasts 7 days. If 7 days have gone by since your purchase, unfortunately, we can’t
                                offer you an exchange. To be eligible for an exchange, your item must be unused and in the same
                                condition that you received it. It must also be in the original packaging. Several types of
                                goods are exempt from being returned.</p>
                        </div>

                        <div class="lfa-policy-section">
                            <h2>Non-returnable items:</h2>
                            <P>
                                – Sports Bras<br />
                                – Accessories<br />
                                To process your exchange, we require proof of purchase or receipt.<br />
                                There are certain situations where only partial refunds are granted (if applicable):<br />
                                – Any item not in its original condition, is damaged or missing parts for reasons not due to our
                                error

                            </P>
                        </div>

                        <div class="lfa-policy-section">
                            <h2>Refund Policy</h2>
                            <p>
                                You are only eligible for a refund if have received a damaged or defected product.<br />
                                Note: Only outfit cost will be refunded
                            </p>
                        </div>

                        <div class="lfa-policy-section">
                            <h2>Sale items</h2>
                            <p>
                                Only regular priced items may be exchanged, unfortunately, sale items cannot be exchanged.
                            </p>
                        </div>

                        <div class="lfa-policy-section">
                            <h2>Exchange Process</h2>
                            <p style="margin-bottom: 0;">
                                We only replace items if they are defective or damaged.<br />
                                Once your exchangeable item is received and inspected, we will send you an email to notify you
                                that
                                we have received your returned item. We will also notify you of the approval or rejection of
                                your
                                exchange.<br />
                                If you are approved, then your exchange will be processed, and a request for exchange will be
                                sent
                                out within a certain amount of days.<br />
                                We offer one time exchange only.<br />
                                Steps:
                            <ol style="margin: 0; padding-inline-start: 16px;">
                                <li>Send us an email at <a
                                        href="mailto:contact@livingfitapparel.com">contact@livingfitapparel.com</a></li>
                                <li>Send your item to the given address.</li>
                                <li>You will be responsible for paying for your own shipping costs for returning your item.
                                    Shipping costs are non-refundable.</li>
                            </ol>
                            </p>
                        </div>
                        <div class="lfa-policy-section">
                            <h2>Time period for exchange/return policy:</h2>
                            <p style="margin-bottom: 0;">
                            <ol style="margin: 0; padding-inline-start: 16px;">
                                <li>We provide exchange and refund within 7 days.</li>
                                <li>Return/Exchange will only be possible after receiving the previous items from customers.
                                </li>
                            </ol>
                            </p>
                        </div>

                        <div class="lfa-policy-section">
                            <h2>Shipping</h2>
                            <p>
                                To return your product, you should mail your product to the given address.<br />
                                Depending on where you live, the time it may take for your exchanged product to reach you may
                                vary.<br />
                                If you are shipping an item over Rs 7,000, you should consider using a trackable shipping
                                service or purchasing shipping insurance. We don’t guarantee that we will receive your returned
                                item.
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

