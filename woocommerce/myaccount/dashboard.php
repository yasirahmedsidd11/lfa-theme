<?php
/**
 * My Account dashboard
 *
 * Shows the first intro screen on the account dashboard.
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/myaccount/dashboard.php.
 *
 * @see     https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 2.6.0
 */

defined( 'ABSPATH' ) || exit;

$current_user = wp_get_current_user();
?>

<div class="woocommerce-MyAccount-content">
	<h2 class="woocommerce-MyAccount-content-title" style="border:0; padding-inline: 0;">DETAILS</h2>
	
	<div class="woocommerce-MyAccount-details">
		<p><strong>Name:</strong> <?php echo esc_html( $current_user->display_name ); ?></p>
		<p><strong>Email:</strong> <?php echo esc_html( $current_user->user_email ); ?></p>
	</div>
</div>
