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
  // Add data attribute for menu items with 'mega-menu' class
  if (!empty($item->classes) && is_array($item->classes) && in_array('mega-menu', $item->classes, true)) {
    // Skip if it already has mega-shop
    if (!in_array('mega-shop', $item->classes, true)) {
      $atts['data-mega'] = 'menu';
      $atts['aria-haspopup'] = 'true';
      $atts['aria-expanded'] = 'false';
    }
  }
  return $atts;
}, 10, 3);

// Helper function to get category links for mobile
if (!function_exists('lfa_get_category_links_mobile')) {
  function lfa_get_category_links_mobile($category_ids_str) {
    if (empty($category_ids_str) || !class_exists('WooCommerce')) {
      return [];
    }
    $ids = array_map('trim', explode(',', $category_ids_str));
    $ids = array_filter(array_map('intval', $ids));
    if (empty($ids)) {
      return [];
    }
    $terms = get_terms([
      'taxonomy' => 'product_cat',
      'include' => $ids,
      'hide_empty' => false,
      'orderby' => 'include',
    ]);
    if (is_wp_error($terms) || empty($terms)) {
      return [];
    }
    return $terms;
  }
}

// Inject mega menu content into sub-menu for mobile (during menu generation)
add_filter('wp_nav_menu_items', function($items, $args) {
  // Only process primary menu location or custom menu
  $is_target_menu = false;
  if (isset($args->theme_location) && $args->theme_location === 'primary') {
    $is_target_menu = true;
  } elseif (isset($args->menu) && !empty($args->menu)) {
    $is_target_menu = true;
  }
  
  if (!$is_target_menu) {
    return $items;
  }

  // Get mega menu settings
  $col1_title = lfa_get('header.megamenu.col1.title');
  $col1_cats = lfa_get('header.megamenu.col1.category_ids');
  $col2_title = lfa_get('header.megamenu.col2.title');
  $col2_cats = lfa_get('header.megamenu.col2.category_ids');
  $col3_title = lfa_get('header.megamenu.col3.title');
  $col3_cats = lfa_get('header.megamenu.col3.category_ids');

  // Build mobile mega menu HTML
  $mobile_mega = '<ul class="sub-menu lfa-mobile-mega">';
  
  // Column 1
  if (!empty($col1_cats)) {
    $col1_terms = lfa_get_category_links_mobile($col1_cats);
    if (!empty($col1_terms)) {
      if (!empty($col1_title)) {
        $mobile_mega .= '<li class="lfa-mega-mobile-title"><span>' . esc_html($col1_title) . '</span></li>';
      }
      foreach ($col1_terms as $term) {
        $mobile_mega .= '<li><a href="' . esc_url(get_term_link($term)) . '">' . esc_html($term->name) . '</a></li>';
      }
    }
  }

  // Column 2
  if (!empty($col2_cats)) {
    $col2_terms = lfa_get_category_links_mobile($col2_cats);
    if (!empty($col2_terms)) {
      if (!empty($col2_title)) {
        $mobile_mega .= '<li class="lfa-mega-mobile-title"><span>' . esc_html($col2_title) . '</span></li>';
      }
      foreach ($col2_terms as $term) {
        $mobile_mega .= '<li><a href="' . esc_url(get_term_link($term)) . '">' . esc_html($term->name) . '</a></li>';
      }
    }
  }

  // Column 3
  if (!empty($col3_cats)) {
    $col3_terms = lfa_get_category_links_mobile($col3_cats);
    if (!empty($col3_terms)) {
      if (!empty($col3_title)) {
        $mobile_mega .= '<li class="lfa-mega-mobile-title"><span>' . esc_html($col3_title) . '</span></li>';
      }
      foreach ($col3_terms as $term) {
        $mobile_mega .= '<li><a href="' . esc_url(get_term_link($term)) . '">' . esc_html($term->name) . '</a></li>';
      }
    }
  }

  $mobile_mega .= '</ul>';

  // Find menu items with mega-menu class and inject sub-menu
  // Use a more flexible pattern that matches the menu item structure
  if (!empty($mobile_mega) && $mobile_mega !== '<ul class="sub-menu lfa-mobile-mega"></ul>') {
    // Try multiple patterns to handle different menu structures
    $patterns = [
      // Pattern 1: <li class="... mega-menu ..."> ... <a>...</a> </li>
      '/(<li[^>]*class="[^"]*\bmega-menu\b[^"]*"[^>]*>)(.*?<a[^>]*>.*?<\/a>)(\s*)(<\/li>)/is',
      // Pattern 2: <li class="... mega-menu ..."> ... <a>...</a> ... </li> (with content after link)
      '/(<li[^>]*class="[^"]*\bmega-menu\b[^"]*"[^>]*>)(.*?<a[^>]*>.*?<\/a>)(.*?)(<\/li>)/is',
      // Pattern 3: Match any <li> with mega-menu class
      '/(<li[^>]*\bclass="[^"]*\bmega-menu\b[^"]*"[^>]*>)(.*?)(<\/li>)/is',
    ];
    
    // First, add plus/minus icon to the link
    $items = preg_replace_callback(
      '/(<li[^>]*class="[^"]*\bmega-menu\b[^"]*"[^>]*>)(.*?<a[^>]*>)(.*?)(<\/a>)(.*?)(<\/li>)/is',
      function($matches) {
        // Check if icon already exists
        if (strpos($matches[0], 'lfa-mega-toggle') === false) {
          // Add plus icon before closing </a>
          $icon = '<span class="lfa-mega-toggle"><span class="lfa-mega-plus">+</span><span class="lfa-mega-minus">−</span></span>';
          return $matches[1] . $matches[2] . $matches[3] . $icon . $matches[4] . $matches[5] . $matches[6];
        }
        return $matches[0];
      },
      $items
    );
    
    // Then inject the sub-menu
    foreach ($patterns as $pattern) {
      $new_items = preg_replace_callback(
        $pattern,
        function($matches) use ($mobile_mega) {
          // Check if sub-menu already exists in this item
          if (strpos($matches[0], 'lfa-mobile-mega') === false) {
            // Find where the </a> tag is to inject after it
            $link_end = strrpos($matches[2], '</a>');
            if ($link_end !== false) {
              $before_link = substr($matches[2], 0, $link_end + 4);
              $after_link = substr($matches[2], $link_end + 4);
              return $matches[1] . $before_link . $mobile_mega . $after_link . $matches[3];
            }
            // If no </a> found, just append before closing </li>
            return $matches[1] . $matches[2] . $mobile_mega . $matches[3];
          }
          return $matches[0];
        },
        $items
      );
      
      // If replacement happened, use the new items
      if ($new_items !== $items && $new_items !== null) {
        $items = $new_items;
        break;
      }
    }
  }

  return $items;
}, 10, 2);

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

// Change variation attributes from dropdowns to radio buttons
add_filter('woocommerce_dropdown_variation_attribute_options_args', function($args) {
  $args['type'] = 'radio';
  return $args;
}, 10, 1);

