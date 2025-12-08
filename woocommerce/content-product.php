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
    $product_image_id = 'lfa-product-image-' . $product->get_id();
    if ($image_id) {
      $default_image_url = wp_get_attachment_image_url($image_id, 'woocommerce_single');
      echo wp_get_attachment_image($image_id, 'woocommerce_single', false, array('class' => 'attachment-woocommerce_single size-woocommerce_single lfa-product-main-image', 'id' => $product_image_id, 'data-default-image' => $default_image_url));
    } else {
      $thumbnail = woocommerce_get_product_thumbnail('woocommerce_single');
      // Wrap thumbnail in span with ID if it doesn't have one
      if (strpos($thumbnail, 'id=') === false) {
        echo '<span id="' . esc_attr($product_image_id) . '" class="lfa-product-main-image">' . $thumbnail . '</span>';
      } else {
        echo $thumbnail;
      }
    }
    ?>
    <?php if (lfa_get_option('quick_view')): ?>
    <button type="button" class="lfa-quick-view-btn" data-product-id="<?php echo esc_attr($product->get_id()); ?>" aria-label="<?php esc_attr_e('Quick view', 'livingfitapparel'); ?>">
      <?php esc_html_e('QUICK VIEW', 'livingfitapparel'); ?>
    </button>
    <?php endif; ?>
    
    <?php if (lfa_get_option('wishlist')): ?>
    <div class="lfa-product-wishlist">
      <?php echo do_shortcode('[ti_wishlists_addtowishlist]'); ?>
    </div>
    <?php endif; ?>
    
  </a>
  <!-- Product Tags Start -->
  <?php 
  $show_new_tag = lfa_get_option('show_new_tag');
  $show_sale_tag = lfa_get_option('show_sale_tag');
  $show_sale_percentage = lfa_get_option('show_sale_percentage');
  $is_on_sale = $product->is_on_sale();
  $sale_badge_text = lfa_get_option('sale_badge_text', 'Sale');
  
  // Calculate discount percentage if needed
  $sale_display_text = $sale_badge_text;
  if ($show_sale_percentage && $is_on_sale) {
    if ($product->is_type('variable')) {
      // For variable products, get the price range and calculate max discount
      $variation_prices = $product->get_variation_prices(true);
      if (!empty($variation_prices['regular_price']) && !empty($variation_prices['price'])) {
        $max_regular = max($variation_prices['regular_price']);
        $min_sale = min($variation_prices['price']);
        if ($max_regular > 0 && $min_sale < $max_regular) {
          $percentage = round((($max_regular - $min_sale) / $max_regular) * 100);
          $sale_display_text = $percentage . '% OFF';
        }
      }
    } else {
      // For simple products
      $regular_price = floatval($product->get_regular_price());
      $sale_price = floatval($product->get_sale_price());
      if ($regular_price > 0 && $sale_price > 0 && $sale_price < $regular_price) {
        $percentage = round((($regular_price - $sale_price) / $regular_price) * 100);
        $sale_display_text = $percentage . '% OFF';
      }
    }
  }
  
  if ($show_new_tag || ($show_sale_tag && $is_on_sale)): ?>
  <div class="lfa-product-tags">
    <?php if ($show_new_tag): ?>
    <!-- new tag start -->
    <span class="lfa-product-tag tag-new"><span class="bullet">&bull;</span> New</span>
    <!-- new tag end -->
    <?php endif; ?>
    
    <?php if ($show_sale_tag && $is_on_sale): ?>
    <!-- sale tag start -->
    <span class="lfa-product-tag tag-sale"><span class="bullet">&bull;</span> <?php echo esc_html($sale_display_text); ?></span>
    <!-- sale tag end -->
    <?php endif; ?>
  </div>
  <!-- Product Tags End -->
  <?php endif; ?>
  <!-- product meta start -->
  <div class="lfa-product-meta">
    <div class="lfa-product-meta-name-price">
      <h3 class="title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
      <?php woocommerce_template_loop_price(); ?>
    </div>
    <div>
      <!-- Product  variations start -->
      <?php
      // Only show colors if option is enabled
      // Check if option exists in saved options (not just default)
      $saved_opts = get_option('lfa_options', []);
      $show_colors = !isset($saved_opts['show_colors']) ? true : !empty($saved_opts['show_colors']);
      if ($show_colors) {
        // Get product color attribute with full term objects
        $color_terms = wp_get_post_terms($product->get_id(), 'pa_color', array('fields' => 'all'));
        $color_count = count($color_terms);

        if ($color_count > 0) {
        // Wrap color count and swatches in a container for overlapping
        echo '<div class="lfa-color-wrapper">';
        echo '<div class="lfa-color-count">' . $color_count . ' colour' . ($color_count > 1 ? 's' : '') . '</div>';
        
        // Get product variations if variable product
        $variations_data = array();
        if ($product->is_type('variable')) {
          $variation_ids = $product->get_children();
          foreach ($variation_ids as $variation_id) {
            $variation_obj = wc_get_product($variation_id);
            if ($variation_obj && $variation_obj->is_purchasable()) {
              // Try multiple ways to get the color attribute
              $variation_attributes = $variation_obj->get_attributes();
              $color_slug = '';
              
              // Check different attribute key formats
              if (isset($variation_attributes['pa_color'])) {
                $color_slug = $variation_attributes['pa_color'];
              } elseif (isset($variation_attributes['attribute_pa_color'])) {
                $color_slug = $variation_attributes['attribute_pa_color'];
              } else {
                // Check in variation data array
                $variation_data = $variation_obj->get_data();
                if (isset($variation_data['attributes']['pa_color'])) {
                  $color_slug = $variation_data['attributes']['pa_color'];
                } elseif (isset($variation_data['attributes']['attribute_pa_color'])) {
                  $color_slug = $variation_data['attributes']['attribute_pa_color'];
                }
              }
              
              if ($color_slug) {
                $variation_image_id = $variation_obj->get_image_id();
                // Use variation image if available, otherwise skip (don't use parent image as fallback here)
                if ($variation_image_id) {
                  $variation_image_url = wp_get_attachment_image_url($variation_image_id, 'woocommerce_single');
                  $variation_image_srcset = wp_get_attachment_image_srcset($variation_image_id, 'woocommerce_single');
                  $variation_image_sizes = wp_get_attachment_image_sizes($variation_image_id, 'woocommerce_single');
                  $image_meta = wp_get_attachment_metadata($variation_image_id);
                  $variation_image_width = isset($image_meta['width']) ? $image_meta['width'] : '';
                  $variation_image_height = isset($image_meta['height']) ? $image_meta['height'] : '';
                  
                  // Store by slug, but also allow lookup by term ID
                  $variations_data[$color_slug] = array(
                    'image_id' => $variation_image_id,
                    'image_url' => $variation_image_url,
                    'image_srcset' => $variation_image_srcset,
                    'image_sizes' => $variation_image_sizes,
                    'image_width' => $variation_image_width,
                    'image_height' => $variation_image_height,
                    'variation_id' => $variation_id
                  );
                }
              }
            }
          }
        }
        
        // Display color swatches
        if (!empty($color_terms)) {
          echo '<div class="lfa-color-swatches-wrapper">';
          echo '<button type="button" class="lfa-color-swatches-nav lfa-color-swatches-prev" aria-label="' . esc_attr__('Previous colors', 'livingfitapparel') . '" style="display: none;"><span>&larr;</span></button>';
          echo '<div class="lfa-color-swatches" data-product-id="' . esc_attr($product->get_id()) . '">';
          foreach ($color_terms as $term) {
            $color_slug = $term->slug;
            $color_name = $term->name;
            
            // Try to get color hex code from term meta (common meta keys used by color swatch plugins)
            $color_hex = get_term_meta($term->term_id, 'color', true);
            if (empty($color_hex)) {
              $color_hex = get_term_meta($term->term_id, 'product_attribute_color', true);
            }
            if (empty($color_hex)) {
              $color_hex = get_term_meta($term->term_id, 'pa_color', true);
            }
            
            // Fallback: try to get color from name or use a default
            if (empty($color_hex)) {
              $color_hex = lfa_get_color_hex_from_name($color_name);
            }
            
            // Get variation image for this color
            $variation_image_url = '';
            $variation_image_id = '';
            $variation_image_srcset = '';
            $variation_image_sizes = '';
            $variation_image_width = '';
            $variation_image_height = '';
            
            // Try to find variation image by slug
            if (isset($variations_data[$color_slug])) {
              $variation_image_url = $variations_data[$color_slug]['image_url'];
              $variation_image_id = $variations_data[$color_slug]['image_id'];
              $variation_image_srcset = isset($variations_data[$color_slug]['image_srcset']) ? $variations_data[$color_slug]['image_srcset'] : '';
              $variation_image_sizes = isset($variations_data[$color_slug]['image_sizes']) ? $variations_data[$color_slug]['image_sizes'] : '';
              $variation_image_width = isset($variations_data[$color_slug]['image_width']) ? $variations_data[$color_slug]['image_width'] : '';
              $variation_image_height = isset($variations_data[$color_slug]['image_height']) ? $variations_data[$color_slug]['image_height'] : '';
            } else {
              // Try to find by term name (slugified) as fallback
              $slugified_name = sanitize_title($color_name);
              if (isset($variations_data[$slugified_name])) {
                $variation_image_url = $variations_data[$slugified_name]['image_url'];
                $variation_image_id = $variations_data[$slugified_name]['image_id'];
                $variation_image_srcset = isset($variations_data[$slugified_name]['image_srcset']) ? $variations_data[$slugified_name]['image_srcset'] : '';
                $variation_image_sizes = isset($variations_data[$slugified_name]['image_sizes']) ? $variations_data[$slugified_name]['image_sizes'] : '';
                $variation_image_width = isset($variations_data[$slugified_name]['image_width']) ? $variations_data[$slugified_name]['image_width'] : '';
                $variation_image_height = isset($variations_data[$slugified_name]['image_height']) ? $variations_data[$slugified_name]['image_height'] : '';
              } else {
                // Fallback to main product image if no variation image found
                $main_image_id = $product->get_image_id();
                if ($main_image_id) {
                  $variation_image_url = wp_get_attachment_image_url($main_image_id, 'woocommerce_single');
                  $variation_image_id = $main_image_id;
                  $variation_image_srcset = wp_get_attachment_image_srcset($main_image_id, 'woocommerce_single');
                  $variation_image_sizes = wp_get_attachment_image_sizes($main_image_id, 'woocommerce_single');
                  $image_meta = wp_get_attachment_metadata($main_image_id);
                  $variation_image_width = isset($image_meta['width']) ? $image_meta['width'] : '';
                  $variation_image_height = isset($image_meta['height']) ? $image_meta['height'] : '';
                }
              }
            }
            
            // Build swatch HTML
            $swatch_class = 'lfa-color-swatch';
            if (empty($variation_image_url)) {
              $swatch_class .= ' lfa-color-swatch-no-image';
            }
            
            echo '<button type="button" class="' . esc_attr($swatch_class) . '" ';
            echo 'data-color-slug="' . esc_attr($color_slug) . '" ';
            echo 'data-color-name="' . esc_attr($color_name) . '" ';
            if ($variation_image_url) {
              echo 'data-image-url="' . esc_url($variation_image_url) . '" ';
              echo 'data-image-id="' . esc_attr($variation_image_id) . '" ';
              if ($variation_image_srcset) {
                echo 'data-image-srcset="' . esc_attr($variation_image_srcset) . '" ';
              }
              if ($variation_image_sizes) {
                echo 'data-image-sizes="' . esc_attr($variation_image_sizes) . '" ';
              }
              if ($variation_image_width) {
                echo 'data-image-width="' . esc_attr($variation_image_width) . '" ';
              }
              if ($variation_image_height) {
                echo 'data-image-height="' . esc_attr($variation_image_height) . '" ';
              }
            }
            echo 'aria-label="' . esc_attr(sprintf(__('Select %s color', 'livingfitapparel'), $color_name)) . '" ';
            echo 'style="background-color: ' . esc_attr($color_hex ? $color_hex : '#ccc') . ';"';
            echo '></button>';
          }
          echo '</div>'; // Close lfa-color-swatches
          echo '<button type="button" class="lfa-color-swatches-nav lfa-color-swatches-next" aria-label="' . esc_attr__('Next colors', 'livingfitapparel') . '" style="display: none;"><span>&rarr;</span></button>';
          echo '</div>'; // Close lfa-color-swatches-wrapper
        }
        echo '</div>'; // Close lfa-color-wrapper
      }
      } // End show_colors check
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