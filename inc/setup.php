<?php
if ( ! defined( 'ABSPATH' ) ) exit;

add_action('after_setup_theme', function () {
  add_theme_support('title-tag');
  add_theme_support('post-thumbnails');
  add_theme_support('automatic-feed-links');
  add_theme_support('responsive-embeds');
  add_theme_support('wp-block-styles');
  add_theme_support('editor-styles');
  add_editor_style('assets/css/main.css');

  // WooCommerce
  add_theme_support('woocommerce', [
    'thumbnail_image_width' => 420,
    'single_image_width'    => 720,
    'product_grid'          => [
      'default_rows' => 3, 'min_rows' => 1, 'max_rows' => 10,
      'default_columns' => 4, 'min_columns' => 2, 'max_columns' => 6
    ]
  ]);
  add_theme_support('wc-product-gallery-zoom');
  add_theme_support('wc-product-gallery-lightbox');
  add_theme_support('wc-product-gallery-slider');

  register_nav_menus([
    'primary' => __('Primary Menu', 'livingfitapparel'),
    'footer'  => __('Footer Menu', 'livingfitapparel'),
  ]);

  register_sidebar([
    'name' => __('Shop Sidebar', 'livingfitapparel'),
    'id'   => 'shop-sidebar',
    'before_widget' => '<section id="%1$s" class="widget %2$s">',
    'after_widget'  => '</section>',
    'before_title'  => '<h3 class="widget-title">',
    'after_title'   => '</h3>',
  ]);
});

// Add data attribute to menu link when menu item has CSS class 'mega-shop'
add_filter('nav_menu_link_attributes', function($atts, $item, $args){
  if (!empty($item->classes) && is_array($item->classes) && in_array('mega-shop', $item->classes, true)) {
    $atts['data-mega'] = 'shop';
    $atts['aria-haspopup'] = 'true';
    $atts['aria-expanded'] = 'false';
  }
  return $atts;
}, 10, 3);

// Replace WooCommerce default column classes with custom grid classes (3 columns)
add_filter('woocommerce_product_loop_start', function($html) {
  // Replace column classes with 3-column grid
  $html = str_replace('columns-4', 'lfa-grid lfa-grid-3', $html);
  $html = str_replace('columns-3', 'lfa-grid lfa-grid-3', $html);
  $html = str_replace('column-4', 'lfa-grid lfa-grid-3', $html);
  $html = str_replace('column-3', 'lfa-grid lfa-grid-3', $html);
  return $html;
});

// Set default products per row to 3
add_filter('loop_shop_columns', function() {
  return 3;
}, 99);

// WooCommerce content wrapper
if (class_exists('WooCommerce')) {
  // Remove default WooCommerce wrappers
  remove_action('woocommerce_before_main_content', 'woocommerce_output_content_wrapper', 10);
  remove_action('woocommerce_after_main_content', 'woocommerce_output_content_wrapper_end', 10);
  remove_action('woocommerce_sidebar', 'woocommerce_get_sidebar', 10);
  // Remove breadcrumbs
  remove_action('woocommerce_before_main_content', 'woocommerce_breadcrumb', 20);

  // Add custom content wrapper with sidebar layout
  add_action('woocommerce_before_main_content', function() {
    echo '<div class="container"><div class="woocommerce-shop-wrapper">';
    // Sidebar on the left
    echo '<div class="woocommerce-sidebar-wrapper">';
    get_template_part('woocommerce/sidebar');
    echo '</div>';
    // Content on the right
    echo '<div class="woocommerce-content-wrapper">';
  }, 10);

  add_action('woocommerce_after_main_content', function() {
    echo '</div>'; // Close woocommerce-content-wrapper
    echo '</div>'; // Close woocommerce-shop-wrapper
    echo '</div>'; // Close container
  }, 10);

  // Force WordPress to use our archive-product template for shop and category pages
  // Use multiple hooks to ensure it works
  add_filter('template_include', function($template) {
    // Only process if WooCommerce is active
    if (!class_exists('WooCommerce')) {
      return $template;
    }
    
    // Get the full path to check
    $template_file = basename($template);
    
    // Check if we're on a WooCommerce shop/archive page
    if (is_shop() || is_product_category() || is_product_tag() || is_product_taxonomy()) {
      $custom_template = get_template_directory() . '/woocommerce/archive-product.php';
      // Verify file exists before using it
      if (file_exists($custom_template)) {
        return $custom_template;
      }
    }
    
    // Also check if we're on the shop page (when it's a WordPress page)
    if (is_page() && function_exists('wc_get_page_id')) {
      $shop_page_id = wc_get_page_id('shop');
      if ($shop_page_id && is_page($shop_page_id)) {
        $custom_template = get_template_directory() . '/woocommerce/archive-product.php';
        if (file_exists($custom_template)) {
          return $custom_template;
        }
      }
    }
    
    return $template;
  }, 999); // Very high priority to run last

  // Also ensure WooCommerce's template loader finds our template
  add_filter('woocommerce_locate_template', function($template, $template_name, $template_path) {
    // For archive-product.php, use our custom template
    if ($template_name === 'archive-product.php') {
      $custom_template = get_template_directory() . '/woocommerce/archive-product.php';
      if (file_exists($custom_template)) {
        return $custom_template;
      }
    }
    return $template;
  }, 10, 3);
}

