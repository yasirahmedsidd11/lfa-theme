<?php
/**
 * Template Name: Privacy Policy
 * 
 * Template for Privacy Policy page
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
                        // Dummy content for Privacy Policy
                        ?>
                        <div class="lfa-policy-section">
                            <p>This Privacy Policy describes how your personal information is collected, used, and shared when
                                you visit or make a purchase from www.livingfitapparel.com (the “Site”).</p>
                        </div>

                        <div class="lfa-policy-section">
                            <h2>Personal information we collect</h2>
                            <p>When you visit the Site, we automatically collect certain information about your device,
                                including information about your web browser, IP address, time zone, and some of the cookies
                                that are installed on your device. Additionally, as you browse the Site, we collect information
                                about the individual web pages or products that you view, what websites or search terms referred
                                you to the Site and information about how you interact with the Site. We refer to this
                                automatically-collected information as “Device Information”.</p>

                            <p>We collect Device Information using the following technologies:<br />
                                – “Cookies” are data files that are placed on your device or computer and often include an
                                anonymous unique identifier. For more information about cookies, and how to disable cookies,
                                visit http://www.allaboutcookies.org.<br />
                                – “Log files” track actions occurring on the Site, and
                                collect data including your IP address, browser type, Internet service provider, referring/exit
                                pages, and date/time stamps.<br />
                                – “Web beacons”, “tags”, and “pixels” are electronic files used to record information about how
                                you browse the Site.</p>
                            <p>
                                Additionally, when you make a purchase or attempt to make a purchase through the Site, we
                                collect certain information from you, including your name, billing address, shipping address,
                                payment information (including credit card numbers, email address, and phone number. We refer to
                                this information as “Order Information”.<br />
                                When we talk about “Personal Information” in this Privacy Policy, we are talking both about
                                Device Information and Order Information.
                            </p>
                        </div>

                        <div class="lfa-policy-section">
                            <h2>How do we use your personal information?</h2>
                            <p>
                                We use the Order Information that we collect generally to fulfill any orders placed through the
                                Site (including processing your payment information, arranging for shipping, and providing you
                                with invoices and/or order confirmations). Additionally, we use this Order Information to:<br />
                                – Communicate with you;<br />
                                – Screen our orders for potential risk or fraud; and<br />
                                – When in line with the preferences you have shared with us, provide you with information or
                                advertising relating to
                                our products or services.<br />
                                We use the Device Information that we collect to help us screen for potential risk and fraud (in
                                particular, your IP address), and more generally to improve and optimize our Site (for example,
                                by generating analytics about how our customers browse and interact with the Site, and to assess
                                the success of our marketing and advertising campaigns).
                            </p>
                        </div>

                        <div class="lfa-policy-section">
                            <h2>Sharing you personal Information</h2>
                            <p>
                                We share your Personal Information with third parties to help us use your Personal Information,
                                as described above. We also use Google Analytics to help us understand how our customers use the
                                Site — you can read more about how Google uses your Personal Information here:
                                <a href="https://www.google.com/intl/en/policies/privacy/"
                                    target="_blank">https://www.google.com/intl/en/policies/privacy/</a>. You can also opt-out
                                of Google Analytics here:
                                <a href="https://tools.google.com/dlpage/gaoptout"
                                    target="_blank">https://tools.google.com/dlpage/gaoptout</a>.<br />
                                Finally, we may also share your Personal Information to comply with applicable laws and
                                regulations, to respond to a subpoena, search warrant, or other lawful requests for information
                                we receive, or to otherwise protect our rights.
                            </p>
                        </div>

                        <div class="lfa-policy-section">
                            <h2>Behavioral advertising</h2>
                            <p>
                                As described above, we use your Personal Information to provide you with targeted advertisements
                                or marketing communications we believe may be of interest to you. For more information about how
                                targeted advertising works, you can visit the Network Advertising Initiative’s (“NAI”)
                                educational page at
                                <a href="http://www.networkadvertising.org/understanding-online-advertising/how-does-it-work"
                                    target="_blank">http://www.networkadvertising.org/understanding-online-advertising/how-does-it-work</a>.<br />
                                You can opt-out of targeted advertising by using the links below:<br />
                                – Facebook: <a href="https://www.facebook.com/settings/?tab=ads"
                                    target="_blank">https://www.facebook.com/settings/?tab=ads</a><br />
                                – Google: <a href="https://www.google.com/settings/ads/anonymous"
                                    target="_blank">https://www.google.com/settings/ads/anonymous</a><br />
                                – Bing: <a
                                    href="https://advertise.bingads.microsoft.com/en-us/resources/policies/personalized-ads"
                                    target="_blank">https://advertise.bingads.microsoft.com/en-us/resources/policies/personalized-ads</a><br />
                                Additionally, you can opt-out of some of these services by visiting the Digital Advertising
                                Alliance’s opt-out portal at <a href="http://optout.aboutads.info/"
                                    target="_blank">http://optout.aboutads.info/</a>.
                            </p>
                        </div>

                        <div class="lfa-policy-section">
                            <h2>Do not track</h2>
                            <p>Please note that we do not alter our Site’s data collection and use practices when we see a Do
                                Not Track signal from your browser.</p>
                        </div>

                        <div class="lfa-policy-section">
                            <h2>Data retention</h2>
                            <p>When you place an order through the Site, we will maintain your Order Information for our records
                                unless and until you ask us to delete this information. </p>
                        </div>

                        <div class="lfa-policy-section">
                            <h2>Changes</h2>
                            <p>We may update this privacy policy from time to time in order to reflect, for example, changes to
                                our practices or for other operational, legal, or regulatory reasons.</p>
                        </div>

                        <div class="lfa-policy-section">
                            <h2>Contact Us</h2>
                            <p>For more information about our privacy practices, if you have questions, or if you would like to
                                make a complaint, please contact us by e‑mail at <a href="mailto:livingfit.pk@gmail.com">livingfit.pk@gmail.com</a> or by mail using the
                                details provided below:</p>

                        </div>

                        <div class="lfa-policy-section">
                            <h2>Head Office</h2>
                            <p>
                                <strong style="font-weight: 400;">Living Fit Apparel</strong><br>
                                C69/71, 12th Commercial Lane, DHA Phase 2 EXT, Karachi.<br>
                                Tel: (92-21) 3539 6663
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

