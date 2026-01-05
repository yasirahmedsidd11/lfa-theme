<?php
/**
 * Template Name: Terms of Service
 * 
 * Template for Terms of Service page
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
                        // Dummy content for Terms of Service
                        ?>
                        <div class="lfa-policy-section">
                            <h2>TERMS</h2>
                            <p>
                                By accessing the website at www.livingfitapparel.com, you are agreeing to be bound by these
                                terms of service, all applicable laws and regulations, and agree that you are responsible for
                                compliance with any applicable local laws. If you do not agree with any of these terms, you are
                                prohibited from using or accessing this site. The materials contained in this website are
                                protected by applicable copyright and trademark law.
                            </p>
                        </div>

                        <div class="lfa-policy-section">
                            <h2>USE LICENSE</h2>
                            <p>
                                Permission is granted to temporarily download one copy of the materials (information or
                                software) on Living Fit Apparel for personal, non-commercial transitory viewing only. This is
                                the grant of a license, not a transfer of title, and under this license, you may not:
                            <ul>
                                <li>modify or copy the materials;</li>
                                <li>use the materials for any commercial purpose, or for any public display (commercial or
                                    non-commercial);</li>
                                <li>attempt to decompile or reverse engineer any software contained on Living Fit Apparel’s
                                    website;
                                    remove any copyright or other proprietary notations from the materials; or transfer the
                                    materials to another person or “mirror” the materials on any other server.</li>
                                <li>
                                    This license shall automatically terminate if you violate any of these restrictions and may
                                    be
                                    terminated by Living Fit Apparel at any time. Upon terminating your viewing of these
                                    materials
                                    or upon the termination of this license, you must destroy any downloaded materials in your
                                    possession whether in electronic or printed format.
                                </li>
                            </ul>
                            </p>
                        </div>

                        <div class="lfa-policy-section">
                            <h2>DISCLAIMER</h2>
                            <p>
                                The materials on Living Fit Apparel website are provided on an ‘as is’ basis. Living Fit Apparel
                                makes no warranties, expressed or implied, and hereby disclaims and negates all other warranties
                                including, without limitation, implied warranties or conditions of merchantability, fitness for
                                a particular purpose, or non-infringement of intellectual property or other violation of rights.
                            </p>
                            <p>
                                Further, Living Fit Apparel does not warrant or make any representations concerning the
                                accuracy, likely results, or reliability of the use of the materials on its website or otherwise
                                relating to such materials or on any sites linked to this site.
                            </p>
                        </div>

                        <div class="lfa-policy-section">
                            <h2>LIMITATIONS</h2>
                            <p>
                                In no event shall Living Fit Apparel be liable for any damages (including, without limitation,
                                damages for loss of data or profit, or due to business interruption) arising out of the use or
                                inability to use the materials on Living Fit Apparel website, even if Living Fit Apparel or a
                                Living Fit Apparel authorized representative has been notified orally or in writing of the
                                possibility of such damage. Because some jurisdictions do not allow limitations on implied
                                warranties, or limitations of liability for consequential or incidental damages, these
                                limitations may not apply to you.
                            </p>
                        </div>

                        <div class="lfa-policy-section">
                            <h2>ACCURACY OF MATERIALS</h2>
                            <p>
                                The materials appearing on Living Fit Apparel website could include technical, typographical, or
                                photographic errors. Living Fit Apparel does not warrant that any of the materials on its
                                website are 100% accurate, complete or current. Living Fit Apparel may make changes to the
                                materials contained on its website at any time without notice. However, Living Fit Apparel does
                                not make any commitment to update the materials.
                            </p>
                        </div>

                        <div class="lfa-policy-section">
                            <h2>PRODUCT & SERVICE DESCRIPTIONS</h2>
                            <p>
                                Whilst we try to display the colors of our products accurately on the Website, the actual colors
                                you see will depend on your screen and we cannot guarantee that your screen’s display of any
                                color will accurately reflect the color of the product on delivery.
                            </p>
                            <p>
                                All items are subject to availability. We will inform you as soon as possible if the product(s)
                                or service(s) you have ordered are not available and we may offer an alternative product(s) or
                                service(s) of equal or higher quality and value otherwise the order had to be canceled.
                            </p>
                        </div>

                        <div class="lfa-policy-section">
                            <h2>ACCEPTANCE OF YOUR ORDER</h2>
                            <p>
                                Please note that completion of the online checkout process does not constitute our acceptance of
                                your order. Our acceptance of your order will take place only when we dispatch the product(s) or
                                commencement of the services that you ordered from us.<br />
                                If you supplied us with your email address when entering your payment details (or if you have a
                                registered account with us), we will notify you by email as soon as possible to confirm that we
                                have received your order.<br />
                                All products that you order through the Website will remain the property of Living Fit Apparel
                                until we have received payment in full from you for those products.<br />
                                If we cannot supply you with the product or service you ordered, we will not process your order.
                                We will inform you via email or call, if you have already paid for the product or service,
                                refund you in full as soon as reasonably possible.</p>
                        </div>

                        <div class="lfa-policy-section">
                            <h2>DELIVERY OF YOUR ORDER</h2>
                            <p>
                                Living Fit Apparel products are sold on a delivery duty unpaid basis. The recipient may have to
                                pay import duty or a formal customs entry fee prior to or on delivery. Additional taxes, fees or
                                levies may apply according to local legislation and customers are required to check these
                                details before placing an order for international delivery.<br />
                                You bear the risk for the products once delivery is completed.<br />
                                Where possible, we try to deliver in one go. We reserve the right to split the delivery of your
                                order, for instance (but not limited to) if part of your order is delayed or unavailable. In the
                                event that we split your order, we will notify you of our intention to do so by sending you an
                                e-mail to the e-mail address provided by you at the time of purchase. You will not be charged
                                for any additional delivery costs.<br />
                                We can entertain any changes to order provided if the order isn’t dispatched yet. We will not be
                                able to accept any order change requests once the order is dispatched.
                            </p>
                        </div>

                        <div class="lfa-policy-section">
                            <h2>Prohibited Uses</h2>
                            <p>You may not use our website:</p>
                            <ul>
                                <li>In any way that violates any applicable law or regulation</li>
                                <li>To transmit any malicious code or viruses</li>
                                <li>To collect or track personal information of others</li>
                                <li>To spam, phish, or engage in any fraudulent activity</li>
                                <li>To interfere with or disrupt the website or servers</li>
                            </ul>
                        </div>

                        <div class="lfa-policy-section">
                            <h2>LINKS</h2>
                            <p>
                                We may have placed links on this Website to other websites which we think you may want to visit.
                                We do not vet these websites and do not have any control over their contents. Except where
                                required by applicable law Living Fit Apparel cannot accept any liability in respect of the use
                                of these websites.
                            </p>
                        </div>

                        <div class="lfa-policy-section">
                            <h2>MODIFICATIONS</h2>
                            <p>
                                Living Fit Apparel may revise these terms of service for its website at any time without notice.
                                By using this website you are agreeing to be bound by the then current version of these terms of
                                service.
                            </p>
                        </div>

                        <div class="lfa-policy-section">
                            <h2>
                                GOVERNING LAW
                            </h2>
                            <p>
                                These terms and conditions are governed by and construed in accordance with the laws of Pakistan
                                and you irrevocably submit to the exclusive jurisdiction of the courts in that State or
                                location.
                            </p>
                        </div>

                        <div class="lfa-policy-section">
                            <h2>COMPLAINTS PROCESS</h2>
                            <p>
                                We hope that you’re satisfied with any purchase you’ve made or the service you’ve received from
                                Living Fit Apparel and that you’ll never have reason to complain – but if there’s something
                                you’re not happy with, we’d like to put matters right, so please contact us straight away:
                            </p>
                            <p><strong>BY EMAIL</strong> <br/> <a href="mailto:contact@livingfitapparel.com">contact@livingfitapparel.com</a></p>
                            <p><strong>BY PHONE</strong> <br/> <a href="tel:+923020532532">+923020532532</a></p>
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