// Apply filters from URL parameters on initial page load
add_action('woocommerce_product_query', 'lfa_apply_url_filters_to_query');

function lfa_apply_url_filters_to_query($query) {
  // Only apply on shop/archive pages
  if (!is_shop() && !is_product_category() && !is_product_tag() && !is_product_taxonomy()) {
    return;
  }
  
  // Check if URL has filter parameters
  if (!isset($_GET['orderby']) && !isset($_GET['categories']) && !isset($_GET['colors']) && !isset($_GET['sizes'])) {
    return;
  }
  
  // Get existing tax_query from the query
  $tax_query = $query->get('tax_query');
  
  // If tax_query is not set or not an array, get it from WooCommerce
  if (!is_array($tax_query)) {
    $tax_query = WC()->query->get_tax_query();
  }
  
  // Ensure it's an array
  if (!is_array($tax_query)) {
    $tax_query = array();
  }
  
  // Extract relation if it exists
  $relation = 'AND';
  if (isset($tax_query['relation'])) {
    $relation = $tax_query['relation'];
    unset($tax_query['relation']);
  }
  
  // Filter out empty arrays and non-array items, but keep valid tax_query structures
  $tax_query = array_filter($tax_query, function($item) {
    return is_array($item) && !empty($item) && isset($item['taxonomy']);
  });
  
  // Re-index array to ensure numeric keys
  $tax_query = array_values($tax_query);
  
  // Add category filter from URL (only if not on a category page, or if different categories are specified)
  if (isset($_GET['categories']) && !empty($_GET['categories'])) {
    $category_ids = array_map('intval', explode(',', sanitize_text_field($_GET['categories'])));
    $category_ids = array_filter($category_ids);
    if (!empty($category_ids)) {
      // Remove any existing product_cat filters
      $tax_query = array_filter($tax_query, function($item) {
        return !isset($item['taxonomy']) || $item['taxonomy'] !== 'product_cat';
      });
      $tax_query = array_values($tax_query);
      
      // Add new category filter
      $tax_query[] = array(
        'taxonomy' => 'product_cat',
        'field' => 'term_id',
        'terms' => $category_ids,
        'operator' => 'IN',
      );
      $has_filters = true;
    }
  }
  
  // Add color filter from URL
  if (isset($_GET['colors']) && !empty($_GET['colors'])) {
    $color_ids = array_map('intval', explode(',', sanitize_text_field($_GET['colors'])));
    $color_ids = array_filter($color_ids);
    if (!empty($color_ids)) {
      // Remove any existing pa_color filters
      $tax_query = array_filter($tax_query, function($item) {
        return !isset($item['taxonomy']) || $item['taxonomy'] !== 'pa_color';
      });
      $tax_query = array_values($tax_query);
      
      $tax_query[] = array(
        'taxonomy' => 'pa_color',
        'field' => 'term_id',
        'terms' => $color_ids,
        'operator' => 'IN',
      );
      $has_filters = true;
    }
  }
  
  // Add size filter from URL
  if (isset($_GET['sizes']) && !empty($_GET['sizes'])) {
    $size_ids = array_map('intval', explode(',', sanitize_text_field($_GET['sizes'])));
    $size_ids = array_filter($size_ids);
    if (!empty($size_ids)) {
      // Remove any existing pa_size filters
      $tax_query = array_filter($tax_query, function($item) {
        return !isset($item['taxonomy']) || $item['taxonomy'] !== 'pa_size';
      });
      $tax_query = array_values($tax_query);
      
      $tax_query[] = array(
        'taxonomy' => 'pa_size',
        'field' => 'term_id',
        'terms' => $size_ids,
        'operator' => 'IN',
      );
      $has_filters = true;
    }
  }
  
  // Handle ordering from URL
  $orderby = isset($_GET['orderby']) ? sanitize_text_field($_GET['orderby']) : '';
  switch ($orderby) {
    case 'featured':
      // Remove any existing product_visibility filters
      $tax_query = array_filter($tax_query, function($item) {
        return !isset($item['taxonomy']) || $item['taxonomy'] !== 'product_visibility';
      });
      $tax_query = array_values($tax_query);
      
      $tax_query[] = array(
        'taxonomy' => 'product_visibility',
        'field' => 'name',
        'terms' => 'featured',
        'operator' => 'IN',
      );
      $has_filters = true;
      $query->set('orderby', 'menu_order title');
      $query->set('order', 'ASC');
      break;
    case 'price':
      $query->set('meta_key', '_price');
      $query->set('orderby', 'meta_value_num');
      $query->set('order', 'ASC');
      break;
    case 'price-desc':
      $query->set('meta_key', '_price');
      $query->set('orderby', 'meta_value_num');
      $query->set('order', 'DESC');
      break;
    case 'alphabetically-asc':
      $query->set('orderby', 'title');
      $query->set('order', 'ASC');
      break;
    case 'alphabetically-desc':
      $query->set('orderby', 'title');
      $query->set('order', 'DESC');
      break;
    case 'new-in-oldest':
      $query->set('orderby', 'date');
      $query->set('order', 'ASC');
      break;
    case 'new-in':
      $query->set('orderby', 'date');
      $query->set('order', 'DESC');
      break;
    case 'most-viewed':
      $query->set('meta_key', 'post_views_count');
      $query->set('orderby', 'meta_value_num');
      $query->set('order', 'DESC');
      break;
    case 'best-selling':
      $query->set('meta_key', 'total_sales');
      $query->set('orderby', 'meta_value_num');
      $query->set('order', 'DESC');
      break;
  }
  
  // Set tax_query if we have any filters
  if ($has_filters && !empty($tax_query)) {
    // Always set relation if we have multiple conditions
    if (count($tax_query) > 1) {
      $tax_query['relation'] = $relation;
    }
    $query->set('tax_query', $tax_query);
  }
  
  // Debug: Log query vars (remove in production)
  // error_log('LFA Query Vars: ' . print_r($query->get('tax_query'), true));
  // error_log('LFA Has Categories: ' . (isset($_GET['categories']) ? 'yes' : 'no'));
  // error_log('LFA Category IDs: ' . (isset($_GET['categories']) ? $_GET['categories'] : 'none'));
}

