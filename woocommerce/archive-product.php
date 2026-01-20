<?php
/**
 * The Template for displaying product archives, including the main shop page
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/archive-product.php.
 *
 * @see https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 3.4.0
 */

defined( 'ABSPATH' ) || exit;

get_header();

/**
 * Hook: woocommerce_before_main_content.
 *
 * @hooked woocommerce_output_content_wrapper - 10 (outputs opening divs for the content)
 * @hooked woocommerce_breadcrumb - 20
 */
do_action( 'woocommerce_before_main_content' );

?>
<header class="woocommerce-products-header">
	<?php
	/**
	 * Hook: woocommerce_archive_description.
	 *
	 * @hooked woocommerce_taxonomy_archive_description - 10
	 * @hooked woocommerce_product_archive_description - 10
	 */
	do_action( 'woocommerce_archive_description' );
	?>
</header>

<?php
if ( woocommerce_product_loop() ) {

	/**
	 * Hook: woocommerce_before_shop_loop.
	 *
	 * @hooked woocommerce_output_all_notices - 10
	 * @hooked woocommerce_result_count - 20
	 * @hooked woocommerce_catalog_ordering - 30
	 */
	// do_action( 'woocommerce_before_shop_loop' );

	?>
	<div class="woocommerce-products">
	<?php
	// woocommerce_product_loop_start() outputs <ul class="products">
	// Each product uses 'lfa-product' class from content-product.php
	// Global product styles from main.css apply automatically
	woocommerce_product_loop_start();

	if ( wc_get_loop_prop( 'is_shortcode' ) ) {
		$columns = absint( wc_get_loop_prop( 'columns' ) );
		$woocommerce_loop['columns'] = $columns;
	}

	while ( have_posts() ) {
		the_post();

		/**
		 * Hook: woocommerce_shop_loop.
		 */
		do_action( 'woocommerce_shop_loop' );

		wc_get_template_part( 'content', 'product' );
	}

	woocommerce_product_loop_end();
	?>
	</div>
	<?php

	/**
	 * Hook: woocommerce_after_shop_loop.
	 *
	 * @hooked woocommerce_pagination - 10
	 */
	do_action( 'woocommerce_after_shop_loop' );
} else {
	/**
	 * Hook: woocommerce_no_products_found.
	 *
	 * @hooked wc_no_products_found - 10
	 */
	do_action( 'woocommerce_no_products_found' );
}

/**
 * Hook: woocommerce_after_main_content.
 *
 * @hooked woocommerce_output_content_wrapper_end - 10 (outputs closing divs for the content)
 */
do_action( 'woocommerce_after_main_content' );

get_footer();
