<?php
/**
 * My Orders
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/myaccount/orders.php.
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

// Use wc_get_orders for better compatibility
$customer_orders = wc_get_orders( apply_filters( 'woocommerce_my_account_my_orders_query', array(
	'customer_id' => $customer_id,
	'status'      => array_keys( wc_get_order_statuses() ),
	'limit'       => -1,
	'orderby'     => 'date',
	'order'       => 'DESC',
) ) );

$has_orders = 0 < count( $customer_orders );

do_action( 'woocommerce_before_account_orders', $has_orders ); ?>

<div class="woocommerce-MyAccount-content">
	<h2 class="woocommerce-MyAccount-content-title">ORDER HISTORY</h2>
	
	<?php if ( $has_orders ) : ?>
		<table class="woocommerce-orders-table woocommerce-MyAccount-orders-table">
			<thead>
				<tr>
					<th class="woocommerce-orders-table__header-order">ORDER</th>
					<th class="woocommerce-orders-table__header-date">DATE</th>
					<th class="woocommerce-orders-table__header-status">STATUS</th>
					<th class="woocommerce-orders-table__header-total">TOTAL</th>
					<th class="woocommerce-orders-table__header-actions">ACTIONS</th>
				</tr>
			</thead>
			<tbody>
				<?php
				foreach ( $customer_orders as $order ) {
					if ( ! $order ) {
						continue;
					}
					$item_count = $order->get_item_count();
					$items      = $order->get_items();
					$first_item = reset( $items );
					$product    = $first_item ? wc_get_product( $first_item->get_product_id() ) : null;
					?>
					<tr class="woocommerce-orders-table__row">
						<td class="woocommerce-orders-table__cell-order" data-title="<?php esc_attr_e( 'Order', 'woocommerce' ); ?>">
							<?php if ( $product ) : ?>
								<div class="lfa-order-cell-content">
									<div class="lfa-order-image">
										<?php echo $product->get_image( 'woocommerce_thumbnail' ); ?>
									</div>
									<div class="lfa-order-info">
										<div class="lfa-order-title"><?php echo esc_html( $product->get_name() ); ?></div>
										<?php
										$variation_data = $first_item->get_meta_data();
										$attributes = array();
										foreach ( $variation_data as $meta ) {
											if ( strpos( $meta->key, 'pa_' ) === 0 || strpos( $meta->key, 'attribute_' ) === 0 ) {
												$attributes[] = $meta->value;
											}
										}
										if ( ! empty( $attributes ) ) {
											echo '<div class="lfa-order-attributes">' . esc_html( implode( '/', $attributes ) ) . '</div>';
										}
										?>
										<div class="lfa-order-number">#<?php echo esc_html( $order->get_order_number() ); ?></div>
									</div>
								</div>
							<?php else : ?>
								<div class="lfa-order-number">#<?php echo esc_html( $order->get_order_number() ); ?></div>
							<?php endif; ?>
						</td>
						<td class="woocommerce-orders-table__cell-date" data-title="<?php esc_attr_e( 'Date', 'woocommerce' ); ?>">
							<time datetime="<?php echo esc_attr( $order->get_date_created()->date( 'c' ) ); ?>">
								<?php echo esc_html( wc_format_datetime( $order->get_date_created() ) ); ?>
							</time>
						</td>
						<td class="woocommerce-orders-table__cell-status" data-title="<?php esc_attr_e( 'Status', 'woocommerce' ); ?>">
							<?php echo esc_html( wc_get_order_status_name( $order->get_status() ) ); ?>
						</td>
						<td class="woocommerce-orders-table__cell-total" data-title="<?php esc_attr_e( 'Total', 'woocommerce' ); ?>">
							<?php echo wp_kses_post( $order->get_formatted_order_total() ); ?>
						</td>
						<td class="woocommerce-orders-table__cell-actions" data-title="<?php esc_attr_e( 'Actions', 'woocommerce' ); ?>">
							<?php
							$actions = wc_get_account_orders_actions( $order );
							
							// Always show View button
							$view_url = wc_get_endpoint_url( 'view-order', $order->get_id(), wc_get_page_permalink( 'myaccount' ) );
							echo '<a href="' . esc_url( $view_url ) . '" class="lfa-order-action-button lfa-order-view-button">View</a>';
							
							// Always show Print button
							$print_url = wp_nonce_url( add_query_arg( array(
								'print_invoice' => $order->get_id(),
							), wc_get_page_permalink( 'myaccount' ) ), 'print-invoice' );
							// echo '<a href="' . esc_url( $print_url ) . '" class="lfa-order-action-button lfa-order-print-button" target="_blank">Print</a>';
							?>
						</td>
					</tr>
					<?php
				}
				?>
			</tbody>
		</table>
	<?php else : ?>
		<div class="woocommerce-message woocommerce-message--info woocommerce-Message woocommerce-Message--info woocommerce-info">
			<?php esc_html_e( 'No order has been made yet.', 'woocommerce' ); ?>
		</div>
	<?php endif; ?>
</div>

<?php do_action( 'woocommerce_after_account_orders', $has_orders ); ?>