// AJAX handler for shop filters
add_action('wp_ajax_lfa_filter_products', 'lfa_ajax_filter_products');
add_action('wp_ajax_nopriv_lfa_filter_products', 'lfa_ajax_filter_products');

function lfa_ajax_filter_products() {
  check_ajax_referer('lfa-nonce', 'nonce');
  
  // Get filter parameters
  $orderby = isset($_POST['orderby']) ? sanitize_text_field($_POST['orderby']) : '';
  
  // Parse JSON arrays from FormData
  $categories = array();
  if (isset($_POST['categories']) && !empty($_POST['categories'])) {
    $cats = json_decode(stripslashes($_POST['categories']), true);
    $categories = is_array($cats) ? array_map('intval', $cats) : array();
  }
  
  $colors = array();
  if (isset($_POST['colors']) && !empty($_POST['colors'])) {
    $cols = json_decode(stripslashes($_POST['colors']), true);
    $colors = is_array($cols) ? array_map('intval', $cols) : array();
  }
  
  $sizes = array();
  if (isset($_POST['sizes']) && !empty($_POST['sizes'])) {
    $sizs = json_decode(stripslashes($_POST['sizes']), true);
    $sizes = is_array($sizs) ? array_map('intval', $sizs) : array();
  }
  
  $paged = isset($_POST['paged']) ? max(1, intval($_POST['paged'])) : 1;
  
  // Build query args
  // Use WooCommerce's products per page setting (default is 12)
  // This respects any custom settings via the loop_shop_per_page filter
  $products_per_page = absint(apply_filters('loop_shop_per_page', get_option('posts_per_page', 12)));
  
  $args = array(
    'post_type' => 'product',
    'post_status' => 'publish',
    'posts_per_page' => $products_per_page,
    'paged' => $paged,
    'meta_query' => WC()->query->get_meta_query(),
    'tax_query' => WC()->query->get_tax_query(),
  );
  
  // Initialize tax_query array
  if (!is_array($args['tax_query'])) {
    $args['tax_query'] = array();
  }
  
  // Extract relation if it exists
  $relation = 'AND';
  if (isset($args['tax_query']['relation'])) {
    $relation = $args['tax_query']['relation'];
    unset($args['tax_query']['relation']);
  }
  
  // Filter out empty arrays and non-array items, but keep valid tax_query structures
  $args['tax_query'] = array_filter($args['tax_query'], function($item) {
    return is_array($item) && !empty($item) && isset($item['taxonomy']);
  });
  $args['tax_query'] = array_values($args['tax_query']);
  
  // Add category filter
  if (!empty($categories)) {
    // Remove any existing product_cat filters
    $args['tax_query'] = array_filter($args['tax_query'], function($item) {
      return !isset($item['taxonomy']) || $item['taxonomy'] !== 'product_cat';
    });
    $args['tax_query'] = array_values($args['tax_query']);
    
    $args['tax_query'][] = array(
      'taxonomy' => 'product_cat',
      'field' => 'term_id',
      'terms' => $categories,
      'operator' => 'IN',
    );
  }
  
  // Add color filter
  if (!empty($colors)) {
    // Remove any existing pa_color filters
    $args['tax_query'] = array_filter($args['tax_query'], function($item) {
      return !isset($item['taxonomy']) || $item['taxonomy'] !== 'pa_color';
    });
    $args['tax_query'] = array_values($args['tax_query']);
    
    $args['tax_query'][] = array(
      'taxonomy' => 'pa_color',
      'field' => 'term_id',
      'terms' => $colors,
      'operator' => 'IN',
    );
  }
  
  // Add size filter
  if (!empty($sizes)) {
    // Remove any existing pa_size filters
    $args['tax_query'] = array_filter($args['tax_query'], function($item) {
      return !isset($item['taxonomy']) || $item['taxonomy'] !== 'pa_size';
    });
    $args['tax_query'] = array_values($args['tax_query']);
    
    $args['tax_query'][] = array(
      'taxonomy' => 'pa_size',
      'field' => 'term_id',
      'terms' => $sizes,
      'operator' => 'IN',
    );
  }
  
  // Add relation back if we have multiple conditions
  if (count($args['tax_query']) > 1) {
    $args['tax_query']['relation'] = $relation;
  }
  
  // Handle ordering
  switch ($orderby) {
    case 'featured':
      // Remove any existing product_visibility filters
      $args['tax_query'] = array_filter($args['tax_query'], function($item) {
        return !isset($item['taxonomy']) || $item['taxonomy'] !== 'product_visibility';
      });
      $args['tax_query'] = array_values($args['tax_query']);
      
      // Featured products - add to tax_query
      $args['tax_query'][] = array(
        'taxonomy' => 'product_visibility',
        'field' => 'name',
        'terms' => 'featured',
        'operator' => 'IN',
      );
      
      $args['orderby'] = 'menu_order title';
      $args['order'] = 'ASC';
      
      // Re-add relation if needed
      if (count($args['tax_query']) > 1 && !isset($args['tax_query']['relation'])) {
        $args['tax_query']['relation'] = $relation;
      }
      break;
    case 'price':
      $args['meta_key'] = '_price';
      $args['orderby'] = 'meta_value_num';
      $args['order'] = 'ASC';
      break;
    case 'price-desc':
      $args['meta_key'] = '_price';
      $args['orderby'] = 'meta_value_num';
      $args['order'] = 'DESC';
      break;
    case 'alphabetically-asc':
      $args['orderby'] = 'title';
      $args['order'] = 'ASC';
      break;
    case 'alphabetically-desc':
      $args['orderby'] = 'title';
      $args['order'] = 'DESC';
      break;
    case 'new-in-oldest':
      $args['orderby'] = 'date';
      $args['order'] = 'ASC';
      break;
    case 'new-in':
      $args['orderby'] = 'date';
      $args['order'] = 'DESC';
      break;
    case 'most-viewed':
      // Use post views meta if available (common meta keys: post_views_count, _wc_product_views, _product_views)
      // Try multiple possible meta keys
      $view_meta_keys = array('post_views_count', '_wc_product_views', '_product_views', 'views');
      $args['meta_key'] = $view_meta_keys[0]; // Use first as primary
      $args['orderby'] = 'meta_value_num';
      $args['order'] = 'DESC';
      // If no meta exists, fallback to date
      $args['meta_compare'] = 'EXISTS';
      break;
    case 'best-selling':
      $args['meta_key'] = 'total_sales';
      $args['orderby'] = 'meta_value_num';
      $args['order'] = 'DESC';
      break;
    default:
      $args['orderby'] = 'menu_order title';
      $args['order'] = 'ASC';
  }
  
  // Execute query
  $products = new WP_Query($args);
  
  // Start output buffering
  ob_start();
  
  if ($products->have_posts()) {
    woocommerce_product_loop_start();
    
    while ($products->have_posts()) {
      $products->the_post();
      do_action('woocommerce_shop_loop');
      wc_get_template_part('content', 'product');
    }
    
    woocommerce_product_loop_end();
    $products_html = ob_get_clean();
    
    // Get pagination
    ob_start();
    woocommerce_pagination();
    $pagination_html = ob_get_clean();
    
    wp_send_json_success(array(
      'products' => $products_html,
      'pagination' => $pagination_html,
      'found_posts' => $products->found_posts,
      'max_pages' => $products->max_num_pages,
    ));
  } else {
    // No products found
    $no_products_html = ob_get_clean();
    
    // Get the no products found message
    ob_start();
    wc_get_template('loop/no-products-found.php');
    $no_products_html = ob_get_clean();
    
    wp_send_json_success(array(
      'products' => $no_products_html,
      'pagination' => '',
      'found_posts' => 0,
      'max_pages' => 0,
      'message' => __('No products were found matching your selection.', 'woocommerce'),
    ));
  }
  
  wp_reset_postdata();
}

