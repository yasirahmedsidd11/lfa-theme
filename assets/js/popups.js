/**
 * Popup Engine - JavaScript
 * 
 * Handles:
 * - Frequency control (once ever, once per session, every time)
 * - Trigger execution (page load, scroll percentage)
 * - Popup display/hide
 */

(function($) {
	'use strict';
	
	/**
	 * Popup Manager
	 */
	var PopupManager = {
		init: function() {
			var $popup = $('.lfa-popup-container');
			
			if ($popup.length === 0) {
				return;
			}
			
			// Check frequency first
			if (!this.checkFrequency($popup)) {
				return;
			}
			
			// Setup trigger
			this.setupTrigger($popup);
			
			// Setup close handlers
			this.setupCloseHandlers($popup);
		},
		
		/**
		 * Check if popup should show based on frequency rules
		 * 
		 * @param {jQuery} $popup Popup container element
		 * @return {boolean} True if popup should show
		 */
		checkFrequency: function($popup) {
			var popupId = $popup.data('popup-id');
			var frequencyType = $popup.data('frequency-type');
			var storageKey = 'lfa_popup_' + popupId;
			
			if (!frequencyType) {
				return true; // Default to showing if no frequency set
			}
			
			switch (frequencyType) {
				case 'every_time':
					return true;
					
				case 'once_ever':
					// Check localStorage
					if (this.supportsLocalStorage() && localStorage.getItem(storageKey)) {
						return false;
					}
					// Check cookie as fallback
					if (this.getCookie(storageKey)) {
						return false;
					}
					return true;
					
				case 'once_per_session':
					// Check sessionStorage
					if (this.supportsSessionStorage() && sessionStorage.getItem(storageKey)) {
						return false;
					}
					return true;
					
				default:
					return true;
			}
		},
		
		/**
		 * Mark popup as shown based on frequency type
		 * 
		 * @param {jQuery} $popup Popup container element
		 */
		markAsShown: function($popup) {
			var popupId = $popup.data('popup-id');
			var frequencyType = $popup.data('frequency-type');
			var storageKey = 'lfa_popup_' + popupId;
			
			if (!frequencyType) {
				return;
			}
			
			switch (frequencyType) {
				case 'once_ever':
					// Store in localStorage
					if (this.supportsLocalStorage()) {
						localStorage.setItem(storageKey, '1');
					}
					// Also set cookie as fallback (expires in 10 years)
					this.setCookie(storageKey, '1', 3650);
					break;
					
				case 'once_per_session':
					// Store in sessionStorage
					if (this.supportsSessionStorage()) {
						sessionStorage.setItem(storageKey, '1');
					}
					break;
			}
		},
		
		/**
		 * Setup trigger based on trigger type
		 * 
		 * @param {jQuery} $popup Popup container element
		 */
		setupTrigger: function($popup) {
			var triggerType = $popup.data('trigger-type');
			var self = this;
			
			if (!triggerType) {
				triggerType = 'page_load'; // Default
			}
			
			switch (triggerType) {
				case 'page_load':
					// Show immediately after DOM is ready
					$(document).ready(function() {
						setTimeout(function() {
							self.showPopup($popup);
						}, 100); // Small delay to ensure page is rendered
					});
					break;
					
				case 'scroll_percent':
					var scrollPercent = parseInt($popup.data('trigger-scroll-percent'), 10);
					if (isNaN(scrollPercent) || scrollPercent < 1 || scrollPercent > 100) {
						scrollPercent = 50; // Default
					}
					
					var triggered = false;
					var self = this;
					
					$(window).on('scroll.popup', function() {
						if (triggered) {
							return;
						}
						
						var scrollTop = $(window).scrollTop();
						var docHeight = $(document).height();
						var winHeight = $(window).height();
						var scrollPercentActual = (scrollTop / (docHeight - winHeight)) * 100;
						
						if (scrollPercentActual >= scrollPercent) {
							triggered = true;
							self.showPopup($popup);
							$(window).off('scroll.popup');
						}
					});
					break;
			}
		},
		
		/**
		 * Show popup
		 * 
		 * @param {jQuery} $popup Popup container element
		 */
		showPopup: function($popup) {
			$popup.fadeIn(300);
			$('body').addClass('lfa-popup-open');
			
			// Mark as shown for frequency control
			this.markAsShown($popup);
		},
		
		/**
		 * Hide popup
		 * 
		 * @param {jQuery} $popup Popup container element
		 */
		hidePopup: function($popup) {
			$popup.fadeOut(300, function() {
				$('body').removeClass('lfa-popup-open');
			});
		},
		
		/**
		 * Setup close handlers
		 * 
		 * @param {jQuery} $popup Popup container element
		 */
		setupCloseHandlers: function($popup) {
			var self = this;
			
			// Close button
			$popup.on('click', '.lfa-popup-close', function(e) {
				e.preventDefault();
				self.hidePopup($popup);
			});
			
			// Overlay click
			$popup.on('click', '.lfa-popup-overlay', function(e) {
				if (e.target === this) {
					self.hidePopup($popup);
				}
			});
			
			// ESC key
			$(document).on('keydown.popup', function(e) {
				if (e.keyCode === 27 && $popup.is(':visible')) { // ESC key
					self.hidePopup($popup);
				}
			});
		},
		
		/**
		 * Check if localStorage is supported
		 * 
		 * @return {boolean}
		 */
		supportsLocalStorage: function() {
			try {
				return 'localStorage' in window && window.localStorage !== null;
			} catch (e) {
				return false;
			}
		},
		
		/**
		 * Check if sessionStorage is supported
		 * 
		 * @return {boolean}
		 */
		supportsSessionStorage: function() {
			try {
				return 'sessionStorage' in window && window.sessionStorage !== null;
			} catch (e) {
				return false;
			}
		},
		
		/**
		 * Get cookie value
		 * 
		 * @param {string} name Cookie name
		 * @return {string|null} Cookie value or null
		 */
		getCookie: function(name) {
			var nameEQ = name + '=';
			var ca = document.cookie.split(';');
			for (var i = 0; i < ca.length; i++) {
				var c = ca[i];
				while (c.charAt(0) === ' ') {
					c = c.substring(1, c.length);
				}
				if (c.indexOf(nameEQ) === 0) {
					return c.substring(nameEQ.length, c.length);
				}
			}
			return null;
		},
		
		/**
		 * Set cookie
		 * 
		 * @param {string} name Cookie name
		 * @param {string} value Cookie value
		 * @param {number} days Days until expiration
		 */
		setCookie: function(name, value, days) {
			var expires = '';
			if (days) {
				var date = new Date();
				date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
				expires = '; expires=' + date.toUTCString();
			}
			document.cookie = name + '=' + (value || '') + expires + '; path=/';
		},
		
		/**
		 * Handle Newsletter Form Submission
		 */
		handleNewsletterForm: function() {
			var self = this;
			$(document).on('submit', '.lfa-popup-newsletter-form', function(e) {
				e.preventDefault();
				
				var $form = $(this);
				var $message = $form.find('.lfa-popup-form-message');
				var $submitBtn = $form.find('.lfa-popup-submit-button');
				var email = $form.find('.lfa-popup-email-input').val().trim();
				var interest = $form.find('input[name="interest"]:checked').val();
				
				// Clear previous messages
				$message.removeClass('success error').text('');
				
				// Basic validation
				if (!email) {
					$message.addClass('error').text('Please enter your email address.');
					return;
				}
				
				// Email validation
				var emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
				if (!emailRegex.test(email)) {
					$message.addClass('error').text('Please enter a valid email address.');
					return;
				}
				
				// Disable submit button
				$submitBtn.prop('disabled', true);
				var originalText = $submitBtn.html();
				$submitBtn.html('<span class="lfa-popup-button-main">Processing...</span>');
				
				// Use AJAX to submit to WordPress
				var ajaxUrl = (typeof LFA !== 'undefined' && LFA.ajaxUrl) ? LFA.ajaxUrl : '/wp-admin/admin-ajax.php';
				var nonce = (typeof LFA !== 'undefined' && LFA.nonce) ? LFA.nonce : '';
				
				$.ajax({
					url: ajaxUrl,
					method: 'POST',
					data: {
						action: 'lfa_popup_newsletter_subscribe',
						email: email,
						interest: interest || 'both',
						nonce: nonce
					},
					success: function(response) {
						if (response.success) {
							$message.addClass('success').text(response.data && response.data.message ? response.data.message : 'Thank you! Check your email for your discount code.');
							$form[0].reset();
							
							// Close popup after 2 seconds
							setTimeout(function() {
								var $popup = $form.closest('.lfa-popup-container');
								self.hidePopup($popup);
							}, 2000);
						} else {
							$message.addClass('error').text(response.data && response.data.message ? response.data.message : 'There was an error. Please try again.');
						}
						$submitBtn.prop('disabled', false).html(originalText);
					},
					error: function(xhr, status, error) {
						$message.addClass('error').text('There was an error submitting your email. Please try again.');
						$submitBtn.prop('disabled', false).html(originalText);
					}
				});
			});
		}
	};
	
	// Initialize on DOM ready
	$(document).ready(function() {
		// Initialize popup manager
		PopupManager.init();
		
		// Setup newsletter form handler
		PopupManager.handleNewsletterForm();
	});
	
})(jQuery);
