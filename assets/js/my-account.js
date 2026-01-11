(function ($) {
    'use strict';

    $(document).ready(function () {
        // Tab Switching
        $('.lfa-auth-tab').on('click', function () {
            var targetTab = $(this).data('tab');
            
            // Update tabs - remove active from all, add to clicked
            $('.lfa-auth-tab').removeClass('active');
            $(this).addClass('active');
            
            // Update forms
            $('.lfa-auth-form').removeClass('active');
            $('.lfa-auth-form[data-form="' + targetTab + '"]').addClass('active');
            
            // Clear messages and errors
            clearMessages();
            clearErrors();
        });

        // Update tab indicator based on active form
        function updateTabIndicator() {
            var activeForm = $('.lfa-auth-form.active').data('form');
            $('.lfa-auth-tab').removeClass('active');
            if (activeForm) {
                $('.lfa-auth-tab[data-tab="' + activeForm + '"]').addClass('active');
            }
        }

        // Initial update
        updateTabIndicator();

        // Show Forgot Password Form
        $('[data-show-forgot]').on('click', function (e) {
            e.preventDefault();
            $('.lfa-auth-tabs').hide();
            $('.lfa-forgot-password-heading').show();
            $('.lfa-auth-tab').removeClass('active');
            $('.lfa-auth-form').removeClass('active');
            $('#lfa-forgot-password-form').addClass('active');
            clearMessages();
            clearErrors();
            // Don't update tab indicator for forgot password form
        });

        // Back to Login
        $('[data-back-to-login]').on('click', function () {
            $('.lfa-auth-tabs').show();
            $('.lfa-forgot-password-heading').hide();
            $('.lfa-auth-tab').removeClass('active');
            $('.lfa-auth-tab[data-tab="login"]').addClass('active');
            $('.lfa-auth-form').removeClass('active');
            $('#lfa-login-form').addClass('active');
            clearMessages();
            clearErrors();
            updateTabIndicator();
        });

        // Password Toggle
        $(document).on('click', '.lfa-password-toggle', function (e) {
            e.preventDefault();
            e.stopPropagation();
            
            var $toggle = $(this);
            var $input = $toggle.siblings('input[type="password"], input[type="text"]');
            
            if ($input.length === 0) {
                return;
            }
            
            var currentType = $input.attr('type');
            var currentValue = $input.val();
            var cursorPosition = $input[0].selectionStart;
            
            if (currentType === 'password') {
                // Show password
                $input.attr('type', 'text');
                $toggle.addClass('active');
            } else {
                // Hide password
                $input.attr('type', 'password');
                $toggle.removeClass('active');
            }
            
            // Restore value and cursor position
            $input.val(currentValue);
            if ($input[0].setSelectionRange) {
                $input[0].setSelectionRange(cursorPosition, cursorPosition);
            }
            
            // Maintain focus on input
            $input.focus();
        });

        // Email Validation
        function validateEmail(email) {
            var re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            return re.test(email);
        }

        // Clear Errors
        function clearErrors() {
            $('.lfa-error-message').removeClass('show').text('');
            $('.lfa-form-group input').removeClass('error');
        }

        // Clear Messages
        function clearMessages() {
            $('.lfa-form-message').removeClass('show success error').text('');
        }

        // Show Error
        function showError(fieldId, message) {
            $('#' + fieldId + '-error').text(message).addClass('show');
            $('#' + fieldId).addClass('error');
        }

        // Show Message
        function showMessage(formId, message, type) {
            var $message = $('#' + formId + '-message');
            $message.removeClass('success error').addClass('show ' + type).text(message);
        }

        // Login Form
        $('#lfa-login-form-element').on('submit', function (e) {
            e.preventDefault();
            clearErrors();
            clearMessages();

            var email = $('#login-email').val().trim();
            var password = $('#login-password').val();
            var remember = $('input[name="remember"]').is(':checked');

            // Validation
            var hasError = false;

            if (!email) {
                showError('login-email', 'Email address is required.');
                hasError = true;
            } else if (!validateEmail(email)) {
                showError('login-email', 'Please enter a valid email address.');
                hasError = true;
            }

            if (!password) {
                showError('login-password', 'Password is required.');
                hasError = true;
            }

            if (hasError) {
                return;
            }

            // Disable submit button
            var $submitBtn = $(this).find('.lfa-submit-btn');
            var originalText = $submitBtn.text();
            $submitBtn.data('original-text', originalText).prop('disabled', true).text('Logging in...');

            // AJAX Request
            var self = this;
            $.ajax({
                url: LFA.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'lfa_user_login',
                    email: email,
                    password: password,
                    remember: remember ? 1 : 0,
                    nonce: LFA.nonce
                },
                success: function (response) {
                    var $btn = $(self).find('.lfa-submit-btn');
                    if (response.success) {
                        showMessage('login', response.data.message || 'Login successful! Redirecting...', 'success');
                        setTimeout(function () {
                            window.location.reload();
                        }, 1000);
                    } else {
                        showMessage('login', response.data.message || 'Login failed. Please check your credentials.', 'error');
                        $btn.prop('disabled', false).text($btn.data('original-text'));
                    }
                },
                error: function () {
                    var $btn = $(self).find('.lfa-submit-btn');
                    showMessage('login', 'An error occurred. Please try again.', 'error');
                    $btn.prop('disabled', false).text($btn.data('original-text'));
                }
            });
        });

        // Signup Form
        $('#lfa-signup-form-element').on('submit', function (e) {
            e.preventDefault();
            clearErrors();
            clearMessages();

            var email = $('#signup-email').val().trim();
            var newsletter = $('input[name="newsletter"]').is(':checked');

            // Validation
            var hasError = false;

            if (!email) {
                showError('signup-email', 'Email address is required.');
                hasError = true;
            } else if (!validateEmail(email)) {
                showError('signup-email', 'Please enter a valid email address.');
                hasError = true;
            }

            if (hasError) {
                return;
            }

            // Disable submit button
            var $submitBtn = $(this).find('.lfa-submit-btn');
            $submitBtn.prop('disabled', true).text('Signing up...');

            // AJAX Request
            $.ajax({
                url: LFA.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'lfa_user_signup',
                    email: email,
                    newsletter: newsletter ? 1 : 0,
                    nonce: LFA.nonce
                },
                success: function (response) {
                    if (response.success) {
                        showMessage('signup', response.data.message || 'Signup successful! Please check your email to set your password.', 'success');
                        $submitBtn.prop('disabled', false).text('SIGN UP');
                        // Clear form
                        $('#signup-email').val('');
                    } else {
                        showMessage('signup', response.data.message || 'Signup failed. Please try again.', 'error');
                        $submitBtn.prop('disabled', false).text('SIGN UP');
                    }
                },
                error: function () {
                    showMessage('signup', 'An error occurred. Please try again.', 'error');
                    $submitBtn.prop('disabled', false).text('SIGN UP');
                }
            });
        });

        // Forgot Password Form
        $('#lfa-forgot-password-form-element').on('submit', function (e) {
            e.preventDefault();
            clearErrors();
            clearMessages();

            var email = $('#forgot-email').val().trim();

            // Validation
            var hasError = false;

            if (!email) {
                showError('forgot-email', 'Email address is required.');
                hasError = true;
            } else if (!validateEmail(email)) {
                showError('forgot-email', 'Please enter a valid email address.');
                hasError = true;
            }

            if (hasError) {
                return;
            }

            // Disable submit button
            var $submitBtn = $(this).find('.lfa-submit-btn');
            var originalText = $submitBtn.text();
            $submitBtn.data('original-text', originalText).prop('disabled', true).text('Sending...');

            // AJAX Request
            var self = this;
            $.ajax({
                url: LFA.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'lfa_forgot_password',
                    email: email,
                    nonce: LFA.nonce
                },
                success: function (response) {
                    var $btn = $(self).find('.lfa-submit-btn');
                    if (response.success) {
                        showMessage('forgot', response.data.message || 'Password reset link has been sent to your email address.', 'success');
                        $btn.prop('disabled', false).text($btn.data('original-text'));
                        // Clear form
                        $('#forgot-email').val('');
                    } else {
                        showMessage('forgot', response.data.message || 'Failed to send reset link. Please try again.', 'error');
                        $btn.prop('disabled', false).text($btn.data('original-text'));
                    }
                },
                error: function () {
                    var $btn = $(self).find('.lfa-submit-btn');
                    showMessage('forgot', 'An error occurred. Please try again.', 'error');
                    $btn.prop('disabled', false).text($btn.data('original-text'));
                }
            });
        });

        // Real-time email validation
        $('input[type="email"]').on('blur', function () {
            var email = $(this).val().trim();
            var fieldId = $(this).attr('id');
            
            if (email && !validateEmail(email)) {
                showError(fieldId, 'Please enter a valid email address.');
            }
        });

        // Clear error on input
        $('.lfa-form-group input').on('input', function () {
            var fieldId = $(this).attr('id');
            $('#' + fieldId + '-error').removeClass('show').text('');
            $(this).removeClass('error');
        });

        // Wishlist removal handler
        $(document).on('click', '.lfa-wishlist-remove', function (e) {
            e.preventDefault();
            var $link = $(this);
            var href = $link.attr('href');
            var productId = $link.data('product-id');
            
            if (!href) {
                return;
            }
            
            // Disable link during removal
            $link.css('pointer-events', 'none').css('opacity', '0.5');
            
            // Follow the link (which will trigger our PHP handler)
            window.location.href = href;
        });
    });
})(jQuery);

