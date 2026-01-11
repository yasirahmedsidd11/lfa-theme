<?php
/**
 * View Order
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/myaccount/view-order.php.
 *
 * @see     https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 2.6.0
 */

defined( 'ABSPATH' ) || exit;

$notes = $order->get_customer_order_notes();
?>

<div class="woocommerce-MyAccount-content">
	<h2 class="woocommerce-MyAccount-content-title">ORDER DETAILS</h2>
	
	<div class="lfa-order-details-section">
		<div class="lfa-order-details-row">
			<div class="lfa-order-details-label">ORDER:</div>
			<div class="lfa-order-details-value"><?php echo esc_html( $order->get_order_number() ); ?></div>
		</div>
		
		<?php
		$items = $order->get_items();
		foreach ( $items as $item_id => $item ) {
			$product = $item->get_product();
			if ( $product ) {
				?>
				<div class="lfa-order-details-row">
					<div class="lfa-order-details-label">PRODUCT:</div>
					<div class="lfa-order-details-value"><?php echo esc_html( $product->get_name() ); ?></div>
				</div>
				<?php
				break; // Only show first product
			}
		}
		?>
		
		<div class="lfa-order-details-row">
			<div class="lfa-order-details-label">DATE:</div>
			<div class="lfa-order-details-value">
				<time datetime="<?php echo esc_attr( $order->get_date_created()->date( 'c' ) ); ?>">
					<?php echo esc_html( wc_format_datetime( $order->get_date_created() ) ); ?>
				</time>
			</div>
		</div>
		
		<div class="lfa-order-details-row">
			<div class="lfa-order-details-label">GIFT VOUCHER:</div>
			<div class="lfa-order-details-value">-</div>
		</div>
		
		<div class="lfa-order-details-row">
			<div class="lfa-order-details-label">SUB TOTAL:</div>
			<div class="lfa-order-details-value"><?php echo wp_kses_post( $order->get_subtotal_to_display() ); ?></div>
		</div>
		
		<div class="lfa-order-details-row">
			<div class="lfa-order-details-label">TOTAL:</div>
			<div class="lfa-order-details-value"><?php echo wp_kses_post( $order->get_formatted_order_total() ); ?></div>
		</div>
		
		<div class="lfa-order-details-row lfa-order-actions-row">
			<div class="lfa-order-details-label">ACTIONS:</div>
			<div class="lfa-order-details-value">
				<a href="<?php echo esc_url( wp_nonce_url( add_query_arg( 'print_invoice', $order->get_id(), wc_get_page_permalink( 'myaccount' ) ), 'print-invoice' ) ); ?>" class="lfa-order-print-button" target="_blank">Print</a>
			</div>
		</div>
	</div>
	
	<h2 class="lfa-billing-address-title">BILLING ADDRESS</h2>
	<div class="lfa-billing-address-section">
		<address>
			<?php echo wp_kses_post( $order->get_formatted_billing_address( esc_html__( 'N/A', 'woocommerce' ) ) ); ?>
			<?php if ( $order->get_billing_phone() ) : ?>
				<br><?php echo esc_html( $order->get_billing_phone() ); ?>
			<?php endif; ?>
			<?php if ( $order->get_billing_email() ) : ?>
				<br><?php echo esc_html( $order->get_billing_email() ); ?>
			<?php endif; ?>
		</address>
	</div>
</div>
