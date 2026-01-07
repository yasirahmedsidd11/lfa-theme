<?php
/**
 * Checkout Form
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/checkout/form-checkout.php.
 *
 * @see https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 3.5.0
 */

defined( 'ABSPATH' ) || exit;

// Ensure WooCommerce is active
if ( ! class_exists( 'WooCommerce' ) ) {
	return;
}

// Ensure checkout object exists
if ( ! isset( $checkout ) || ! $checkout ) {
	$checkout = WC()->checkout();
}

// Check if cart exists and is empty
if ( ! WC()->cart || WC()->cart->is_empty() ) {
	?>
	<div class="woocommerce-info">
		<?php esc_html_e( 'Your cart is currently empty.', 'woocommerce' ); ?>
		<a class="button wc-backward" href="<?php echo esc_url( apply_filters( 'woocommerce_return_to_shop_redirect', wc_get_page_permalink( 'shop' ) ) ); ?>">
			<?php esc_html_e( 'Return to shop', 'woocommerce' ); ?>
		</a>
	</div>
	<?php
	return;
}

do_action( 'woocommerce_before_checkout_form', $checkout );

// If checkout registration is disabled and not logged in, the user cannot checkout.
if ( ! $checkout->is_registration_enabled() && $checkout->is_registration_required() && ! is_user_logged_in() ) {
	echo esc_html( apply_filters( 'woocommerce_checkout_must_be_logged_in_message', __( 'You must be logged in to checkout.', 'woocommerce' ) ) );
	return;
}

?>

