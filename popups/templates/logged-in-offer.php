<?php
/**
 * Logged In Offer Popup Template
 * 
 * Template variables available:
 * - $popup_id: Popup post ID
 */

if (!defined('ABSPATH')) exit;

// Get current user info
$current_user = wp_get_current_user();
$user_name = $current_user->display_name ? $current_user->display_name : $current_user->user_login;
?>

<button class="lfa-popup-close" aria-label="<?php esc_attr_e('Close', 'livingfitapparel'); ?>">
	<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
		<path d="M18 6L6 18M6 6l12 12" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
	</svg>
</button>

<div class="lfa-popup-inner">
	<h2 class="lfa-popup-title"><?php printf(__('Hi %s!', 'livingfitapparel'), esc_html($user_name)); ?></h2>
	<p class="lfa-popup-text">
		<?php _e('As a valued member, enjoy 15% off your next purchase! Use code MEMBER15 at checkout.', 'livingfitapparel'); ?>
	</p>
	<?php if (class_exists('WooCommerce')): ?>
		<div class="lfa-popup-actions">
			<a href="<?php echo esc_url(wc_get_page_permalink('shop')); ?>" class="lfa-popup-button lfa-popup-button-primary">
				<?php _e('Shop Now', 'livingfitapparel'); ?>
			</a>
			<button class="lfa-popup-button lfa-popup-button-secondary lfa-popup-close">
				<?php _e('Close', 'livingfitapparel'); ?>
			</button>
		</div>
	<?php else: ?>
		<div class="lfa-popup-actions">
			<button class="lfa-popup-button lfa-popup-button-primary lfa-popup-close">
				<?php _e('Got It', 'livingfitapparel'); ?>
			</button>
		</div>
	<?php endif; ?>
</div>
