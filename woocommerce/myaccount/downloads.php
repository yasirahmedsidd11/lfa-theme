<?php
/**
 * My Downloads
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/myaccount/downloads.php.
 *
 * @see     https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 2.6.0
 */

defined( 'ABSPATH' ) || exit;

$downloads     = WC()->customer->get_downloadable_products();
$has_downloads = (bool) $downloads;

do_action( 'woocommerce_before_account_downloads', $has_downloads ); ?>

<div class="woocommerce-MyAccount-content">
	<h2 class="woocommerce-MyAccount-content-title" style="border:0; padding-inline:0;">DOWNLOADS</h2>
	
	<?php if ( $has_downloads ) : ?>
		<table class="woocommerce-table woocommerce-table--order-downloads shop_table shop_table_responsive order_details">
			<thead>
				<tr>
					<th class="download-product"><span class="nobr"><?php esc_html_e( 'Product', 'woocommerce' ); ?></span></th>
					<th class="download-remaining"><span class="nobr"><?php esc_html_e( 'Downloads remaining', 'woocommerce' ); ?></span></th>
					<th class="download-expires"><span class="nobr"><?php esc_html_e( 'Expires', 'woocommerce' ); ?></span></th>
					<th class="download-file"><span class="nobr"><?php esc_html_e( 'Download', 'woocommerce' ); ?></span></th>
				</tr>
			</thead>
			<?php foreach ( $downloads as $download ) : ?>
				<tr>
					<td class="download-product" data-title="<?php esc_attr_e( 'Product', 'woocommerce' ); ?>">
						<a href="<?php echo esc_url( get_permalink( $download['product_id'] ) ); ?>">
							<?php echo esc_html( $download['product_name'] ); ?>
						</a>
					</td>
					<td class="download-remaining" data-title="<?php esc_attr_e( 'Downloads remaining', 'woocommerce' ); ?>">
						<?php
						echo esc_html( $download['downloads_remaining'] );
						?>
					</td>
					<td class="download-expires" data-title="<?php esc_attr_e( 'Expires', 'woocommerce' ); ?>">
						<?php if ( ! empty( $download['access_expires'] ) ) : ?>
							<time datetime="<?php echo esc_attr( date( 'Y-m-d', strtotime( $download['access_expires'] ) ) ); ?>" title="<?php echo esc_attr( strtotime( $download['access_expires'] ) ); ?>">
								<?php echo esc_html( date_i18n( get_option( 'date_format' ), strtotime( $download['access_expires'] ) ) ); ?>
							</time>
						<?php else : ?>
							<?php esc_html_e( 'Never', 'woocommerce' ); ?>
						<?php endif; ?>
					</td>
					<td class="download-file" data-title="<?php esc_attr_e( 'Download', 'woocommerce' ); ?>">
						<?php
						$download_url = add_query_arg(
							array(
								'download_file' => $download['product_id'],
								'order'         => $download['order_key'],
								'email'         => urlencode( $download['user_email'] ),
								'key'           => $download['download_id'],
							),
							home_url( '/' )
						);
						?>
						<a href="<?php echo esc_url( $download_url ); ?>" class="woocommerce-MyAccount-downloads-file-button button alt">
							<?php esc_html_e( 'Download', 'woocommerce' ); ?>
						</a>
					</td>
				</tr>
			<?php endforeach; ?>
		</table>
	<?php else : ?>
		<div class="woocommerce-message woocommerce-message--info woocommerce-Message woocommerce-Message--info woocommerce-info">
			<?php esc_html_e( 'No downloadable products found.', 'woocommerce' ); ?>
		</div>
	<?php endif; ?>
</div>

<?php do_action( 'woocommerce_after_account_downloads', $has_downloads ); ?>
