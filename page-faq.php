<?php
/**
 * Template Name: FAQ
 * 
 * Template for FAQ page with native WordPress meta box support
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
                        ?>
                        <div class="lfa-policy-section">
                            <?php the_content(); ?>
                        </div>
                        <?php
                    }

                    // Get FAQs from post meta
                    $faqs = get_post_meta(get_the_ID(), '_lfa_faqs', true);
                    
                    if (!empty($faqs) && is_array($faqs)) {
                        ?>
                        <div class="lfa-faq-container">
                            <?php
                            foreach ($faqs as $faq) {
                                $faq_title = isset($faq['title']) ? $faq['title'] : '';
                                $faq_description = isset($faq['description']) ? $faq['description'] : '';
                                
                                if (!empty($faq_title)) {
                                    ?>
                                    <div class="lfa-faq-item">
                                        <button class="lfa-faq-question" type="button" aria-expanded="false">
                                            <span class="lfa-faq-question-text"><?php echo esc_html($faq_title); ?></span>
                                            <span class="lfa-faq-icon" aria-hidden="true">+</span>
                                        </button>
                                        <div class="lfa-faq-answer">
                                            <div class="lfa-faq-answer-content">
                                                <?php echo wp_kses_post($faq_description); ?>
                                            </div>
                                        </div>
                                    </div>
                                    <?php
                                }
                            }
                            ?>
                        </div>
                        <?php
                    } else {
                        // Message if no FAQs
                        ?>
                        <div class="lfa-policy-section">
                            <p>No FAQs have been added yet. Please add FAQs using the FAQs meta box in the page editor.</p>
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

<script>
(function() {
    document.addEventListener('DOMContentLoaded', function() {
        const faqItems = document.querySelectorAll('.lfa-faq-item');
        
        faqItems.forEach(function(item) {
            const question = item.querySelector('.lfa-faq-question');
            const answer = item.querySelector('.lfa-faq-answer');
            const icon = item.querySelector('.lfa-faq-icon');
            
            if (!question || !answer) return;
            
            question.addEventListener('click', function() {
                const isExpanded = this.getAttribute('aria-expanded') === 'true';
                
                // Close all other FAQs
                faqItems.forEach(function(otherItem) {
                    if (otherItem !== item) {
                        const otherQuestion = otherItem.querySelector('.lfa-faq-question');
                        const otherAnswer = otherItem.querySelector('.lfa-faq-answer');
                        const otherIcon = otherItem.querySelector('.lfa-faq-icon');
                        
                        if (otherQuestion && otherAnswer) {
                            otherQuestion.setAttribute('aria-expanded', 'false');
                            otherAnswer.style.maxHeight = null;
                            if (otherIcon) {
                                otherIcon.textContent = '+';
                            }
                        }
                    }
                });
                
                // Toggle current FAQ
                if (isExpanded) {
                    this.setAttribute('aria-expanded', 'false');
                    answer.style.maxHeight = null;
                    if (icon) {
                        icon.textContent = '+';
                    }
                } else {
                    this.setAttribute('aria-expanded', 'true');
                    answer.style.maxHeight = answer.scrollHeight + 'px';
                    if (icon) {
                        icon.textContent = '−';
                    }
                }
            });
        });
    });
})();
</script>

<?php
get_footer();