<form name="checkout" method="post" class="checkout woocommerce-checkout" action="<?php echo esc_url( wc_get_checkout_url() ); ?>" enctype="multipart/form-data">

	<div class="lfa-checkout-wrapper">
		<div class="lfa-checkout-container">
			<div class="lfa-checkout-layout">
				
				<!-- Left Column: Billing Details -->
				<div class="lfa-checkout-billing">
					<h2 class="lfa-checkout-section-title"><?php esc_html_e( 'BILLING DETAILS', 'woocommerce' ); ?></h2>
					
					<?php if ( $checkout->get_checkout_fields( 'billing' ) ) : ?>
						<div class="lfa-billing-fields">
							<?php do_action( 'woocommerce_checkout_before_customer_details' ); ?>
							
							<div class="woocommerce-billing-fields">
								<?php do_action( 'woocommerce_before_checkout_billing_form', $checkout ); ?>
								
								<div class="woocommerce-billing-fields__field-wrapper">
									<?php
									$fields = $checkout->get_checkout_fields( 'billing' );
									
									foreach ( $fields as $key => $field ) {
										// Skip email field - we'll add it separately
										if ( $key === 'billing_email' ) {
											continue;
										}
										
										woocommerce_form_field( $key, $field, $checkout->get_value( $key ) );
									}
									?>
								</div>
								
								<?php do_action( 'woocommerce_after_checkout_billing_form', $checkout ); ?>
							</div>
							
							<!-- Email Address (separate field) -->
							<?php if ( isset( $fields['billing_email'] ) ) : ?>
								<div class="lfa-email-field">
									<?php woocommerce_form_field( 'billing_email', $fields['billing_email'], $checkout->get_value( 'billing_email' ) ); ?>
								</div>
							<?php endif; ?>
							
							<!-- Newsletter Checkbox -->
							<p class="form-row form-row-wide lfa-newsletter-checkbox">
								<label class="woocommerce-form__label woocommerce-form__label-for-checkbox checkbox">
									<input class="woocommerce-form__input woocommerce-form__input-checkbox input-checkbox" type="checkbox" name="lfa_subscribe_newsletter" value="1" />
									<span><?php esc_html_e( 'Subscribe to our newsletter', 'woocommerce' ); ?></span>
								</label>
							</p>
							
							<!-- Create Account Checkbox (only if not logged in) -->
							<?php if ( ! is_user_logged_in() && $checkout->is_registration_enabled() ) : ?>
								<p class="form-row form-row-wide lfa-create-account-checkbox">
									<label class="woocommerce-form__label woocommerce-form__label-for-checkbox checkbox">
										<input class="woocommerce-form__input woocommerce-form__input-checkbox input-checkbox" id="createaccount" type="checkbox" name="createaccount" value="1" />
										<span><?php esc_html_e( 'Create Account', 'woocommerce' ); ?></span>
									</label>
								</p>
							<?php endif; ?>
							
							<!-- Ship to Different Address -->
							<?php if ( $checkout->get_checkout_fields( 'shipping' ) ) : ?>
								<p class="form-row form-row-wide lfa-ship-to-different-address">
									<label class="woocommerce-form__label woocommerce-form__label-for-checkbox checkbox">
										<input id="ship-to-different-address-checkbox" class="woocommerce-form__input woocommerce-form__input-checkbox input-checkbox" <?php checked( apply_filters( 'woocommerce_ship_to_different_address_checked', 'shipping' === get_option( 'woocommerce_ship_to_destination' ) ? 1 : 0 ), 1 ); ?> type="checkbox" name="ship_to_different_address" value="1" />
										<span><?php esc_html_e( 'Ship to a different address?', 'woocommerce' ); ?></span>
									</label>
								</p>
								
								<div class="shipping_address">
									<?php do_action( 'woocommerce_before_checkout_shipping_form', $checkout ); ?>
									
									<div class="woocommerce-shipping-fields__field-wrapper">
										<?php
										$shipping_fields = $checkout->get_checkout_fields( 'shipping' );
										
										foreach ( $shipping_fields as $key => $field ) {
											woocommerce_form_field( $key, $field, $checkout->get_value( $key ) );
										}
										?>
									</div>
									
									<?php do_action( 'woocommerce_after_checkout_shipping_form', $checkout ); ?>
								</div>
							<?php endif; ?>
							
							<!-- Order Notes -->
							<?php if ( $checkout->get_checkout_fields( 'order' ) ) : ?>
								<div class="lfa-order-notes">
									<?php
									foreach ( $checkout->get_checkout_fields( 'order' ) as $key => $field ) {
										woocommerce_form_field( $key, $field, $checkout->get_value( $key ) );
									}
									?>
								</div>
							<?php endif; ?>
							
							<?php do_action( 'woocommerce_checkout_after_customer_details' ); ?>
						</div>
					<?php endif; ?>
				</div>
				
				<!-- Right Column: Order Review and Payment -->
				<div class="lfa-checkout-right-column">
					<!-- Order Review Accordion -->
					<div class="lfa-checkout-order-review">
						<?php
						do_action( 'woocommerce_checkout_before_order_review_heading' );
						?>
						<h2 class="lfa-checkout-section-title lfa-order-review-title">
							<span><?php esc_html_e( 'ORDER REVIEW', 'woocommerce' ); ?></span>
							<span class="lfa-chevron-up lfa-order-review-chevron">
								<svg width="18" height="11" viewBox="0 0 18 11" fill="none" xmlns="http://www.w3.org/2000/svg">
									<path d="M17 9.41406L9 1.41406L1 9.41406" stroke="#222222" stroke-width="2" stroke-linecap="round"/>
								</svg>
							</span>
						</h2>
						<?php
						do_action( 'woocommerce_checkout_before_order_review' );
						?>
						<div class="lfa-order-review-content">
							<div id="order_review" class="woocommerce-checkout-review-order">
								<?php do_action( 'woocommerce_checkout_order_review' ); ?>
							</div>
						</div>
						<?php
						do_action( 'woocommerce_checkout_after_order_review' );
						?>
					</div>
					
					<!-- Payment Methods (Separate Accordion) -->
					<?php if ( ! WC()->cart->is_empty() && WC()->cart->needs_payment() ) : ?>
					<div class="lfa-checkout-payment">
						<h3 class="lfa-checkout-section-title lfa-payment-title">
							<span><?php esc_html_e( 'PAYMENT METHODS', 'woocommerce' ); ?></span>
							<span class="lfa-chevron-up lfa-payment-chevron">
								<svg width="18" height="11" viewBox="0 0 18 11" fill="none" xmlns="http://www.w3.org/2000/svg">
									<path d="M17 9.41406L9 1.41406L1 9.41406" stroke="#222222" stroke-width="2" stroke-linecap="round"/>
								</svg>
							</span>
						</h3>
						<div class="lfa-payment-methods-content">
							<?php
							if ( ! empty( WC()->payment_gateways()->get_available_payment_gateways() ) ) {
								?>
								<ul class="wc_payment_methods payment_methods methods">
									<?php
									$available_gateways = WC()->payment_gateways()->get_available_payment_gateways();
									if ( ! empty( $available_gateways ) ) {
										WC()->payment_gateways()->set_current_gateway( $available_gateways );
									}

									foreach ( $available_gateways as $gateway ) {
										wc_get_template( 'checkout/payment-method.php', array( 'gateway' => $gateway ) );
									}
									?>
								</ul>
								<?php
							} else {
								$billing_country = WC()->customer && WC()->customer->get_billing_country() ? WC()->customer->get_billing_country() : '';
								$message = $billing_country ? esc_html__( 'Sorry, it seems that there are no available payment methods for your location. Please contact us if you require assistance or wish to make alternate arrangements.', 'woocommerce' ) : esc_html__( 'Please fill in your details above to see available payment methods.', 'woocommerce' );
								echo '<li class="woocommerce-notice woocommerce-notice--info woocommerce-info">' . apply_filters( 'woocommerce_no_available_payment_methods_message', $message, $billing_country ) . '</li>'; // @codingStandardsIgnoreLine
							}
							?>
						</div>
					</div>
					<?php endif; ?>
					
					<!-- Privacy Policy, Terms, Grand Total, and Place Order Button (Always Visible) -->
					<?php if ( ! WC()->cart->is_empty() && WC()->cart->needs_payment() ) : ?>
					<div class="lfa-checkout-payment-footer">
						<!-- Privacy Policy -->
						<div class="lfa-checkout-privacy">
							<p class="lfa-privacy-text">
								<?php
								$privacy_policy_text = __( 'Your personal data will be used to process your order, support your experience throughout this website, and for other purposes described in our privacy policy.', 'woocommerce' );
								$privacy_policy_link = get_privacy_policy_url();
								if ( $privacy_policy_link ) {
									$privacy_policy_text = str_replace( 'privacy policy', '<a href="' . esc_url( $privacy_policy_link ) . '">privacy policy</a>', $privacy_policy_text );
								}
								echo wp_kses_post( $privacy_policy_text );
								?>
							</p>
						</div>
						
						<!-- Terms and Conditions -->
						<?php
						if ( wc_get_page_id( 'terms' ) > 0 && apply_filters( 'woocommerce_checkout_show_terms', true ) ) :
							$terms_page_id = wc_terms_and_conditions_page_id();
							$terms_page    = $terms_page_id ? get_post( $terms_page_id ) : null;
							$terms_content = $terms_page ? $terms_page->post_content : '';
							?>
							<div class="lfa-checkout-terms">
								<p class="form-row terms wc-terms-and-conditions">
									<label class="woocommerce-form__label woocommerce-form__label-for-checkbox checkbox">
										<input type="checkbox" class="woocommerce-form__input woocommerce-form__input-checkbox input-checkbox" name="terms" <?php checked( apply_filters( 'woocommerce_terms_is_checked_default', isset( $_POST['terms'] ) ), true ); // WPCS: input var ok, csrf ok. ?> id="terms" />
										<span class="woocommerce-form__label-text">
											<?php
											printf(
												/* translators: %s terms and conditions page name and link */
												__( 'I have read and agree to the website %s', 'woocommerce' ),
												'<a href="' . esc_url( wc_get_page_permalink( 'terms' ) ) . '" class="woocommerce-terms-and-conditions-link" target="_blank">' . esc_html__( 'terms and conditions', 'woocommerce' ) . '</a> *'
											);
											?>
										</span>
									</label>
									<input type="hidden" name="terms-field" value="1" />
								</p>
							</div>
						<?php endif; ?>
						
						<!-- Grand Total -->
						<div class="lfa-checkout-grand-total">
							<span class="lfa-grand-total-label"><?php esc_html_e( 'Grand Total', 'woocommerce' ); ?>:</span>
							<span class="lfa-grand-total-value"><?php wc_cart_totals_order_total_html(); ?></span>
						</div>
						
						<!-- Place Order Button -->
						<div class="lfa-checkout-place-order">
							<?php do_action( 'woocommerce_review_order_before_submit' ); ?>
							
							<?php echo apply_filters( 'woocommerce_order_button_html', '<button type="submit" class="button alt lfa-place-order-button" name="woocommerce_checkout_place_order" id="place_order" value="' . esc_attr( __( 'Place order', 'woocommerce' ) ) . '" data-value="' . esc_attr( __( 'Place order', 'woocommerce' ) ) . '">' . esc_html( __( 'Place order', 'woocommerce' ) ) . '</button>' ); // @codingStandardsIgnoreLine ?>
							
							<?php do_action( 'woocommerce_review_order_after_submit' ); ?>
							
							<?php wp_nonce_field( 'woocommerce-process_checkout', 'woocommerce-process-checkout-nonce' ); ?>
						</div>
					</div>
					<?php endif; ?>
				</div>
				
			</div>
		</div>
	</div>

</form>

<?php do_action( 'woocommerce_after_checkout_form', $checkout ); ?>

