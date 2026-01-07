<?php
/**
 * Order review
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/checkout/order-review.php.
 *
 * @see https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 3.8.0
 */

defined( 'ABSPATH' ) || exit;

// Ensure WooCommerce is active
if ( ! class_exists( 'WooCommerce' ) ) {
	return;
}

// Ensure cart exists
if ( ! WC()->cart ) {
	return;
}

// Don't return early if cart is empty - let WooCommerce handle it
// We'll just check before displaying items
$checkout = WC()->checkout();
?>
<?php if ( ! WC()->cart->is_empty() ) : ?>
<div class="lfa-order-review-items">
	<?php
	do_action( 'woocommerce_review_order_before_cart_contents' );

	foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
		$_product = apply_filters( 'woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key );

		if ( $_product && $_product->exists() && $cart_item['quantity'] > 0 && apply_filters( 'woocommerce_checkout_cart_item_visible', true, $cart_item, $cart_item_key ) ) {
			$product_permalink = apply_filters( 'woocommerce_cart_item_permalink', $_product->is_visible() ? $_product->get_permalink( $cart_item ) : '', $cart_item, $cart_item_key );
			?>
			<div class="lfa-checkout-item <?php echo esc_attr( apply_filters( 'woocommerce_cart_item_class', 'cart_item', $cart_item, $cart_item_key ) ); ?>">
				<!-- Column 1: Image -->
				<div class="lfa-checkout-item-image">
					<?php
					$thumbnail = apply_filters( 'woocommerce_cart_item_thumbnail', $_product->get_image(), $cart_item, $cart_item_key );
					if ( ! $product_permalink ) {
						echo $thumbnail; // PHPCS: XSS ok.
					} else {
						printf( '<a href="%s">%s</a>', esc_url( $product_permalink ), $thumbnail ); // PHPCS: XSS ok.
					}
					?>
					<span class="lfa-checkout-item-quantity-badge"><?php echo esc_html( $cart_item['quantity'] ); ?></span>
				</div>
				<!-- Column 2: Title and Attributes -->
				<div class="lfa-checkout-item-details">
					<div class="lfa-checkout-item-title">
						<?php
						// Get product name without attributes - use parent product name for variations
						if ( $_product->is_type( 'variation' ) ) {
							$parent_id = $_product->get_parent_id();
							if ( $parent_id ) {
								$parent_product = wc_get_product( $parent_id );
								$display_name = $parent_product ? $parent_product->get_name() : $_product->get_name();
							} else {
								$display_name = $_product->get_name();
							}
						} else {
							$display_name = $_product->get_name();
						}
						// Strip any HTML that might contain attributes
						$display_name = wp_strip_all_tags( $display_name );
						
						if ( ! $product_permalink ) {
							echo esc_html( $display_name );
						} else {
							printf( '<a href="%s">%s</a>', esc_url( $product_permalink ), esc_html( $display_name ) );
						}
						?>
					</div>
					<div class="lfa-checkout-item-attributes">
						<?php
						// Extract attribute values and format them with "/"
						$attributes = array();
						if ( ! empty( $cart_item['variation'] ) && is_array( $cart_item['variation'] ) ) {
							foreach ( $cart_item['variation'] as $key => $value ) {
								if ( ! empty( $value ) ) {
									$taxonomy = str_replace( 'attribute_', '', $key );
									// Validate taxonomy exists before calling get_term_by
									if ( taxonomy_exists( $taxonomy ) ) {
										$term = get_term_by( 'slug', $value, $taxonomy );
										if ( $term && ! is_wp_error( $term ) ) {
											$attributes[] = $term->name;
										} else {
											$attributes[] = $value;
										}
									} else {
										$attributes[] = $value;
									}
								}
							}
						}
						if ( ! empty( $attributes ) ) {
							?>
							<span class="lfa-checkout-item-attr"><?php echo esc_html( implode( ' / ', $attributes ) ); ?></span>
							<?php
						} else {
							// Fallback to WooCommerce formatted data if no variation attributes
							echo wc_get_formatted_cart_item_data( $cart_item ); // PHPCS: XSS ok.
						}
						?>
					</div>
				</div>
				<!-- Column 3: Price -->
				<div class="lfa-checkout-item-price">
					<?php
					echo apply_filters( 'woocommerce_cart_item_subtotal', WC()->cart->get_product_subtotal( $_product, $cart_item['quantity'] ), $cart_item, $cart_item_key ); // PHPCS: XSS ok.
					?>
				</div>
			</div>
			<?php
		}
	}

	do_action( 'woocommerce_review_order_after_cart_contents' );
	?>
</div>
<?php endif; ?>