// Override WooCommerce variation attribute display to use radio buttons
add_filter('woocommerce_variation_attribute_options_html', function($html, $args) {
  $product = $args['product'];
  $attribute = $args['attribute'];
  $options = $args['options'];
  
  if (empty($options) || !$product) {
    return $html;
  }
  
  $selected = isset($args['selected']) ? $args['selected'] : $product->get_variation_default_attribute($attribute);
  $name = 'attribute_' . sanitize_title($attribute);
  $id = sanitize_title($attribute);
  
  $radio_html = '<div class="lfa-variation-radio-wrapper">';
  
  foreach ($options as $option) {
    $option_value = esc_attr($option);
    $option_label = esc_html(apply_filters('woocommerce_variation_option_name', $option));
    $checked = checked($selected, $option_value, false);
    $radio_id = $id . '_' . sanitize_title($option_value);
    
    $radio_html .= sprintf(
      '<label for="%s" class="lfa-variation-radio-label">
        <input type="radio" id="%s" name="%s" value="%s" %s class="lfa-variation-radio">
        <span class="lfa-radio-text">%s</span>
      </label>',
      $radio_id,
      $radio_id,
      $name,
      $option_value,
      $checked,
      $option_label
    );
  }
  
  $radio_html .= '</div>';
  
  return $radio_html;
}, 10, 2);

