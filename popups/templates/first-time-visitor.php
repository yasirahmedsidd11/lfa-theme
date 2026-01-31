<?php
/**
 * First Time Visitor Popup Template
 * 
 * Template variables available:
 * - $popup_id: Popup post ID
 */

if (!defined('ABSPATH')) exit;

// Get featured image
$featured_image_id = get_post_thumbnail_id($popup_id);
$featured_image_url = $featured_image_id ? wp_get_attachment_image_url($featured_image_id, 'full') : '';

// Get site logo
$logo_id = lfa_get('general.logo_id');
if (!$logo_id) {
	$logo_id = get_theme_mod('custom_logo');
}
$logo_url = $logo_id ? wp_get_attachment_image_url($logo_id, 'full') : '';

// Get newsletter shortcode
$newsletter_shortcode = lfa_get('footer.newsletter_sc', lfa_get('home.footer.newsletter_sc', ''));
?>

<button class="lfa-popup-close" aria-label="<?php esc_attr_e('Close', 'livingfitapparel'); ?>">
	<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
		<path d="M18 6L6 18M6 6l12 12" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
	</svg>
</button>

<div class="lfa-popup-inner lfa-popup-two-column">
	<!-- Left Column: Featured Image -->
	<?php if ($featured_image_url): ?>
		<div class="lfa-popup-image-column">
			<img src="<?php echo esc_url($featured_image_url); ?>" alt="<?php echo esc_attr(get_the_title($popup_id)); ?>" class="lfa-popup-featured-image">
		</div>
	<?php endif; ?>
	
	<!-- Right Column: Content -->
	<div class="lfa-popup-content-column">
		<?php if ($logo_url): ?>
			<div class="lfa-popup-logo">
				<img src="<?php echo esc_url($logo_url); ?>" alt="<?php echo esc_attr(get_bloginfo('name')); ?>" class="lfa-popup-logo-img">
				
			</div>
		<?php endif; ?>
		<div class="lfa-popup-unlock-text">UNLOCK</div>
		<h2 class="lfa-popup-title-large">15% OFF</h2>
		<h3 class="lfa-popup-subtitle">WHEN YOU SIGN UP FOR EMAIL</h3>
		<p class="lfa-popup-disclaimer">*FULL PRICE ITEMS, ONLINE ONLY</p>
		
		<p class="lfa-popup-privacy-text">
			<?php _e('By submitting your email address, you agree to receive marketing emails from LFA. We may use information collected about you on our site to suggest other products and offers. You can unsubscribe at any time.', 'livingfitapparel'); ?>
			<?php
			$privacy_link = get_permalink(get_page_by_path('privacy-policy'));
			$terms_link = get_permalink(get_page_by_path('terms-of-service'));
			if ($privacy_link || $terms_link):
			?>
				<?php _e('View', 'livingfitapparel'); ?>
				<?php if ($terms_link): ?>
					<a href="<?php echo esc_url($terms_link); ?>" target="_blank"><?php _e('Terms', 'livingfitapparel'); ?></a>
				<?php endif; ?>
				<?php if ($privacy_link && $terms_link): ?> & <?php endif; ?>
				<?php if ($privacy_link): ?>
					<a href="<?php echo esc_url($privacy_link); ?>" target="_blank"><?php _e('Privacy', 'livingfitapparel'); ?></a>
				<?php endif; ?>
			<?php endif; ?>
		</p>
		
		<form class="lfa-popup-newsletter-form" id="lfa-popup-newsletter-form-<?php echo esc_attr($popup_id); ?>">
			<div class="lfa-popup-interest-group">
				<label class="lfa-popup-interest-label"><?php _e('What are you interested in?', 'livingfitapparel'); ?></label>
				<div class="lfa-popup-radio-group">
					<label class="lfa-popup-radio-label">
						<input type="radio" name="interest" value="womens" checked>
						<span><?php _e("Women's", 'livingfitapparel'); ?></span>
					</label>
					<label class="lfa-popup-radio-label">
						<input type="radio" name="interest" value="mens">
						<span><?php _e("Men's", 'livingfitapparel'); ?></span>
					</label>
					<label class="lfa-popup-radio-label">
						<input type="radio" name="interest" value="both">
						<span><?php _e('Both', 'livingfitapparel'); ?></span>
					</label>
				</div>
			</div>
			
			<div class="lfa-popup-email-group">
				<input type="email" name="email" class="lfa-popup-email-input" placeholder="<?php esc_attr_e('Email Address', 'livingfitapparel'); ?>" required>
			</div>
			
			<button type="submit" class="lfa-popup-submit-button">
				<span class="lfa-popup-button-main"><?php _e('GET 15% OFF NOW', 'livingfitapparel'); ?></span>
				<span class="lfa-popup-button-sub"><?php _e('When you sign up for emails', 'livingfitapparel'); ?></span>
			</button>
			
			<div class="lfa-popup-form-message"></div>
		</form>
	</div>
</div>
