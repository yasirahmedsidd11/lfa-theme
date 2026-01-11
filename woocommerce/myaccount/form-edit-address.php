<?php
/**
 * Edit address form
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/myaccount/form-edit-address.php.
 *
 * @see     https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 2.6.0
 */

defined( 'ABSPATH' ) || exit;

$page_title = ( 'billing' === $load_address ) ? __( 'Billing address', 'woocommerce' ) : __( 'Shipping address', 'woocommerce' );

do_action( 'woocommerce_before_edit_address_form', $load_address ); ?>

<div class="woocommerce-MyAccount-content">
	<?php if ( ! $load_address ) : ?>
		<?php wc_get_template( 'myaccount/my-address.php' ); ?>
	<?php else : ?>
		<form method="post" class="edit-address-form">
			<h3><?php echo apply_filters( 'woocommerce_my_account_edit_address_title', $page_title, $load_address ); ?></h3>

			<?php do_action( "woocommerce_before_edit_address_form_{$load_address}" ); ?>

			<?php foreach ( $address as $key => $field ) : ?>
				<?php woocommerce_form_field( $key, $field, wc_get_post_data_by_key( $key, $field['value'] ) ); ?>
			<?php endforeach; ?>

			<?php do_action( "woocommerce_after_edit_address_form_{$load_address}" ); ?>

			<p>
				<button type="submit" class="button save-address-button" name="save_address" value="<?php esc_attr_e( 'Save address', 'woocommerce' ); ?>"><?php esc_html_e( 'Save address', 'woocommerce' ); ?></button>
				<?php wp_nonce_field( 'woocommerce-edit_address', 'woocommerce-edit-address-nonce' ); ?>
				<input type="hidden" name="action" value="edit_address" />
			</p>
		</form>
	<?php endif; ?>
</div>

<?php do_action( 'woocommerce_after_edit_address_form', $load_address ); ?>
