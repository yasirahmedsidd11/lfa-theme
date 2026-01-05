<?php
/**
 * Cart totals
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/cart/cart-totals.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 2.3.6
 */

defined( 'ABSPATH' ) || exit;

?>
<div class="cart_totals <?php echo ( WC()->customer->has_calculated_shipping() ) ? 'calculated_shipping' : ''; ?>">

	<?php do_action( 'woocommerce_before_cart_totals' ); ?>

	<div class="lfa-cart-totals-content">
		<div class="lfa-cart-subtotal-row">
			<span class="lfa-cart-subtotal-label"><?php esc_html_e( 'Subtotal', 'woocommerce' ); ?></span>
			<span class="lfa-cart-subtotal-value"><?php wc_cart_totals_subtotal_html(); ?></span>
		</div>

		<?php foreach ( WC()->cart->get_coupons() as $code => $coupon ) : ?>
			<div class="lfa-cart-discount-row">
				<span class="lfa-cart-discount-label"><?php wc_cart_totals_coupon_label( $coupon ); ?></span>
				<span class="lfa-cart-discount-value"><?php wc_cart_totals_coupon_html( $coupon ); ?></span>
			</div>
		<?php endforeach; ?>

		<?php if ( WC()->cart->needs_shipping() ) : ?>
			<?php do_action( 'woocommerce_cart_totals_before_shipping' ); ?>

			<div class="lfa-cart-shipping-section">
				<?php if ( WC()->cart->show_shipping() ) : ?>
					<?php
					$shipping_state = WC()->customer->get_shipping_state() ? WC()->customer->get_shipping_state() : WC()->customer->get_billing_state();
					$shipping_title = $shipping_state ? sprintf( esc_html__( 'Shipping to %s', 'woocommerce' ), esc_html( $shipping_state ) ) : esc_html__( 'Shipping', 'woocommerce' );
					?>
					<h3 class="lfa-cart-shipping-title"><?php echo $shipping_title; ?></h3>
					<?php wc_cart_totals_shipping_html(); ?>
				<?php else : ?>
					<h3 class="lfa-cart-shipping-title"><?php esc_html_e( 'Shipping', 'woocommerce' ); ?></h3>
				<?php endif; ?>

				<?php if ( 'yes' === get_option( 'woocommerce_enable_shipping_calc' ) ) : ?>
					<?php woocommerce_shipping_calculator(); ?>
				<?php endif; ?>
			</div>

			<?php do_action( 'woocommerce_cart_totals_after_shipping' ); ?>
		<?php endif; ?>

		<?php foreach ( WC()->cart->get_fees() as $fee ) : ?>
			<div class="lfa-cart-fee-row">
				<span class="lfa-cart-fee-label"><?php echo esc_html( $fee->name ); ?></span>
				<span class="lfa-cart-fee-value"><?php wc_cart_totals_fee_html( $fee ); ?></span>
			</div>
		<?php endforeach; ?>

		<?php
		if ( wc_tax_enabled() && ! WC()->cart->display_prices_including_tax() ) {
			$taxable_address = WC()->customer->get_taxable_address();
			$estimated_text  = '';

			if ( WC()->customer->is_customer_outside_base() && ! WC()->customer->has_calculated_shipping() ) {
				/* translators: %s location. */
				$estimated_text = sprintf( ' <small>' . esc_html__( '(estimated for %s)', 'woocommerce' ) . '</small>', WC()->countries->estimated_for_prefix( $taxable_address[0] ) . WC()->countries->countries[ $taxable_address[0] ] );
			}

			if ( 'itemized' === get_option( 'woocommerce_tax_total_display' ) ) {
				foreach ( WC()->cart->get_tax_totals() as $code => $tax ) { // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
					?>
					<div class="lfa-cart-tax-row">
						<span class="lfa-cart-tax-label"><?php echo esc_html( $tax->label ) . $estimated_text; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
						<span class="lfa-cart-tax-value"><?php echo wp_kses_post( $tax->formatted_amount ); ?></span>
					</div>
					<?php
				}
			} else {
				?>
				<div class="lfa-cart-tax-row">
					<span class="lfa-cart-tax-label"><?php echo esc_html( WC()->countries->tax_or_vat() ) . $estimated_text; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
					<span class="lfa-cart-tax-value"><?php wc_cart_totals_taxes_total_html(); ?></span>
				</div>
				<?php
			}
		}
		?>

		<?php do_action( 'woocommerce_cart_totals_before_order_total' ); ?>

		<div class="lfa-cart-total-row">
			<span class="lfa-cart-total-label"><?php esc_html_e( 'Total', 'woocommerce' ); ?></span>
			<span class="lfa-cart-total-value"><?php wc_cart_totals_order_total_html(); ?></span>
		</div>

		<?php do_action( 'woocommerce_cart_totals_after_order_total' ); ?>
	</div>

	<div class="wc-proceed-to-checkout">
		<?php do_action( 'woocommerce_proceed_to_checkout' ); ?>
	</div>

	<?php do_action( 'woocommerce_after_cart_totals' ); ?>

</div>

