<?php
defined('ABSPATH') || exit;
global $product;
$card = lfa_get_option('shop_card_style', 'card');
?>
<li <?php wc_product_class('lfa-product card-'.$card, $product); ?>>
  <a href="<?php the_permalink(); ?>" class="thumb">
    <?php echo woocommerce_get_product_thumbnail('woocommerce_thumbnail'); ?>
    <?php wc_get_template('loop/sale-flash.php'); ?>
  </a>
  <h3 class="title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
  <?php woocommerce_template_loop_price(); ?>
  <?php if ( ! lfa_get_option('catalog_mode') ) woocommerce_template_loop_add_to_cart(); ?>
</li>
