<?php
if (!defined('ABSPATH')) exit;

// AJAX Handler for User Login
add_action('wp_ajax_lfa_user_login', 'lfa_handle_user_login');
add_action('wp_ajax_nopriv_lfa_user_login', 'lfa_handle_user_login');

function lfa_handle_user_login() {
    check_ajax_referer('lfa-nonce', 'nonce');

    $email = sanitize_email($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $remember = isset($_POST['remember']) && $_POST['remember'] == '1';

    // Validation
    if (empty($email)) {
        wp_send_json_error(['message' => __('Email address is required.', 'livingfitapparel')]);
    }

    if (!is_email($email)) {
        wp_send_json_error(['message' => __('Please enter a valid email address.', 'livingfitapparel')]);
    }

    if (empty($password)) {
        wp_send_json_error(['message' => __('Password is required.', 'livingfitapparel')]);
    }

    // Get user by email
    $user = get_user_by('email', $email);

    if (!$user) {
        wp_send_json_error(['message' => __('Invalid email or password.', 'livingfitapparel')]);
    }

    // Verify password
    if (!wp_check_password($password, $user->user_pass, $user->ID)) {
        wp_send_json_error(['message' => __('Invalid email or password.', 'livingfitapparel')]);
    }

    // Login user
    wp_set_current_user($user->ID, $user->user_login);
    wp_set_auth_cookie($user->ID, $remember);

    do_action('wp_login', $user->user_login, $user);

    wp_send_json_success(['message' => __('Login successful!', 'livingfitapparel')]);
}

// AJAX Handler for User Signup
add_action('wp_ajax_lfa_user_signup', 'lfa_handle_user_signup');
add_action('wp_ajax_nopriv_lfa_user_signup', 'lfa_handle_user_signup');

function lfa_handle_user_signup() {
    check_ajax_referer('lfa-nonce', 'nonce');

    $email = sanitize_email($_POST['email'] ?? '');
    $newsletter = isset($_POST['newsletter']) && $_POST['newsletter'] == '1';

    // Validation
    if (empty($email)) {
        wp_send_json_error(['message' => __('Email address is required.', 'livingfitapparel')]);
    }

    if (!is_email($email)) {
        wp_send_json_error(['message' => __('Please enter a valid email address.', 'livingfitapparel')]);
    }

    // Check if user already exists
    if (email_exists($email)) {
        wp_send_json_error(['message' => __('An account with this email already exists.', 'livingfitapparel')]);
    }

    // Generate username from email
    $username = sanitize_user(current(explode('@', $email)));
    $counter = 1;
    while (username_exists($username)) {
        $username = $username . $counter;
        $counter++;
    }

    // Generate random password (user will set via email link)
    $password = wp_generate_password(20, false);

    // Create user
    $user_id = wp_create_user($username, $password, $email);

    if (is_wp_error($user_id)) {
        wp_send_json_error(['message' => $user_id->get_error_message()]);
    }

    // Send password reset email (so user can set their password)
    $user = get_user_by('id', $user_id);
    $key = get_password_reset_key($user);
    
    if (!is_wp_error($key)) {
        $reset_link = network_site_url("wp-login.php?action=rp&key=$key&login=" . rawurlencode($user->user_login), 'login');
        
        $message = __('Welcome! Please click the link below to set your password:', 'livingfitapparel') . "\r\n\r\n";
        $message .= $reset_link . "\r\n\r\n";
        $message .= __('If you did not request this, please ignore this email.', 'livingfitapparel');
        
        wp_mail($email, __('Set Your Password', 'livingfitapparel'), $message);
    }

    // Handle newsletter subscription if checked
    if ($newsletter) {
        // You can integrate with your newsletter service here
        // For example: update_user_meta($user_id, 'newsletter_subscribed', true);
    }

    wp_send_json_success(['message' => __('Account created! Please check your email to set your password.', 'livingfitapparel')]);
}

// AJAX Handler for Forgot Password
add_action('wp_ajax_lfa_forgot_password', 'lfa_handle_forgot_password');
add_action('wp_ajax_nopriv_lfa_forgot_password', 'lfa_handle_forgot_password');

function lfa_handle_forgot_password() {
    check_ajax_referer('lfa-nonce', 'nonce');

    $email = sanitize_email($_POST['email'] ?? '');

    // Validation
    if (empty($email)) {
        wp_send_json_error(['message' => __('Email address is required.', 'livingfitapparel')]);
    }

    if (!is_email($email)) {
        wp_send_json_error(['message' => __('Please enter a valid email address.', 'livingfitapparel')]);
    }

    // Get user by email
    $user = get_user_by('email', $email);

    if (!$user) {
        // Don't reveal if email exists or not for security
        wp_send_json_success(['message' => __('If that email address exists in our system, we have sent a password reset link.', 'livingfitapparel')]);
    }

    // Generate reset key
    $key = get_password_reset_key($user);

    if (is_wp_error($key)) {
        wp_send_json_error(['message' => __('Failed to generate reset key. Please try again.', 'livingfitapparel')]);
    }

    // Send reset email
    $reset_link = network_site_url("wp-login.php?action=rp&key=$key&login=" . rawurlencode($user->user_login), 'login');
    
    $message = __('Someone requested a password reset for your account.', 'livingfitapparel') . "\r\n\r\n";
    $message .= __('If this was you, click the link below to reset your password:', 'livingfitapparel') . "\r\n\r\n";
    $message .= $reset_link . "\r\n\r\n";
    $message .= __('If you did not request this, please ignore this email.', 'livingfitapparel');
    
    $sent = wp_mail($email, __('Password Reset', 'livingfitapparel'), $message);

    if ($sent) {
        wp_send_json_success(['message' => __('Password reset link has been sent to your email address.', 'livingfitapparel')]);
    } else {
        wp_send_json_error(['message' => __('Failed to send email. Please try again later.', 'livingfitapparel')]);
    }
}

