<?php
if ( ! defined( 'ABSPATH' ) ) exit;

add_action('wp_enqueue_scripts', function () {
  // Frontend fonts
  wp_enqueue_style('lfa-fonts', 'https://fonts.googleapis.com/css2?family=Questrial&display=swap', [], null);
  
  // Slick CSS (local files)
  wp_enqueue_style('slick-css', LFA_URI . '/assets/css/slick/slick.css', [], '1.8.1');
  wp_enqueue_style('slick-theme-css', LFA_URI . '/assets/css/slick/slick-theme.css', ['slick-css'], '1.8.1');
  
  wp_enqueue_style('lfa-main', LFA_URI . '/assets/css/main.css', [], LFA_VER);
  // Respect configurable container width
  $container = lfa_get_option('container_width', '1180px');
  if ($container) {
    wp_add_inline_style('lfa-main', ':root{--container:' . trim($container) . '}');
  }

  // Load rtl.css automatically when WordPress signals RTL
  if ( is_rtl() ) {
    wp_enqueue_style('lfa-rtl', LFA_URI . '/assets/css/rtl.css', ['lfa-main'], LFA_VER);
  }

  // Slick JavaScript (local file)
  wp_enqueue_script('slick-js', LFA_URI . '/assets/js/slick/slick.min.js', ['jquery'], '1.8.1', true);
  
  wp_enqueue_script('lfa-main', LFA_URI . '/assets/js/main.js', ['jquery'], LFA_VER, true);
  wp_enqueue_script('lfa-markets', LFA_URI . '/assets/js/markets.js', [], LFA_VER, true);
  wp_enqueue_script('lfa-sliders', LFA_URI . '/assets/js/sliders.js', ['jquery', 'slick-js'], LFA_VER, true);

  wp_localize_script('lfa-main', 'LFA', array(
    'ajaxUrl' => admin_url('admin-ajax.php'),
    'nonce'   => wp_create_nonce('lfa-nonce'),
  ));

  // Enqueue Find Your Fit CSS only on that template
  if (is_page_template('find-your-fit.php')) {
    wp_enqueue_style('lfa-find-your-fit', LFA_URI . '/assets/css/find-your-fit.css', ['lfa-main'], LFA_VER);
  }

  // Enqueue Policies CSS only on policy templates and FAQ template
  if (is_page_template('page-privacy-policy.php') || 
      is_page_template('page-shipping-policy.php') || 
      is_page_template('page-return-exchange-policy.php') || 
      is_page_template('page-terms-of-service.php') ||
      is_page_template('page-faq.php')) {
    wp_enqueue_style('lfa-policies', LFA_URI . '/assets/css/policies.css', ['lfa-main'], LFA_VER);
  }

  // Enqueue 404 page CSS and JS only on 404 page
  if (is_404()) {
    wp_enqueue_style('lfa-404', LFA_URI . '/assets/css/404.css', ['lfa-main'], LFA_VER);
    wp_enqueue_script('lfa-404', LFA_URI . '/assets/js/404.js', ['jquery', 'slick-js'], LFA_VER, true);
  }

  // Enqueue My Account CSS and JS only on my-account template
  if (is_page_template('page-my-account.php')) {
    wp_enqueue_style('lfa-my-account', LFA_URI . '/assets/css/my-account.css', ['lfa-main'], LFA_VER);
    wp_enqueue_script('lfa-my-account', LFA_URI . '/assets/js/my-account.js', ['jquery'], LFA_VER, true);
  }

  // Enqueue Contact CSS only on contact template
  if (is_page_template('page-contact.php')) {
    wp_enqueue_style('lfa-contact', LFA_URI . '/assets/css/contact.css', ['lfa-main'], LFA_VER);
  }

  // Enqueue Order Tracking CSS only on order tracking template
  if (is_page_template('page-order-tracking.php')) {
    wp_enqueue_style('lfa-order-tracking', LFA_URI . '/assets/css/order-tracking.css', ['lfa-main'], LFA_VER);
  }

  // Enqueue Cart CSS and JS globally (for cart drawer) when WooCommerce is active
  if (class_exists('WooCommerce')) {
    // Make cart CSS depend on WooCommerce styles to load after them
    $dependencies = ['lfa-main'];
    $dependencies[] = 'woocommerce-general';
    $dependencies[] = 'woocommerce-layout';
    
    wp_enqueue_style('lfa-cart', LFA_URI . '/assets/css/cart.css', $dependencies, LFA_VER);
    wp_enqueue_script('lfa-cart', LFA_URI . '/assets/js/cart.js', ['jquery'], LFA_VER, true);
    
    // Localize WooCommerce cart params if available
    if (class_exists('WC_AJAX') && function_exists('wc_get_cart_url')) {
      wp_localize_script('lfa-cart', 'wc_cart_params', [
        'wc_ajax_url' => \WC_AJAX::get_endpoint('%%endpoint%%'),
        'cart_url' => wc_get_cart_url(),
      ]);
    }
  }

  // Enqueue Single Product CSS and JS only on single product pages
  if (class_exists('WooCommerce') && is_product()) {
    wp_enqueue_style('lfa-single-product', LFA_URI . '/assets/css/single-product.css', ['lfa-main'], LFA_VER);
    wp_enqueue_script('lfa-single-product', LFA_URI . '/assets/js/single-product.js', ['jquery', 'slick-js'], LFA_VER, true);
  }
});