// AJAX handler to get available colors and sizes for selected categories
add_action('wp_ajax_lfa_get_filter_options', 'lfa_ajax_get_filter_options');
add_action('wp_ajax_nopriv_lfa_get_filter_options', 'lfa_ajax_get_filter_options');

function lfa_ajax_get_filter_options() {
  check_ajax_referer('lfa-nonce', 'nonce');
  
  // Get selected categories
  $categories = array();
  if (isset($_POST['categories']) && !empty($_POST['categories'])) {
    $cats = json_decode(stripslashes($_POST['categories']), true);
    $categories = is_array($cats) ? array_map('intval', $cats) : array();
  }
  
  $available_colors = array();
  $available_sizes = array();
  
  // If no categories selected, return all colors and sizes as available
  if (empty($categories)) {
    $all_colors = get_terms(array(
      'taxonomy' => 'pa_color',
      'hide_empty' => false,
    ));
    
    $all_sizes = get_terms(array(
      'taxonomy' => 'pa_size',
      'hide_empty' => false,
    ));
    
    if (!is_wp_error($all_colors)) {
      foreach ($all_colors as $color) {
        $available_colors[] = $color->term_id;
      }
    }
    
    if (!is_wp_error($all_sizes)) {
      foreach ($all_sizes as $size) {
        $available_sizes[] = $size->term_id;
      }
    }
  } else {
    // Build query to get products in selected categories
    $args = array(
      'post_type' => 'product',
      'post_status' => 'publish',
      'posts_per_page' => -1,
      'fields' => 'ids',
      'meta_query' => WC()->query->get_meta_query(),
      'tax_query' => WC()->query->get_tax_query(),
    );
    
    // Add category filter
    $args['tax_query'][] = array(
      'taxonomy' => 'product_cat',
      'field' => 'term_id',
      'terms' => $categories,
      'operator' => 'IN',
    );
    
    $products = new WP_Query($args);
    
    if ($products->have_posts()) {
      $product_ids = $products->posts;
      
      // Get all colors and sizes
      $all_colors = get_terms(array(
        'taxonomy' => 'pa_color',
        'hide_empty' => false,
      ));
      
      $all_sizes = get_terms(array(
        'taxonomy' => 'pa_size',
        'hide_empty' => false,
      ));
      
      // Check which colors are available in these products
      if (!is_wp_error($all_colors)) {
        foreach ($all_colors as $color) {
          $color_products = get_posts(array(
            'post_type' => 'product',
            'post__in' => $product_ids,
            'posts_per_page' => 1,
            'tax_query' => array(
              array(
                'taxonomy' => 'pa_color',
                'field' => 'term_id',
                'terms' => $color->term_id,
              ),
            ),
            'fields' => 'ids',
          ));
          
          if (!empty($color_products)) {
            $available_colors[] = $color->term_id;
          }
        }
      }
      
      // Check which sizes are available in these products
      if (!is_wp_error($all_sizes)) {
        foreach ($all_sizes as $size) {
          $size_products = get_posts(array(
            'post_type' => 'product',
            'post__in' => $product_ids,
            'posts_per_page' => 1,
            'tax_query' => array(
              array(
                'taxonomy' => 'pa_size',
                'field' => 'term_id',
                'terms' => $size->term_id,
              ),
            ),
            'fields' => 'ids',
          ));
          
          if (!empty($size_products)) {
            $available_sizes[] = $size->term_id;
          }
        }
      }
    }
  }
  
  wp_reset_postdata();
  
  wp_send_json_success(array(
    'available_colors' => $available_colors,
    'available_sizes' => $available_sizes,
  ));
}

