<?php
/**
 * My Addresses
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/myaccount/addresses.php.
 *
 * @see     https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 2.6.0
 */

defined( 'ABSPATH' ) || exit;

$customer_id = get_current_user_id();

if ( ! $customer_id ) {
	return;
}

if ( ! wc_ship_to_billing_address_only() && wc_shipping_enabled() ) {
	$get_addresses = apply_filters(
		'woocommerce_my_account_get_addresses',
		array(
			'billing'  => __( 'Billing address', 'woocommerce' ),
			'shipping' => __( 'Shipping address', 'woocommerce' ),
		),
		$customer_id
	);
} else {
	$get_addresses = apply_filters(
		'woocommerce_my_account_get_addresses',
		array(
			'billing' => __( 'Billing address', 'woocommerce' ),
		),
		$customer_id
	);
}

do_action( 'woocommerce_before_account_addresses', $get_addresses ); ?>

<div class="woocommerce-MyAccount-content">
	<?php if ( ! wc_ship_to_billing_address_only() && wc_shipping_enabled() ) : ?>
		<div class="woocommerce-Addresses u-columns woocommerce-columns woocommerce-columns--2">
	<?php endif; ?>

	<?php foreach ( $get_addresses as $name => $address_title ) : ?>
		<?php
		$address = wc_get_account_formatted_address( $name );
		?>
		<div class="woocommerce-Address woocommerce-Address--<?php echo esc_attr( $name ); ?>">
			<header class="woocommerce-Address-title lfa-address-title">
				<h3><?php echo esc_html( $address_title ); ?></h3>
			</header>
			<address class="lfa-address-content">
				<?php
				if ( $address ) {
					echo wp_kses_post( $address );
				} else {
					echo esc_html__( 'You have not set up this type of address yet.', 'woocommerce' );
				}
				?>
			</address>
			<a href="<?php echo esc_url( wc_get_endpoint_url( 'edit-address', $name ) ); ?>" class="lfa-edit-address-button">
				<?php 
				if ( $address ) {
					echo esc_html__( 'Edit', 'woocommerce' ) . ' ' . esc_html( $address_title );
				} else {
					echo esc_html__( 'Add', 'woocommerce' ) . ' ' . esc_html( $address_title );
				}
				?>
			</a>
		</div>

	<?php endforeach; ?>

	<?php if ( ! wc_ship_to_billing_address_only() && wc_shipping_enabled() ) : ?>
		</div>
	<?php endif; ?>
</div>

<?php do_action( 'woocommerce_after_account_addresses', $get_addresses ); ?>
