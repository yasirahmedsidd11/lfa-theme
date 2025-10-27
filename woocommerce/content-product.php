<?php
defined('ABSPATH') || exit;
global $product;
$card = lfa_get_option('shop_card_style', 'card');
?>
<li <?php wc_product_class('lfa-product card-' . $card, $product); ?>>
  <a href="<?php the_permalink(); ?>" class="thumb">
    <?php 
    // Get the product image without cropping
    $image_id = $product->get_image_id();
    if ($image_id) {
      echo wp_get_attachment_image($image_id, 'woocommerce_single', false, array('class' => 'attachment-woocommerce_single size-woocommerce_single'));
    } else {
      echo woocommerce_get_product_thumbnail('woocommerce_single');
    }
    ?>
    <?php wc_get_template('loop/sale-flash.php'); ?>
  </a>
  <!-- Product Tags Start -->
  <div class="lfa-product-tags">
    <!-- new tag start -->
    <span class="lfa-product-tag tag-new"><span class="bullet">&bull;</span> New</span>
    <!-- new tag end -->
  </div>
  <!-- Product Tags End -->
  <!-- product meta start -->
  <div class="lfa-product-meta">
    <div class="lfa-product-meta-name-price">
      <h3 class="title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
      <?php woocommerce_template_loop_price(); ?>
    </div>
    <div>
      <!-- Product  variations start -->
      <?php
      // Get product color attribute
      $color_terms = wp_get_post_terms($product->get_id(), 'pa_color', array('fields' => 'names'));
      $color_count = count($color_terms);

      if ($color_count > 0) {
        echo '<div class="lfa-color-count">' . $color_count . ' colour' . ($color_count > 1 ? 's' : '') . '</div>';
      }
      ?>
      <!-- Product  variations end -->
    </div>
  </div>
  <!-- product meta end -->

  <!-- hide the select options buttons -->
  <?php // if (! lfa_get_option('catalog_mode')) woocommerce_template_loop_add_to_cart(); 
  ?>
  <!-- hide the select options buttons end -->
</li>