// AJAX: Get tab data for Find Your Fit page
add_action('wp_ajax_lfa_get_tab_data', 'lfa_ajax_get_tab_data');
add_action('wp_ajax_nopriv_lfa_get_tab_data', 'lfa_ajax_get_tab_data');

function lfa_ajax_get_tab_data() {
  check_ajax_referer('lfa-nonce', 'nonce');
  
  $image_id = isset($_POST['image_id']) ? intval($_POST['image_id']) : 0;
  $product_ids_json = isset($_POST['product_ids']) ? stripslashes($_POST['product_ids']) : '[]';
  $product_ids = json_decode($product_ids_json, true);
  
  if (!is_array($product_ids)) {
    $product_ids = array();
  }
  $product_ids = array_map('intval', $product_ids);
  $product_ids = array_filter($product_ids);
  
  // Get image
  $image_html = '';
  if ($image_id) {
    $image_html = wp_get_attachment_image($image_id, 'large', false, array('class' => 'lfa-fyf-category-img'));
  }
  
  // Get products
  $products_html = '';
  if (!empty($product_ids) && class_exists('WooCommerce')) {
    $args = array(
      'post_type' => 'product',
      'post_status' => 'publish',
      'post__in' => $product_ids,
      'posts_per_page' => -1,
      'orderby' => 'post__in', // Maintain the order specified in the array
    );
    
    $products_query = new WP_Query($args);
    
    if ($products_query->have_posts()) {
      ob_start();
      echo '<ul class="products lfa-grid lfa-grid-2">';
      global $post;
      while ($products_query->have_posts()) {
        $products_query->the_post();
        wc_get_template_part('content', 'product');
      }
      echo '</ul>';
      $products_html = ob_get_clean();
      wp_reset_postdata();
    } else {
      $products_html = '<p class="lfa-fyf-no-products">No products found.</p>';
    }
  } else {
    $products_html = '<p class="lfa-fyf-no-products">No products found.</p>';
  }
  
  wp_send_json_success(array(
    'image' => $image_html,
    'products' => $products_html,
  ));
}

// AJAX: Get tab image only (for instant image update)
add_action('wp_ajax_lfa_get_tab_image', 'lfa_ajax_get_tab_image');
add_action('wp_ajax_nopriv_lfa_get_tab_image', 'lfa_ajax_get_tab_image');

function lfa_ajax_get_tab_image() {
  check_ajax_referer('lfa-nonce', 'nonce');
  
  $image_id = isset($_POST['image_id']) ? intval($_POST['image_id']) : 0;
  
  // Get image
  $image_html = '';
  if ($image_id) {
    $image_html = wp_get_attachment_image($image_id, 'large', false, array('class' => 'lfa-fyf-category-img'));
  }
  
  wp_send_json_success(array(
    'image' => $image_html,
  ));
}