<!-- Order Totals -->
<?php if ( ! WC()->cart->is_empty() ) : ?>
<div class="lfa-checkout-totals">
	<!-- Discount/Coupon Form -->
	<?php if ( wc_coupons_enabled() ) { ?>
		<div class="lfa-checkout-coupon">
			<!-- <label for="coupon_code"><?php esc_html_e( 'Discount Card or Gift Card', 'woocommerce' ); ?></label> -->
			<form class="checkout_coupon woocommerce-form-coupon" method="post" style="display:block !important">
				<?php wp_nonce_field( 'woocommerce-cart', 'woocommerce-cart-nonce' ); ?>
				<div class="lfa-coupon-input-wrapper">
					<input type="text" name="coupon_code" class="input-text" id="coupon_code" value="" placeholder="<?php esc_attr_e( 'Discount Card or Gift Card', 'woocommerce' ); ?>" />
					<button type="button" class="button lfa-apply-coupon-btn" name="apply_coupon" value="<?php esc_attr_e( 'Apply', 'woocommerce' ); ?>"><?php esc_html_e( 'Apply', 'woocommerce' ); ?></button>
				</div>
			</form>
		</div>
		
		<!-- Applied Coupons List -->
		<?php if ( ! empty( WC()->cart->get_applied_coupons() ) ) : ?>
			<div class="lfa-checkout-applied-coupons">
				<?php foreach ( WC()->cart->get_applied_coupons() as $coupon_code ) : ?>
					<div class="lfa-applied-coupon-item">
						<span class="lfa-applied-coupon-code"><?php echo esc_html( $coupon_code ); ?></span>
					<?php
					// Get the checkout URL for removing coupon
					// WooCommerce expects 'coupon' parameter for AJAX
					$remove_url = add_query_arg( 
						array(
							'coupon' => urlencode( $coupon_code ),
							'wc-ajax' => 'remove_coupon'
						), 
						wc_get_checkout_url() 
					);
					// Add nonce to URL - WooCommerce uses 'woocommerce-cart' for cart operations
					$remove_url = wp_nonce_url( $remove_url, 'woocommerce-cart' );
					?>
					<a href="<?php echo esc_url( $remove_url ); ?>" class="woocommerce-remove-coupon" data-coupon="<?php echo esc_attr( $coupon_code ); ?>">
						<?php esc_html_e( '[Remove]', 'woocommerce' ); ?>
					</a>
					</div>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>
	<?php } ?>
	
	<div class="lfa-checkout-subtotal">
		<span class="lfa-checkout-total-label"><?php esc_html_e( 'Subtotal', 'woocommerce' ); ?> <?php echo esc_html( WC()->cart->get_cart_contents_count() ); ?> <?php echo esc_html( _n( 'item', 'items', WC()->cart->get_cart_contents_count(), 'woocommerce' ) ); ?>:</span>
		<span class="lfa-checkout-total-value"><?php wc_cart_totals_subtotal_html(); ?></span>
	</div>

	<?php if ( WC()->cart->needs_shipping() && WC()->cart->show_shipping() ) : ?>
		<div class="lfa-checkout-shipping">
			<span class="lfa-checkout-total-label"><?php esc_html_e( 'Shipping', 'woocommerce' ); ?>:</span>
			<span class="lfa-checkout-total-value">
				<?php woocommerce_shipping_calculator(); ?>
			</span>
		</div>
	<?php endif; ?>

	<?php foreach ( WC()->cart->get_coupons() as $code => $coupon ) : ?>
		<!-- <div class="lfa-checkout-discount">
			<span class="lfa-checkout-total-label"><?php //wc_cart_totals_coupon_label( $coupon ); ?>:</span>
			<span class="lfa-checkout-total-value"><?php //wc_cart_totals_coupon_html( $coupon ); ?></span>
		</div> -->
	<?php endforeach; ?>

	<?php foreach ( WC()->cart->get_fees() as $fee ) : ?>
		<div class="lfa-checkout-fee">
			<span class="lfa-checkout-total-label"><?php echo esc_html( $fee->name ); ?>:</span>
			<span class="lfa-checkout-total-value"><?php wc_cart_totals_fee_html( $fee ); ?></span>
		</div>
	<?php endforeach; ?>

	<?php
	if ( wc_tax_enabled() && ! WC()->cart->display_prices_including_tax() && WC()->customer ) {
		$taxable_address = WC()->customer->get_taxable_address();
		$estimated_text  = '';

		if ( WC()->customer && WC()->customer->is_customer_outside_base() && ! WC()->customer->has_calculated_shipping() ) {
			/* translators: %s location. */
			$estimated_text = sprintf( ' <small>' . esc_html__( '(estimated for %s)', 'woocommerce' ) . '</small>', WC()->countries->estimated_for_prefix( $taxable_address[0] ) . WC()->countries->countries[ $taxable_address[0] ] );
		}

		if ( 'itemized' === get_option( 'woocommerce_tax_total_display' ) ) {
			foreach ( WC()->cart->get_tax_totals() as $code => $tax ) { // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
				?>
				<div class="lfa-checkout-tax">
					<span class="lfa-checkout-total-label"><?php echo esc_html( $tax->label ) . $estimated_text; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
					<span class="lfa-checkout-total-value"><?php echo wp_kses_post( $tax->formatted_amount ); ?></span>
				</div>
				<?php
			}
		} else {
			?>
			<div class="lfa-checkout-tax">
				<span class="lfa-checkout-total-label"><?php echo esc_html( WC()->countries->tax_or_vat() ) . $estimated_text; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></span>
				<span class="lfa-checkout-total-value"><?php wc_cart_totals_taxes_total_html(); ?></span>
			</div>
			<?php
		}
	}
	?>

	<div class="lfa-checkout-total">
		<span class="lfa-checkout-total-label"><?php esc_html_e( 'Total', 'woocommerce' ); ?>:</span>
		<span class="lfa-checkout-total-value"><?php wc_cart_totals_order_total_html(); ?></span>
	</div>
</div>
<?php endif; ?>

