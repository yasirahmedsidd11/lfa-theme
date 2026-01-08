<?php
/**
 * Template Name: My Account
 *
 * @package LivingFitApparel
 */

defined('ABSPATH') || exit;

get_header();

// Check if user is logged in
$is_logged_in = is_user_logged_in();
?>

<main class="lfa-my-account-page">
    <div class="container">
        <div class="lfa-my-account-wrapper">
            <?php if ($is_logged_in): ?>
                <!-- Logged In: Show My Account -->
                <div class="lfa-my-account-content">
                    <?php
                    // Use WooCommerce my account shortcode if available
                    if (class_exists('WooCommerce')) {
                        echo do_shortcode('[woocommerce_my_account]');
                    } else {
                        // Fallback for non-WooCommerce
                        $current_user = wp_get_current_user();
                        echo '<div class="lfa-account-info">';
                        echo '<p><strong>' . __('Email:', 'livingfitapparel') . '</strong> ' . esc_html($current_user->user_email) . '</p>';
                        echo '<p><a href="' . wp_logout_url(home_url()) . '" class="lfa-logout-btn">' . __('Logout', 'livingfitapparel') . '</a></p>';
                        echo '</div>';
                    }
                    ?>
                </div>
            <?php else: ?>
                <!-- Logged Out: Show Login/Signup/Forgot Password -->
                <div class="lfa-auth-container">
                    <div class="lfa-auth-tabs">
                        <button class="lfa-auth-tab active" data-tab="login">
                            <span class="lfa-tab-indicator">•</span>
                            <?php _e('LOGIN', 'livingfitapparel'); ?>
                        </button>
                        <button class="lfa-auth-tab" data-tab="signup">
                            <span class="lfa-tab-indicator">•</span>
                            <?php _e('SIGN UP', 'livingfitapparel'); ?>
                        </button>
                    </div>
                    
                    <div class="lfa-forgot-password-heading" style="display: none;">
                        <h2 class="lfa-forgot-password-title">
                            <span class="lfa-tab-indicator">•</span>
                            <?php _e('RESET YOUR PASSWORD', 'livingfitapparel'); ?>
                        </h2>
                    </div>

                    <!-- Login Form -->
                    <div class="lfa-auth-form active" id="lfa-login-form" data-form="login">
                        <form id="lfa-login-form-element" class="lfa-auth-form-element">
                            <div class="lfa-form-group">
                                <input type="email" id="login-email" name="email" placeholder="<?php esc_attr_e('Email Address', 'livingfitapparel'); ?>" required autocomplete="email">
                                <span class="lfa-error-message" id="login-email-error"></span>
                            </div>

                            <div class="lfa-form-group">
                                <div class="lfa-password-wrapper">
                                    <input type="password" id="login-password" name="password" placeholder="<?php esc_attr_e('Password', 'livingfitapparel'); ?>" required autocomplete="current-password">
                                    <button type="button" class="lfa-password-toggle" aria-label="<?php esc_attr_e('Toggle password visibility', 'livingfitapparel'); ?>" tabindex="-1">
                                        <svg class="lfa-eye-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                                            <circle cx="12" cy="12" r="3"/>
                                        </svg>
                                        <svg class="lfa-eye-off-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="display: none;">
                                            <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/>
                                            <line x1="1" y1="1" x2="23" y2="23"/>
                                        </svg>
                                    </button>
                                </div>
                                <span class="lfa-error-message" id="login-password-error"></span>
                            </div>

                            <div class="lfa-form-options">
                                <label class="lfa-checkbox-label">
                                    <input type="checkbox" name="remember" value="1">
                                    <span><?php _e('Remember me', 'livingfitapparel'); ?></span>
                                </label>
                                <a href="#" class="lfa-forgot-password-link" data-show-forgot><?php _e('Forgot Password', 'livingfitapparel'); ?></a>
                            </div>

                            <div class="lfa-form-message" id="login-message"></div>

                            <button type="submit" class="lfa-submit-btn">
                                <?php _e('LOGIN', 'livingfitapparel'); ?>
                            </button>
                        </form>
                    </div>

                    <!-- Signup Form -->
                    <div class="lfa-auth-form" id="lfa-signup-form" data-form="signup">
                        <form id="lfa-signup-form-element" class="lfa-auth-form-element">
                            <div class="lfa-form-group">
                                <input type="email" id="signup-email" name="email" placeholder="<?php esc_attr_e('Email Address', 'livingfitapparel'); ?>" required autocomplete="email">
                                <span class="lfa-error-message" id="signup-email-error"></span>
                            </div>

                            <div class="lfa-form-info">
                                <p><?php _e('A link to set a new password will be sent to your email address.', 'livingfitapparel'); ?></p>
                            </div>

                            <div class="lfa-form-group">
                                <label class="lfa-checkbox-label">
                                    <input type="checkbox" name="newsletter" value="1">
                                    <span class="lfa-checkbox-text"><?php _e('Subscribe to our newsletter', 'livingfitapparel'); ?></span>
                                </label>
                            </div>

                            <div class="lfa-form-privacy">
                                <p><?php 
                                    printf(
                                        __('Your personal data will be used to support your experience throughout this website, to manage access to your account, and for other purposes described in our %s.', 'livingfitapparel'),
                                        '<a href="' . esc_url(get_permalink(get_page_by_path('privacy-policy'))) . '" target="_blank">' . __('privacy policy', 'livingfitapparel') . '</a>'
                                    );
                                ?></p>
                            </div>

                            <div class="lfa-form-message" id="signup-message"></div>

                            <button type="submit" class="lfa-submit-btn">
                                <?php _e('SIGN UP', 'livingfitapparel'); ?>
                            </button>
                        </form>
                    </div>

                    <!-- Forgot Password Form -->
                    <div class="lfa-auth-form" id="lfa-forgot-password-form" data-form="forgot">
                        <form id="lfa-forgot-password-form-element" class="lfa-auth-form-element">
                            <div class="lfa-form-group">
                                <input type="email" id="forgot-email" name="email" placeholder="<?php esc_attr_e('Email Address', 'livingfitapparel'); ?>" required autocomplete="email">
                                <span class="lfa-error-message" id="forgot-email-error"></span>
                            </div>

                            <div class="lfa-form-info">
                                <p><?php _e('A link to set a new password will be sent to your email address.', 'livingfitapparel'); ?></p>
                            </div>

                            <div class="lfa-form-message" id="forgot-message"></div>

                            <div class="lfa-form-actions">
                                <button type="button" class="lfa-back-btn" data-back-to-login>
                                    <?php _e('Back to Login', 'livingfitapparel'); ?>
                                </button>
                                <button type="submit" class="lfa-submit-btn">
                                    <?php _e('RESET PASSWORD', 'livingfitapparel'); ?>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</main>

<?php
get_footer();