// WooCommerce content wrapper
if (class_exists('WooCommerce')) {
  // Remove default WooCommerce wrappers
  remove_action('woocommerce_before_main_content', 'woocommerce_output_content_wrapper', 10);
  remove_action('woocommerce_after_main_content', 'woocommerce_output_content_wrapper_end', 10);
  remove_action('woocommerce_sidebar', 'woocommerce_get_sidebar', 10);
  // Remove breadcrumbs
  remove_action('woocommerce_before_main_content', 'woocommerce_breadcrumb', 20);

  // Add custom content wrapper with sidebar layout (only for shop/archive pages, not single product)
  add_action('woocommerce_before_main_content', function() {
    // Don't add sidebar wrapper on single product pages
    if (is_product()) {
      echo '<div class="container">';
      return;
    }
    
    echo '<div class="container"><div class="woocommerce-shop-wrapper">';
    // Sidebar on the left
    echo '<div class="woocommerce-sidebar-wrapper">';
    get_template_part('woocommerce/sidebar');
    echo '</div>';
    // Content on the right
    echo '<div class="woocommerce-content-wrapper">';
  }, 10);

  add_action('woocommerce_after_main_content', function() {
    // Don't close sidebar wrapper on single product pages
    if (is_product()) {
      echo '</div>'; // Close container
      return;
    }
    
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

// Handle cart clearing
add_action('init', function() {
	if (isset($_GET['empty-cart']) && $_GET['empty-cart'] == '1') {
		if (class_exists('WooCommerce')) {
			WC()->cart->empty_cart();
			wp_safe_redirect(wc_get_cart_url());
			exit;
		}
	}
});

// Remove variation attributes from cart item names (we display them separately)
add_filter('woocommerce_cart_item_name', function($name, $cart_item, $cart_item_key) {
	// Only apply on cart page
	if (is_cart()) {
		$product = $cart_item['data'];
		if ($product && method_exists($product, 'get_name')) {
			// For variations, get parent product name
			if ($product->is_type('variation')) {
				$parent_id = $product->get_parent_id();
				if ($parent_id) {
					$parent_product = wc_get_product($parent_id);
					if ($parent_product) {
						$clean_name = wp_strip_all_tags($parent_product->get_name());
						return $clean_name;
					}
				}
			}
			// Strip any HTML tags that might contain attributes
			$clean_name = wp_strip_all_tags($product->get_name());
			return $clean_name;
		}
	}
	return $name;
}, 10, 3);

// Add custom class to cart remove button
add_filter('woocommerce_cart_item_remove_link', function($link, $cart_item_key) {
	// Only apply on cart page
	if (is_cart()) {
		// Add custom class if not already present
		if (strpos($link, 'lfa-remove-button') === false) {
			$link = str_replace('class="remove', 'class="lfa-remove-button remove', $link);
			$link = str_replace("class='remove", "class='lfa-remove-button remove", $link);
		}
	}
	return $link;
}, 10, 2);

// Ensure shipping is recalculated when cart page loads if shipping address is set
add_action('woocommerce_cart_loaded_from_session', function() {
	if (is_cart() && WC()->cart->needs_shipping()) {
		$shipping_country = WC()->customer->get_shipping_country();
		if (!empty($shipping_country) && !WC()->customer->has_calculated_shipping()) {
			WC()->customer->set_calculated_shipping(true);
			WC()->cart->calculate_shipping();
		}
	}
});

// Ensure customer shipping data is saved to session after shipping calculator processes
add_action('woocommerce_calculated_shipping', function() {
	if (function_exists('WC') && WC()->customer) {
		// Force save customer data to session
		WC()->customer->save();
	}
});

// Custom AJAX handler for shipping calculator on checkout page
add_action('wp_ajax_lfa_update_shipping_calculator', 'lfa_ajax_update_shipping_calculator');
add_action('wp_ajax_nopriv_lfa_update_shipping_calculator', 'lfa_ajax_update_shipping_calculator');

function lfa_ajax_update_shipping_calculator() {
	// Verify nonce
	$nonce = isset($_POST['woocommerce-shipping-calculator-nonce']) ? $_POST['woocommerce-shipping-calculator-nonce'] : '';
	if (empty($nonce) || !wp_verify_nonce($nonce, 'woocommerce-shipping-calculator')) {
		wp_send_json_error(array('message' => 'Invalid security token.'));
		return;
	}
	
	// Check if WooCommerce is active
	if (!class_exists('WooCommerce') || !WC()->customer) {
		wp_send_json_error(array('message' => 'WooCommerce is not available.'));
		return;
	}
	
	// Update customer shipping data
	if (isset($_POST['calc_shipping_country'])) {
		$country = sanitize_text_field($_POST['calc_shipping_country']);
		if ($country && $country !== 'default') {
			WC()->customer->set_shipping_country($country);
		}
	}
	if (isset($_POST['calc_shipping_state'])) {
		$state = sanitize_text_field($_POST['calc_shipping_state']);
		if ($state && $state !== 'default') {
			WC()->customer->set_shipping_state($state);
		}
	}
	if (isset($_POST['calc_shipping_city'])) {
		WC()->customer->set_shipping_city(sanitize_text_field($_POST['calc_shipping_city']));
	}
	if (isset($_POST['calc_shipping_postcode'])) {
		WC()->customer->set_shipping_postcode(sanitize_text_field($_POST['calc_shipping_postcode']));
	}
	
	WC()->customer->set_calculated_shipping(true);
	WC()->customer->save();
	
	// Trigger checkout update
	wp_send_json_success(array(
		'message' => 'Shipping updated successfully.',
		'fragments' => apply_filters('woocommerce_add_to_cart_fragments', array())
	));
}

// Completely disable WooCommerce's default shipping calculator processing on checkout
add_action('init', function() {
	// Only on checkout page - if shipping calculator was submitted via normal POST (not AJAX), remove the parameter
	// so WooCommerce doesn't process it and redirect to cart
	if (is_checkout() && isset($_POST['calc_shipping'])) {
		// Check if this is an AJAX request
		$is_ajax = wp_doing_ajax() || 
		           (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') ||
		           (isset($_POST['action']) && $_POST['action'] === 'lfa_update_shipping_calculator');
		
		// If NOT an AJAX request, remove calc_shipping from POST to prevent WooCommerce from processing it
		if (!$is_ajax) {
			unset($_POST['calc_shipping']);
			unset($_REQUEST['calc_shipping']);
		}
	}
}, 1);

// Prevent WooCommerce from processing shipping calculator on checkout at wp_loaded hook
add_action('wp_loaded', function() {
	// Only on checkout page
	if (is_checkout() && isset($_POST['calc_shipping'])) {
		// Check if this is an AJAX request
		$is_ajax = wp_doing_ajax() || 
		           (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') ||
		           (isset($_POST['action']) && $_POST['action'] === 'lfa_update_shipping_calculator');
		
		// If NOT an AJAX request, remove calc_shipping to prevent WooCommerce from processing
		if (!$is_ajax) {
			unset($_POST['calc_shipping']);
			unset($_REQUEST['calc_shipping']);
			unset($_GET['calc_shipping']);
		}
	}
}, 1);

// Completely prevent WooCommerce from processing shipping calculator on checkout
// Hook into the shipping calculator processing before WooCommerce does
add_action('woocommerce_cart_calculate_shipping', function() {
	if (is_checkout() && isset($_POST['calc_shipping']) && !wp_doing_ajax()) {
		// Check if this is our AJAX handler
		if (!isset($_POST['action']) || $_POST['action'] !== 'lfa_update_shipping_calculator') {
			// Remove calc_shipping to prevent WooCommerce from processing
			unset($_POST['calc_shipping']);
			unset($_REQUEST['calc_shipping']);
		}
	}
}, 1);

// Prevent WooCommerce from redirecting to cart when shipping calculator is used on checkout
// ALWAYS return checkout URL when on checkout page to prevent ANY redirects to cart
add_filter('woocommerce_get_cart_url', function($url) {
	// If we're on checkout page, NEVER redirect to cart - always stay on checkout
	if (is_checkout()) {
		return wc_get_checkout_url();
	}
	return $url;
}, 1); // Use priority 1 to run before other filters

// Intercept WooCommerce's shipping calculator redirect and force it to stay on checkout
// Use priority 1 to run before WooCommerce processes it
add_action('template_redirect', function() {
	// Skip if this is our AJAX handler request
	if (isset($_POST['action']) && $_POST['action'] === 'lfa_update_shipping_calculator') {
		return; // Let our AJAX handler process it
	}
	
	// Skip if this is an AJAX request
	if (wp_doing_ajax() || (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')) {
		return;
	}
	
	// If shipping calculator was submitted on checkout page via normal form submission, prevent redirect to cart
	if (is_checkout() && isset($_POST['calc_shipping']) && isset($_POST['calc_shipping_country'])) {
		// Process shipping calculator on checkout page
		if (function_exists('WC') && WC()->customer) {
			// Update customer shipping data
			if (isset($_POST['calc_shipping_country'])) {
				$country = sanitize_text_field($_POST['calc_shipping_country']);
				if ($country && $country !== 'default') {
					WC()->customer->set_shipping_country($country);
				}
			}
			if (isset($_POST['calc_shipping_state'])) {
				$state = sanitize_text_field($_POST['calc_shipping_state']);
				if ($state && $state !== 'default') {
					WC()->customer->set_shipping_state($state);
				}
			}
			if (isset($_POST['calc_shipping_city'])) {
				WC()->customer->set_shipping_city(sanitize_text_field($_POST['calc_shipping_city']));
			}
			if (isset($_POST['calc_shipping_postcode'])) {
				WC()->customer->set_shipping_postcode(sanitize_text_field($_POST['calc_shipping_postcode']));
			}
			WC()->customer->set_calculated_shipping(true);
			WC()->customer->save();
		}
		
		// Clear any notices that might have been set
		wc_clear_notices();
		// Redirect to checkout to stay on checkout page (instead of cart)
		// Add a query parameter to prevent infinite loops
		$checkout_url = wc_get_checkout_url();
		if (strpos($checkout_url, '?') !== false) {
			$checkout_url .= '&shipping_updated=1';
		} else {
			$checkout_url .= '?shipping_updated=1';
		}
		wp_safe_redirect($checkout_url);
		exit;
	}
	
	// Handle cart page shipping calculator (don't interfere)
	if (is_cart() && isset($_POST['calc_shipping']) && isset($_POST['calc_shipping_country'])) {
		// Shipping calculator form was submitted on cart page
		// Ensure customer data is saved after processing
		add_action('wp_loaded', function() {
			if (function_exists('WC') && WC()->customer) {
				WC()->customer->save();
			}
		}, 999);
	}
}, 1); // Priority 1 to run before WooCommerce

// Add custom classes to WooCommerce notices
add_filter('woocommerce_notice_wrapper_classes', function($classes) {
	$classes[] = 'lfa-woocommerce-notice-wrapper';
	return $classes;
});

// Add custom classes to individual notices via output filter
add_filter('woocommerce_add_notice', function($message, $notice_type = 'success', $data = array()) {
	// We'll add classes via CSS targeting, but this hook can be used for other modifications
	return $message;
}, 10, 3);

// Add cart badge to WooCommerce fragments for automatic updates
add_filter('woocommerce_add_to_cart_fragments', function($fragments) {
	if (!class_exists('WooCommerce') || !WC()->cart) {
		return $fragments;
	}
	
	$cart_count = WC()->cart->get_cart_contents_count();
	$badge_html = '<span class="hdr-cart-badge">' . esc_html($cart_count) . '</span>';
	
	// Add the badge as a fragment with a unique key
	$fragments['.hdr-cart-badge'] = $badge_html;
	
	return $fragments;
}, 10, 1);

// AJAX handler for cart drawer content
add_action('wp_ajax_lfa_get_cart_drawer', 'lfa_get_cart_drawer_content');
add_action('wp_ajax_nopriv_lfa_get_cart_drawer', 'lfa_get_cart_drawer_content');


function lfa_get_cart_drawer_content() {
	// Log that the function was called (for debugging)
	error_log('=== lfa_get_cart_drawer_content CALLED ===');
	error_log('POST data: ' . print_r($_POST, true));
	
	// Clear any previous output buffers
	while (ob_get_level()) {
		ob_end_clean();
	}
	
	// Ensure we're sending JSON
	nocache_headers();
	
	// Check nonce from either 'nonce' or '_ajax_nonce' parameter
	$nonce = isset($_POST['nonce']) ? $_POST['nonce'] : (isset($_POST['_ajax_nonce']) ? $_POST['_ajax_nonce'] : '');
	error_log('Nonce received: ' . ($nonce ? 'yes' : 'no'));
	
	if (empty($nonce) || !wp_verify_nonce($nonce, 'lfa-nonce')) {
		error_log('Nonce verification failed');
		wp_send_json_error(array('message' => 'Invalid nonce'));
		return;
	}
	
	if (!class_exists('WooCommerce')) {
		error_log('WooCommerce not active');
		wp_send_json_error(array('message' => 'WooCommerce not active'));
		return;
	}
	
	error_log('Starting cart drawer content generation');
	
	// Set flag to indicate we're rendering for drawer (only if not already defined)
	if (!defined('LFA_CART_DRAWER')) {
		define('LFA_CART_DRAWER', true);
	}
	
	// Start output buffering
	ob_start();
	
	// Include the cart template
	$cart_count = WC()->cart->get_cart_contents_count();
	?>
	<?php if ($cart_count > 0): ?>
		<div class="lfa-cart-layout">
			<div class="lfa-cart-left">
				<div class="lfa-cart-box">
					<div class="lfa-cart-box-header">
						<h2 class="lfa-cart-title"><?php printf(esc_html__('CART (%d)', 'woocommerce'), $cart_count); ?></h2>
						<a href="<?php echo esc_url(wc_get_cart_url()); ?>?empty-cart=1" class="lfa-cart-clear" aria-label="<?php esc_attr_e('Clear cart', 'woocommerce'); ?>">×</a>
					</div>

					<form class="woocommerce-cart-form" action="<?php echo esc_url(wc_get_cart_url()); ?>" method="post">
						<?php do_action('woocommerce_before_cart_table'); ?>

						<div class="lfa-cart-items">
							<?php do_action('woocommerce_before_cart_contents'); ?>

							<?php
							foreach (WC()->cart->get_cart() as $cart_item_key => $cart_item) {
								$_product = apply_filters('woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key);
								$product_id = apply_filters('woocommerce_cart_item_product_id', $cart_item['product_id'], $cart_item, $cart_item_key);
								$product_name = apply_filters('woocommerce_cart_item_name', $_product->get_name(), $cart_item, $cart_item_key);

								if ($_product && $_product->exists() && $cart_item['quantity'] > 0 && apply_filters('woocommerce_cart_item_visible', true, $cart_item, $cart_item_key)) {
									$product_permalink = apply_filters('woocommerce_cart_item_permalink', $_product->is_visible() ? $_product->get_permalink($cart_item) : '', $cart_item, $cart_item_key);
									?>
									<div class="lfa-cart-item <?php echo esc_attr(apply_filters('woocommerce_cart_item_class', 'cart_item', $cart_item, $cart_item_key)); ?>">
										<div class="lfa-cart-item-col-1">
											<div class="lfa-cart-item-image">
												<?php
												$thumbnail = apply_filters('woocommerce_cart_item_thumbnail', $_product->get_image(), $cart_item, $cart_item_key);
												if (!$product_permalink) {
													echo $thumbnail; // PHPCS: XSS ok.
												} else {
													printf('<a href="%s">%s</a>', esc_url($product_permalink), $thumbnail); // PHPCS: XSS ok.
												}
												?>
											</div>
										</div>
										<div class="lfa-cart-item-col-2">
											<h3 class="lfa-cart-item-name">
												<?php
												// Get product name without attributes - use parent product name for variations
												if ($_product->is_type('variation')) {
													$parent_id = $_product->get_parent_id();
													if ($parent_id) {
														$parent_product = wc_get_product($parent_id);
														$display_name = $parent_product ? $parent_product->get_name() : $_product->get_name();
													} else {
														$display_name = $_product->get_name();
													}
												} else {
													$display_name = $_product->get_name();
												}
												// Strip any HTML that might contain attributes
												$display_name = wp_strip_all_tags($display_name);
												if (!$product_permalink) {
													echo esc_html($display_name);
												} else {
													printf('<a href="%s">%s</a>', esc_url($product_permalink), esc_html($display_name));
												}
												?>
											</h3>
											<?php
											// Extract attribute values and format them with "/"
											$attributes = array();
											if (!empty($cart_item['variation'])) {
												foreach ($cart_item['variation'] as $key => $value) {
													if (!empty($value)) {
														$taxonomy = str_replace('attribute_', '', $key);
														$term = get_term_by('slug', $value, $taxonomy);
														if ($term) {
															$attributes[] = $term->name;
														} else {
															$attributes[] = $value;
														}
													}
												}
											}
											if (!empty($attributes)) {
												?>
												<div class="lfa-cart-item-attributes">
													<span class="lfa-cart-item-attr"><?php echo esc_html(implode(' / ', $attributes)); ?></span>
												</div>
												<?php
											}
											?>
											<div class="lfa-cart-item-quantity">
												<?php
												if ($_product->is_sold_individually()) {
													$min_quantity = 1;
													$max_quantity = 1;
												} else {
													$min_quantity = 0;
													$max_quantity = $_product->get_max_purchase_quantity();
												}
												?>
												<div class="lfa-quantity-control">
													<div class="lfa-quantity-control-inner">
														<button type="button" class="lfa-quantity-minus" data-cart-item-key="<?php echo esc_attr($cart_item_key); ?>" data-min="<?php echo esc_attr($min_quantity); ?>" aria-label="<?php esc_attr_e('Decrease quantity', 'woocommerce'); ?>">−</button>
														<input type="number" 
															class="lfa-quantity-input" 
															data-cart-item-key="<?php echo esc_attr($cart_item_key); ?>"
															name="cart[<?php echo esc_attr($cart_item_key); ?>][qty]" 
															value="<?php echo esc_attr($cart_item['quantity']); ?>" 
															min="<?php echo esc_attr($min_quantity); ?>" 
															max="<?php echo esc_attr($max_quantity); ?>"
															readonly
															aria-label="<?php esc_attr_e('Quantity', 'woocommerce'); ?>">
														<button type="button" class="lfa-quantity-plus" data-cart-item-key="<?php echo esc_attr($cart_item_key); ?>" data-max="<?php echo esc_attr($max_quantity); ?>" aria-label="<?php esc_attr_e('Increase quantity', 'woocommerce'); ?>">+</button>
													</div>
												</div>
											</div>
										</div>
										<div class="lfa-cart-item-col-3">
											<div class="lfa-cart-item-price">
												<?php
												echo apply_filters('woocommerce_cart_item_price', WC()->cart->get_product_price($_product), $cart_item, $cart_item_key); // PHPCS: XSS ok.
												?>
											</div>
											<div class="lfa-cart-item-remove">
												<?php
												echo apply_filters( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
													'woocommerce_cart_item_remove_link',
													sprintf(
														'<a href="%s" class="lfa-remove-button remove" aria-label="%s" data-product_id="%s" data-product_sku="%s">%s</a>',
														esc_url(wc_get_cart_remove_url($cart_item_key)),
														/* translators: %s is the product name */
														esc_attr(sprintf(__('Remove %s from cart', 'woocommerce'), wp_strip_all_tags($product_name))),
														esc_attr($product_id),
														esc_attr($_product->get_sku()),
														esc_html__('Remove', 'woocommerce')
													),
													$cart_item_key
												);
												?>
											</div>
										</div>
									</div>
									<?php
								}
							}
							?>

							<?php do_action('woocommerce_cart_contents'); ?>

							<?php wp_nonce_field('woocommerce-cart', 'woocommerce-cart-nonce'); ?>

							<?php do_action('woocommerce_after_cart_contents'); ?>
						</div>

						<?php do_action('woocommerce_after_cart_table'); ?>
					</form>
				</div>
			</div>

			<div class="lfa-cart-right">
				<?php do_action('woocommerce_before_cart_collaterals'); ?>

				<?php
				// Capture cart totals output to modify shipping section for drawer
				ob_start();
				/**
				 * Cart collaterals hook.
				 *
				 * @hooked woocommerce_cross_sell_display
				 * @hooked woocommerce_cart_totals - 10
				 */
				do_action('woocommerce_cart_collaterals');
				$cart_totals_html = ob_get_clean();
				
				// Modify shipping section to add accordion structure only in drawer
				if (defined('LFA_CART_DRAWER') && LFA_CART_DRAWER) {
					// Get shipping title
					$shipping_title = esc_html__('Shipping', 'woocommerce');
					if (WC()->cart->show_shipping()) {
						$shipping_state = WC()->customer->get_shipping_state() ? WC()->customer->get_shipping_state() : WC()->customer->get_billing_state();
						if ($shipping_state) {
							$shipping_title = sprintf(esc_html__('Shipping to %s', 'woocommerce'), esc_html($shipping_state));
						}
					}
					
					// Use regex to find and replace the shipping section with accordion
					// Match: <div class="lfa-cart-shipping-section">...everything until closing div...
					$pattern = '/(<div class="lfa-cart-shipping-section">\s*)(<h3 class="lfa-cart-shipping-title">.*?<\/h3>\s*)?(.*?)(<\/div>\s*(?=<div class="lfa-cart|<\/div>\s*<\/div>\s*<\/div>))/s';
					
					$replacement = '<div class="lfa-cart-shipping-section lfa-shipping-accordion">' .
						'<button type="button" class="lfa-shipping-accordion-toggle" aria-expanded="false">' .
						'<span class="lfa-cart-shipping-title">' . $shipping_title . '</span>' .
						'<span class="lfa-shipping-toggle-icon">+</span>' .
						'</button>' .
						'<div class="lfa-shipping-accordion-content">' .
						'$3' .
						'</div>' .
						'</div>';
					
					$cart_totals_html = preg_replace($pattern, $replacement, $cart_totals_html);
				}
				
				echo $cart_totals_html;
				?>
			</div>
		</div>
	<?php else: ?>
		<div class="lfa-cart-empty">
			<div class="lfa-cart-empty-content">
				<p class="lfa-cart-empty-message"><?php esc_html_e('No products added to the cart', 'woocommerce'); ?></p>
				
				<?php if (wc_get_page_id('shop') > 0): ?>
					<a class="lfa-cart-empty-button button" href="<?php echo esc_url(apply_filters('woocommerce_return_to_shop_redirect', wc_get_page_permalink('shop'))); ?>">
						<?php echo esc_html(apply_filters('woocommerce_return_to_shop_text', __('Return to shop', 'woocommerce'))); ?>
					</a>
				<?php endif; ?>
			</div>
		</div>
	<?php endif; ?>
	<?php
	
	$html = ob_get_clean();
	
	error_log('Cart drawer HTML generated, length: ' . strlen($html));
	
	// Ensure no output before sending JSON
	while (ob_get_level()) {
		ob_end_clean();
	}
	
	// Send JSON response (wp_send_json_success will exit, but adding exit for safety)
	error_log('Sending JSON response');
	wp_send_json_success(array('html' => $html));
	exit;
}

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

// Custom AJAX handler for applying coupon on checkout - hook early to bypass default checks
add_action('init', 'lfa_register_coupon_ajax_handler', 5);
function lfa_register_coupon_ajax_handler() {
    // Register for both logged in and logged out users
    add_action('wp_ajax_lfa_apply_coupon', 'lfa_ajax_apply_coupon');
    add_action('wp_ajax_nopriv_lfa_apply_coupon', 'lfa_ajax_apply_coupon');
}

// Also register for wc_ajax endpoint
add_action('wc_ajax_lfa_apply_coupon', 'lfa_ajax_apply_coupon');

function lfa_ajax_apply_coupon() {
    // Set proper headers
    nocache_headers();
    
    // Verify nonce - try multiple possible nonce fields and actions
    $nonce = '';
    $nonce_field = '';
    
    if (isset($_POST['security'])) {
        $nonce = $_POST['security'];
        $nonce_field = 'security';
    } elseif (isset($_POST['woocommerce-cart-nonce'])) {
        $nonce = $_POST['woocommerce-cart-nonce'];
        $nonce_field = 'woocommerce-cart-nonce';
    }
    
    // Try multiple nonce actions that WooCommerce might use
    $nonce_verified = false;
    if (!empty($nonce)) {
        // Try woocommerce-cart action (most common for cart operations)
        if (wp_verify_nonce($nonce, 'woocommerce-cart')) {
            $nonce_verified = true;
        }
        // Try woocommerce-apply-coupon action
        elseif (wp_verify_nonce($nonce, 'woocommerce-apply-coupon')) {
            $nonce_verified = true;
        }
        // Try woocommerce-process-checkout action (for checkout page)
        elseif (wp_verify_nonce($nonce, 'woocommerce-process-checkout')) {
            $nonce_verified = true;
        }
    }
    
    if (!$nonce_verified) {
        wp_send_json_error(array('message' => 'Invalid security token. Please refresh the page and try again.'));
        wp_die();
        return;
    }
    
    // Check if WooCommerce is active
    if (!class_exists('WooCommerce') || !WC()->cart) {
        wp_send_json_error(array('message' => 'WooCommerce is not available.'));
        return;
    }
    
    // Get coupon code
    $coupon_code = isset($_POST['coupon_code']) ? sanitize_text_field($_POST['coupon_code']) : '';
    
    if (empty($coupon_code)) {
        wp_send_json_error(array('message' => 'Please enter a coupon code.'));
        return;
    }
    
    // Clear any existing notices
    wc_clear_notices();
    
    // Apply the coupon
    $result = WC()->cart->apply_coupon($coupon_code);
    
    // Get notices after applying
    $notices = wc_get_notices();
    wc_clear_notices();
    
    if ($result) {
        // Coupon applied successfully
        wp_send_json_success(array(
            'message' => 'Coupon applied successfully.',
            'fragments' => apply_filters('woocommerce_add_to_cart_fragments', array())
        ));
    } else {
        // Get error message from notices
        $error_message = 'Invalid coupon code.';
        foreach ($notices as $notice) {
            if ($notice['notice_type'] === 'error') {
                $error_message = $notice['notice'];
                break;
            }
        }
        wp_send_json_error(array('message' => $error_message));
    }
}

// Custom AJAX handler for removing coupon on checkout - hook early to bypass default checks
add_action('init', 'lfa_register_remove_coupon_ajax_handler', 5);
function lfa_register_remove_coupon_ajax_handler() {
    // Register for both logged in and logged out users
    add_action('wp_ajax_lfa_remove_coupon', 'lfa_ajax_remove_coupon');
    add_action('wp_ajax_nopriv_lfa_remove_coupon', 'lfa_ajax_remove_coupon');
}

// Also register for wc_ajax endpoint
add_action('wc_ajax_lfa_remove_coupon', 'lfa_ajax_remove_coupon');

function lfa_ajax_remove_coupon() {
    // Set proper headers
    nocache_headers();
    
    // Verify nonce - try multiple possible nonce fields and actions
    $nonce = '';
    $nonce_field = '';
    
    if (isset($_POST['security'])) {
        $nonce = $_POST['security'];
        $nonce_field = 'security';
    } elseif (isset($_POST['woocommerce-cart-nonce'])) {
        $nonce = $_POST['woocommerce-cart-nonce'];
        $nonce_field = 'woocommerce-cart-nonce';
    } elseif (isset($_GET['_wpnonce'])) {
        $nonce = $_GET['_wpnonce'];
        $nonce_field = '_wpnonce';
    }
    
    // Try multiple nonce actions that WooCommerce might use
    $nonce_verified = false;
    if (!empty($nonce)) {
        // Try woocommerce-cart action (most common for cart operations)
        if (wp_verify_nonce($nonce, 'woocommerce-cart')) {
            $nonce_verified = true;
        }
        // Try woocommerce-remove-coupon action
        elseif (wp_verify_nonce($nonce, 'woocommerce-remove-coupon')) {
            $nonce_verified = true;
        }
        // Try woocommerce-process-checkout action (for checkout page)
        elseif (wp_verify_nonce($nonce, 'woocommerce-process-checkout')) {
            $nonce_verified = true;
        }
    }
    
    if (!$nonce_verified) {
        wp_send_json_error(array('message' => 'Invalid security token. Please refresh the page and try again.'));
        wp_die();
        return;
    }
    
    // Check if WooCommerce is active
    if (!class_exists('WooCommerce') || !WC()->cart) {
        wp_send_json_error(array('message' => 'WooCommerce is not available.'));
        return;
    }
    
    // Get coupon code
    $coupon_code = '';
    if (isset($_POST['coupon'])) {
        $coupon_code = sanitize_text_field($_POST['coupon']);
    } elseif (isset($_GET['coupon'])) {
        $coupon_code = sanitize_text_field($_GET['coupon']);
    } elseif (isset($_POST['remove_coupon'])) {
        $coupon_code = sanitize_text_field($_POST['remove_coupon']);
    }
    
    if (empty($coupon_code)) {
        wp_send_json_error(array('message' => 'No coupon code provided.'));
        return;
    }
    
    // Remove the coupon
    $result = WC()->cart->remove_coupon($coupon_code);
    
    // Get notices after removing
    $notices = wc_get_notices();
    wc_clear_notices();
    
    if ($result) {
        // Coupon removed successfully
        wp_send_json_success(array(
            'message' => 'Coupon removed successfully.',
            'fragments' => apply_filters('woocommerce_add_to_cart_fragments', array())
        ));
    } else {
        // Get error message from notices
        $error_message = 'Failed to remove coupon.';
        foreach ($notices as $notice) {
            if ($notice['notice_type'] === 'error') {
                $error_message = $notice['notice'];
                break;
            }
        }
        wp_send_json_error(array('message' => $error_message));
    }
}

// Register wishlist endpoint for My Account
add_action('init', function() {
  if (class_exists('WooCommerce')) {
    add_rewrite_endpoint('wishlist', EP_ROOT | EP_PAGES);
  }
}, 10);

// Add wishlist to My Account menu
add_filter('woocommerce_account_menu_items', function($items) {
  // Insert wishlist before logout
  $logout = $items['customer-logout'];
  unset($items['customer-logout']);
  $items['wishlist'] = __('Wishlist', 'woocommerce');
  $items['customer-logout'] = $logout;
  return $items;
}, 99);

// Handle TI Wishlist removal
add_action('init', function() {
  if (isset($_GET['tinvwl-remove']) && is_user_logged_in()) {
    $wishlist_product_id = absint($_GET['tinvwl-remove']);
    
    // Verify nonce if provided
    if (isset($_GET['_wpnonce'])) {
      if (!wp_verify_nonce($_GET['_wpnonce'], 'tinvwl_remove_' . $wishlist_product_id)) {
        wp_die('Security check failed');
      }
    }
    
    if ($wishlist_product_id > 0) {
      global $wpdb;
      $table = $wpdb->prefix . 'tinvwl_items';
      
      // Verify the item belongs to current user before deleting
      $item = $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM {$table} WHERE ID = %d",
        $wishlist_product_id
      ));
      
      if ($item && isset($item->wishlist_id)) {
        // Get wishlist to verify ownership
        $wishlist_table = $wpdb->prefix . 'tinvwl_lists';
        $wishlist = $wpdb->get_row($wpdb->prepare(
          "SELECT * FROM {$wishlist_table} WHERE ID = %d AND author = %d",
          $item->wishlist_id,
          get_current_user_id()
        ));
        
        if ($wishlist) {
          // Delete the item directly from database using SQL query
          $deleted = $wpdb->query($wpdb->prepare(
            "DELETE FROM {$table} WHERE ID = %d",
            $wishlist_product_id
          ));
          
          // Check if deletion was successful
          if ($deleted !== false && $deleted > 0) {
            wc_add_notice(__('Product removed from wishlist.', 'woocommerce'), 'success');
            // Clear any caches
            if (function_exists('wp_cache_flush')) {
              wp_cache_flush();
            }
          } else {
            wc_add_notice(__('Failed to remove product from wishlist.', 'woocommerce'), 'error');
          }
        } else {
          wc_add_notice(__('You do not have permission to remove this item.', 'woocommerce'), 'error');
        }
      } else {
        wc_add_notice(__('Product not found in wishlist.', 'woocommerce'), 'error');
      }
    }
    
    // Redirect back to wishlist page
    $redirect_url = wc_get_page_permalink('myaccount') . 'wishlist/';
    wp_safe_redirect($redirect_url);
    exit;
  }
}, 10);

// Load custom wishlist template - use high priority to override plugin templates
add_action('woocommerce_account_wishlist_endpoint', function() {
  // Remove any default TI Wishlist actions that might interfere
  if (class_exists('TInvWL_Public_Wishlist_View')) {
    remove_all_actions('woocommerce_account_wishlist_endpoint');
  }
  
  $template_file = get_template_directory() . '/woocommerce/myaccount/wishlist.php';
  if (file_exists($template_file)) {
    include $template_file;
  }
}, 1); // Priority 1 to run before other handlers

// AJAX handler to add variable/simple products to wishlist
add_action('wp_ajax_lfa_add_to_wishlist', 'lfa_add_to_wishlist');
add_action('wp_ajax_nopriv_lfa_add_to_wishlist', 'lfa_add_to_wishlist');

function lfa_add_to_wishlist() {
  // Verify user is logged in
  if (!is_user_logged_in()) {
    wp_send_json_error(array('message' => 'You must be logged in to add items to wishlist.'));
    return;
  }

  // Verify nonce
  if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'lfa-nonce')) {
    wp_send_json_error(array('message' => 'Security check failed.'));
    return;
  }

  // Get product ID
  $product_id = isset($_POST['product_id']) ? absint($_POST['product_id']) : 0;
  $variation_id = isset($_POST['variation_id']) ? absint($_POST['variation_id']) : 0;
  $quantity = isset($_POST['quantity']) ? absint($_POST['quantity']) : 1;
  
  if (!$product_id) {
    wp_send_json_error(array('message' => 'Invalid product ID.'));
    return;
  }

  // Ensure WooCommerce is loaded
  if (!function_exists('wc_get_product')) {
    wp_send_json_error(array('message' => 'WooCommerce is not available.'));
    return;
  }

  // Verify product exists
  $product = wc_get_product($product_id);
  if (!$product) {
    wp_send_json_error(array('message' => 'Product not found.'));
    return;
  }

  // For variable products, verify variation exists
  if ($variation_id > 0) {
    $variation = wc_get_product($variation_id);
    if (!$variation) {
      wp_send_json_error(array('message' => 'Variation not found.'));
      return;
    }
    // Note: Gift cards might not be type 'variation', so we'll be more lenient
    if (!$variation->is_type('variation') && !$variation->is_type('simple')) {
      wp_send_json_error(array('message' => 'Invalid variation type.'));
      return;
    }
  }

  // Try to add to TI Wishlist
  if (!class_exists('TInvWL_Wishlist') || !class_exists('TInvWL_Product')) {
    wp_send_json_error(array('message' => 'Wishlist plugin classes not found.'));
    return;
  }

  try {
    // Get user's default wishlist
    $wl = new TInvWL_Wishlist();
    $wishlists = $wl->get_by_user(get_current_user_id());
    
    if (empty($wishlists) || !is_array($wishlists)) {
      // Create a default wishlist if none exists
      $wishlist_id = $wl->add_default();
      if (!$wishlist_id) {
        wp_send_json_error(array('message' => 'Could not create wishlist.'));
        return;
      }
      $wishlist = $wl->get_by_id($wishlist_id);
    } else {
      $wishlist = reset($wishlists); // Get the first/default wishlist
    }
    
    if (!$wishlist || !isset($wishlist['ID'])) {
      wp_send_json_error(array('message' => 'Could not find or create a wishlist.'));
      return;
    }
    
    // Prepare meta data for variations
    // For variable products, TI Wishlist expects variation data in meta array
    // The filter_var_array error suggests we should pass variation_id in meta, not as separate param
    $meta = array();
    if ($variation_id > 0) {
      // Always add variation_id to meta for variable products
      $meta['variation_id'] = $variation_id;
      
      $variation_product = wc_get_product($variation_id);
      if ($variation_product && $variation_product->is_type('variation')) {
        $variation_attributes = $variation_product->get_variation_attributes();
        if (!empty($variation_attributes)) {
          foreach ($variation_attributes as $key => $value) {
            // Ensure key has 'attribute_' prefix if it's a taxonomy
            $meta_key = (strpos($key, 'attribute_') === 0) ? $key : 'attribute_' . $key;
            $meta[$meta_key] = $value;
          }
        }
      }
    }
    
    // Ensure meta is always an array (required by filter_var_array in TI Wishlist)
    if (!is_array($meta)) {
      $meta = array();
    }
    
    // TI Wishlist plugin has a bug in add_product() that causes filter_var_array error
    // Workaround: Insert directly into database to bypass the problematic code
    global $wpdb;
    $table_name = $wpdb->prefix . 'tinvwl_items';
    
    // Check if item already exists
    $existing = $wpdb->get_var($wpdb->prepare(
        "SELECT ID FROM {$table_name} WHERE product_id = %d AND variation_id = %d AND wishlist_id = %d",
        $product_id,
        $variation_id,
        $wishlist['ID']
    ));
    
    if ($existing) {
        wp_send_json_error(array('message' => 'Product is already in your wishlist.'));
        return;
    }
    
    // Get product price for the wishlist entry
    $product_obj = wc_get_product($variation_id > 0 ? $variation_id : $product_id);
    $price = $product_obj ? $product_obj->get_price() : 0;
    $in_stock = $product_obj ? ($product_obj->is_in_stock() ? 1 : 0) : 1;
    
    // Insert the item directly into the database
    // Note: The 'meta' column doesn't exist in the table, so we omit it
    // Variation data is stored via variation_id, and attributes are retrieved from the product
    $inserted = $wpdb->insert(
        $table_name,
        array(
            'product_id' => (int)$product_id,
            'variation_id' => (int)$variation_id,
            'quantity' => (int)$quantity,
            'price' => $price,
            'in_stock' => $in_stock,
            'wishlist_id' => $wishlist['ID'],
            'author' => get_current_user_id(),
            'date' => current_time('mysql')
        ),
        array('%d', '%d', '%d', '%f', '%d', '%d', '%d', '%s')
    );
    
    if ($inserted) {
        $result = $wpdb->insert_id;
    } else {
        $result = false;
    }
    
    // Check result - it should return the wishlist item ID on success
    if ($result !== false && $result > 0) {
      wp_send_json_success(array(
        'message' => 'Product added to wishlist!',
        'product_id' => $product_id,
        'variation_id' => $variation_id
      ));
    } else {
      // If insert failed, check if it's because item already exists
      $existing = $wpdb->get_var($wpdb->prepare(
        "SELECT ID FROM {$table_name} WHERE product_id = %d AND variation_id = %d AND wishlist_id = %d",
        $product_id,
        $variation_id,
        $wishlist['ID']
      ));
      
      if ($existing) {
        wp_send_json_error(array('message' => 'Product is already in your wishlist.'));
      } else {
        wp_send_json_error(array('message' => 'Failed to add product to wishlist. Please try again.'));
      }
    }
  } catch (Exception $e) {
    wp_send_json_error(array('message' => 'Exception: ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine()));
  } catch (Error $e) {
    wp_send_json_error(array('message' => 'Fatal error: ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine()));
  }
}

// AJAX handler to add composite products to wishlist
add_action('wp_ajax_lfa_add_composite_to_wishlist', 'lfa_add_composite_to_wishlist');
add_action('wp_ajax_nopriv_lfa_add_composite_to_wishlist', 'lfa_add_composite_to_wishlist');

function lfa_add_composite_to_wishlist() {
  // Verify user is logged in
  if (!is_user_logged_in()) {
    wp_send_json_error(array('message' => 'You must be logged in to add items to wishlist.'));
    return;
  }

  // Verify nonce
  if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'lfa-nonce')) {
    wp_send_json_error(array('message' => 'Security check failed.'));
    return;
  }

  // Get product ID
  $product_id = isset($_POST['product_id']) ? absint($_POST['product_id']) : 0;
  
  if (!$product_id) {
    wp_send_json_error(array('message' => 'Invalid product ID.'));
    return;
  }

  // Verify product exists
  $product = wc_get_product($product_id);
  if (!$product) {
    wp_send_json_error(array('message' => 'Product not found.'));
    return;
  }

  // Get component data from POST
  $composite_data = array();
  foreach ($_POST as $key => $value) {
    if (strpos($key, 'wccp_component') !== false || strpos($key, 'component_') !== false) {
      $composite_data[$key] = sanitize_text_field($value);
    }
  }

  // Try to add to TI Wishlist
  if (class_exists('TInvWL_Wishlist') && class_exists('TInvWL_Product')) {
    try {
      // Get user's default wishlist
      $wl = new TInvWL_Wishlist();
      $wishlists = $wl->get_by_user(get_current_user_id());
      
      if (empty($wishlists) || !is_array($wishlists)) {
        // Create a default wishlist if none exists
        $wishlist_id = $wl->add_default();
        $wishlist = $wl->get_by_id($wishlist_id);
      } else {
        $wishlist = reset($wishlists); // Get the first/default wishlist
      }
      
      if ($wishlist && isset($wishlist['ID'])) {
        // Prepare product data
        $wl_product_data = array(
          'product_id' => $product_id,
          'quantity' => 1,
          'meta' => $composite_data
        );
        
        // Add product to wishlist
        $wl_product = new TInvWL_Product($wishlist);
        $result = $wl_product->add_product($product_id, 0, $composite_data, 1);
        
        if ($result) {
          wp_send_json_success(array(
            'message' => 'Product added to wishlist!',
            'product_id' => $product_id
          ));
        } else {
          wp_send_json_error(array('message' => 'Failed to add product to wishlist. It might already be there.'));
        }
      } else {
        wp_send_json_error(array('message' => 'Could not find or create a wishlist.'));
      }
    } catch (Exception $e) {
      wp_send_json_error(array('message' => 'Error: ' . $e->getMessage()));
    }
  } else {
    wp_send_json_error(array('message' => 'Wishlist plugin not found.'));
  }
}

// Custom checkout order review template
// Remove default WooCommerce order review actions
add_action('woocommerce_before_checkout_form', function() {
  if (class_exists('WooCommerce')) {
    remove_action('woocommerce_checkout_order_review', 'woocommerce_order_review', 10);
    remove_action('woocommerce_checkout_order_review', 'woocommerce_checkout_payment', 20);
    // Remove the coupon toggle ("Have a coupon?" link)
    remove_action('woocommerce_before_checkout_form', 'woocommerce_checkout_coupon_form', 10);
  }
}, 5);

// Add placeholders to checkout fields based on labels
add_filter('woocommerce_checkout_fields', function($fields) {
  // Process billing fields
  if (isset($fields['billing'])) {
    foreach ($fields['billing'] as $key => &$field) {
      if (isset($field['label']) && !empty($field['label'])) {
        // Convert label to placeholder
        $placeholder = $field['label'];
        // Remove required asterisk if present
        $placeholder = str_replace(' *', '', $placeholder);
        // Add "(Optional)" for optional fields
        if (isset($field['required']) && !$field['required']) {
          $placeholder .= ' (Optional)';
        }
        $field['placeholder'] = $placeholder;
        
        // For select fields, add a default empty option with placeholder text
        if (isset($field['type']) && $field['type'] === 'select' && isset($field['options'])) {
          $field['options'] = array('' => $placeholder) + $field['options'];
        }
      }
    }
  }
  
  // Process shipping fields
  if (isset($fields['shipping'])) {
    foreach ($fields['shipping'] as $key => &$field) {
      if (isset($field['label']) && !empty($field['label'])) {
        // Convert label to placeholder
        $placeholder = $field['label'];
        // Remove required asterisk if present
        $placeholder = str_replace(' *', '', $placeholder);
        // Add "(Optional)" for optional fields
        if (isset($field['required']) && !$field['required']) {
          $placeholder .= ' (Optional)';
        }
        $field['placeholder'] = $placeholder;
        
        // For select fields, add a default empty option with placeholder text
        if (isset($field['type']) && $field['type'] === 'select' && isset($field['options'])) {
          $field['options'] = array('' => $placeholder) + $field['options'];
        }
      }
    }
  }
  
  // Process order fields (order notes)
  if (isset($fields['order'])) {
    foreach ($fields['order'] as $key => &$field) {
      if (isset($field['label']) && !empty($field['label'])) {
        // For order notes, set custom placeholder
        if ($key === 'order_comments') {
          $field['placeholder'] = 'Notes about your order, e.g special notes for delivery';
        } else {
          // Convert label to placeholder for other order fields
          $placeholder = $field['label'];
          // Remove required asterisk if present
          $placeholder = str_replace(' *', '', $placeholder);
          // Add "(Optional)" for optional fields
          if (isset($field['required']) && !$field['required']) {
            $placeholder .= ' (Optional)';
          }
          $field['placeholder'] = $placeholder;
        }
      }
    }
  }
  
  return $fields;
}, 20);

// Add our custom order review
add_action('woocommerce_checkout_order_review', function() {
  if (!class_exists('WooCommerce') || !function_exists('WC')) {
    error_log('WooCommerce or WC() function not available in woocommerce_checkout_order_review hook.');
    return;
  }
  
  if (!WC()->cart) {
    error_log('WC()->cart not available in woocommerce_checkout_order_review hook.');
    return;
  }
  
  // Ensure checkout object exists
  global $checkout;
  if (!isset($checkout) || !($checkout instanceof WC_Checkout)) {
    $checkout = WC()->checkout();
  }
  
  // Load our custom template
  $template_file = get_template_directory() . '/woocommerce/checkout/order-review.php';
  if (file_exists($template_file)) {
    load_template($template_file, false, array('checkout' => $checkout));
  } else {
    error_log('Order review template not found: ' . $template_file);
  }
}, 10);

// Ensure checkout uses checkout shipping calculator template
add_filter('woocommerce_locate_template', function($template, $template_name, $template_path) {
  if ($template_name === 'checkout/shipping-calculator.php' && is_checkout()) {
    $checkout_template = get_template_directory() . '/woocommerce/checkout/shipping-calculator.php';
    if (file_exists($checkout_template)) {
      return $checkout_template;
    }
  }
  return $template;
}, 10, 3);