<?php
/**
 * Edit account form
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/myaccount/form-edit-account.php.
 *
 * @see     https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 2.6.0
 */

defined( 'ABSPATH' ) || exit;

$user = wp_get_current_user();

do_action( 'woocommerce_before_edit_account_form' ); ?>

<div class="woocommerce-MyAccount-content">
	<h2 class="woocommerce-MyAccount-content-title" style="border:0; padding:0 ;">ACCOUNT DETAILS</h2>
	
	<form class="woocommerce-EditAccountForm edit-account" action="" method="post" <?php do_action( 'woocommerce_edit_account_form_tag' ); ?> >
		
		<?php do_action( 'woocommerce_edit_account_form_start' ); ?>

		<div class="lfa-account-details-section">
			<div class="lfa-form-row">
				<label for="account_first_name"><?php esc_html_e( 'First name', 'woocommerce' ); ?></label>
				<input type="text" class="woocommerce-Input woocommerce-Input--text input-text" name="account_first_name" id="account_first_name" autocomplete="given-name" value="<?php echo esc_attr( $user->first_name ); ?>" />
			</div>

			<div class="lfa-form-row">
				<label for="account_last_name"><?php esc_html_e( 'Last name', 'woocommerce' ); ?></label>
				<input type="text" class="woocommerce-Input woocommerce-Input--text input-text" name="account_last_name" id="account_last_name" autocomplete="family-name" value="<?php echo esc_attr( $user->last_name ); ?>" />
			</div>

			<div class="lfa-form-row">
				<label for="account_email"><?php esc_html_e( 'Email address', 'woocommerce' ); ?></label>
				<input type="email" class="woocommerce-Input woocommerce-Input--email input-text" name="account_email" id="account_email" autocomplete="email" value="<?php echo esc_attr( $user->user_email ); ?>" />
			</div>

			<div class="lfa-form-row">
				<label for="account_display_name"><?php esc_html_e( 'Display name', 'woocommerce' ); ?></label>
				<input type="text" class="woocommerce-Input woocommerce-Input--text input-text" name="account_display_name" id="account_display_name" value="<?php echo esc_attr( $user->display_name ); ?>" />
			</div>
		</div>

		<div class="lfa-password-change-section">
			<h3 class="lfa-password-change-title"><?php esc_html_e( 'Password change', 'woocommerce' ); ?></h3>
			
			<div class="lfa-form-row">
				<label for="password_current"><?php esc_html_e( 'Current password (leave blank to leave unchanged)', 'woocommerce' ); ?></label>
				<input type="password" class="woocommerce-Input woocommerce-Input--password input-text" name="password_current" id="password_current" autocomplete="off" />
			</div>

			<div class="lfa-form-row">
				<label for="password_1"><?php esc_html_e( 'New password (leave blank to leave unchanged)', 'woocommerce' ); ?></label>
				<input type="password" class="woocommerce-Input woocommerce-Input--password input-text" name="password_1" id="password_1" autocomplete="off" />
			</div>

			<div class="lfa-form-row">
				<label for="password_2"><?php esc_html_e( 'Confirm new password', 'woocommerce' ); ?></label>
				<input type="password" class="woocommerce-Input woocommerce-Input--password input-text" name="password_2" id="password_2" autocomplete="off" />
			</div>
		</div>

		<?php do_action( 'woocommerce_edit_account_form' ); ?>

		<div class="lfa-form-row lfa-form-submit">
			<?php wp_nonce_field( 'save_account_details', 'save-account-details-nonce' ); ?>
			<button type="submit" class="woocommerce-Button button lfa-save-account-button" name="save_account_details" value="<?php esc_attr_e( 'Save changes', 'woocommerce' ); ?>"><?php esc_html_e( 'Save changes', 'woocommerce' ); ?></button>
			<input type="hidden" name="action" value="save_account_details" />
		</div>

		<?php do_action( 'woocommerce_edit_account_form_end' ); ?>
	</form>
</div>

<?php do_action( 'woocommerce_after_edit_account_form' ); ?>
