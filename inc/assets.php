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

  $lfa_strings = array(
    'ajaxUrl' => admin_url('admin-ajax.php'),
    'nonce'   => wp_create_nonce('lfa-nonce'),
    'isRtl'   => is_rtl(),
    'strings' => array(
      'loading'                => __('Loading...', 'livingfitapparel'),
      'loadingCart'            => __('Loading cart...', 'livingfitapparel'),
      'loadMore'               => __('Load more', 'livingfitapparel'),
      'trending'               => __('TRENDING PRODUCTS', 'livingfitapparel'),
      'searchResultsFor'       => __('Results for "%s"', 'livingfitapparel'),
      'errorLoadingProduct'    => __('Error loading product details.', 'livingfitapparel'),
      'errorLoadingCart'       => __('Error loading cart', 'livingfitapparel'),
      'errorLoadingResults'    => __('Error loading results', 'livingfitapparel'),
      'errorLfaNotDefined'     => __('Error: LFA not defined', 'livingfitapparel'),
      'errorAjaxUrlMissing'    => __('Error: ajaxUrl missing', 'livingfitapparel'),
      'errorJqueryMissing'     => __('Error: jQuery not available', 'livingfitapparel'),
      'requestTimeout'         => __('Request timeout. Please try again.', 'livingfitapparel'),
      'connectionTimeout'      => __('Request timed out. Please check your connection.', 'livingfitapparel'),
      'networkError'           => __('Network error', 'livingfitapparel'),
      'maxQtyReached'          => __('Max quantity reached', 'livingfitapparel'),
      'updateCart'             => __('Update cart', 'livingfitapparel'),
      'failedRemoveCoupon'     => __('Failed to remove coupon.', 'livingfitapparel'),
      'applying'               => __('Applying...', 'livingfitapparel'),
      'apply'                  => __('Apply', 'livingfitapparel'),
      'couponError'            => __('An error occurred while applying the coupon. Please try again.', 'livingfitapparel'),
      'invalidCoupon'          => __('Invalid coupon code', 'livingfitapparel'),
      'serverError'            => __('A server error occurred. Please try again or contact support if the problem persists.', 'livingfitapparel'),
      'couponFormError'        => __('Error: Could not find coupon form. Please refresh the page.', 'livingfitapparel'),
      'updating'               => __('Updating...', 'livingfitapparel'),
      'update'                 => __('Update', 'livingfitapparel'),
      'failedUpdateShipping'   => __('Failed to update shipping.', 'livingfitapparel'),
      'shippingUpdateError'    => __('An error occurred while updating shipping. Please try again.', 'livingfitapparel'),
      'emailRequired'          => __('Email address is required.', 'livingfitapparel'),
      'emailInvalid'           => __('Please enter a valid email address.', 'livingfitapparel'),
      'passwordRequired'       => __('Password is required.', 'livingfitapparel'),
      'loggingIn'              => __('Logging in...', 'livingfitapparel'),
      'loginSuccess'           => __('Login successful! Redirecting...', 'livingfitapparel'),
      'loginFailed'            => __('Login failed. Please check your credentials.', 'livingfitapparel'),
      'genericError'           => __('An error occurred. Please try again.', 'livingfitapparel'),
      'signingUp'              => __('Signing up...', 'livingfitapparel'),
      'signupSuccess'          => __('Signup successful! Please check your email to set your password.', 'livingfitapparel'),
      'signupFailed'           => __('Signup failed. Please try again.', 'livingfitapparel'),
      'signUp'                 => __('SIGN UP', 'livingfitapparel'),
      'sending'                => __('Sending...', 'livingfitapparel'),
      'resetLinkSent'          => __('Password reset link has been sent to your email address.', 'livingfitapparel'),
      'resetLinkFailed'        => __('Failed to send reset link. Please try again.', 'livingfitapparel'),
      'enterEmail'             => __('Please enter your email address.', 'livingfitapparel'),
      'processing'             => __('Processing...', 'livingfitapparel'),
      'newsletterSuccess'      => __('Thank you! Check your email for your discount code.', 'livingfitapparel'),
      'newsletterError'        => __('There was an error. Please try again.', 'livingfitapparel'),
      'newsletterSubmitError'  => __('There was an error submitting your email. Please try again.', 'livingfitapparel'),
      'addToCart'              => __('ADD TO CART', 'livingfitapparel'),
      'addToWishlist'          => __('Add to wishlist', 'livingfitapparel'),
      'buyItNow'               => __('BUY IT NOW', 'livingfitapparel'),
      'selectVariation'        => __('Please select a variation first.', 'livingfitapparel'),
      'wishlistAdded'          => __('Product added to wishlist!', 'livingfitapparel'),
      'wishlistFailed'         => __('Failed to add product to wishlist.', 'livingfitapparel'),
      'wishlistUnexpected'     => __('An unexpected error occurred. Please try again.', 'livingfitapparel'),
      'previous'               => __('Previous', 'livingfitapparel'),
      'next'                   => __('Next', 'livingfitapparel'),
    ),
  );
  wp_localize_script('lfa-main', 'LFA', $lfa_strings);

  // Enqueue Find Your Fit CSS only on that template
  if (is_page_template('find-your-fit.php')) {
    wp_enqueue_style('lfa-find-your-fit', LFA_URI . '/assets/css/find-your-fit.css', ['lfa-main'], LFA_VER);
  }

  // Find Us CSS will be enqueued separately with high priority (see below)

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
    
    // Enqueue Checkout CSS and JS only on checkout page
    if (is_checkout()) {
      wp_enqueue_style('lfa-checkout', LFA_URI . '/assets/css/checkout.css', $dependencies, LFA_VER);
      wp_enqueue_script('lfa-checkout', LFA_URI . '/assets/js/checkout.js', ['jquery'], LFA_VER, true);
    }
  }

  // Enqueue Single Product CSS and JS only on single product pages
  if (class_exists('WooCommerce') && is_product()) {
    wp_enqueue_style('lfa-single-product', LFA_URI . '/assets/css/single-product.css', ['lfa-main'], LFA_VER);
    wp_enqueue_script('lfa-single-product', LFA_URI . '/assets/js/single-product.js', ['jquery', 'slick-js'], LFA_VER, true);
    
    wp_add_inline_script('lfa-single-product', 'if(typeof LFA==="undefined"){var LFA=' . wp_json_encode(array('ajaxUrl'=>admin_url('admin-ajax.php'),'nonce'=>wp_create_nonce('lfa-nonce'))) . ';}', 'before');
  }
  
  // Enqueue Popup CSS and JS globally (popups can appear on any page)
  wp_enqueue_style('lfa-popups', LFA_URI . '/assets/css/popups.css', ['lfa-main'], LFA_VER);
  wp_enqueue_script('lfa-popups', LFA_URI . '/assets/js/popups.js', ['jquery'], LFA_VER, true);
  
  wp_add_inline_script('lfa-popups', 'if(typeof LFA==="undefined"){var LFA=' . wp_json_encode(array('ajaxUrl'=>admin_url('admin-ajax.php'),'nonce'=>wp_create_nonce('lfa-nonce'))) . ';}', 'before');
});

// Enqueue Find Us CSS with very high priority to load after plugin CSS
add_action('wp_enqueue_scripts', function () {
  if (is_page_template('page-find-us.php')) {
    // Get all registered styles to find plugin stylesheets
    global $wp_styles;
    $dependencies = ['lfa-main'];
    
    if (isset($wp_styles) && is_object($wp_styles)) {
      // Common ASL Store Locator plugin handles
      $asl_handles = [
        'asl-storelocator', 
        'asl-store-locator', 
        'asl-style', 
        'store-locator-style',
        'asl-frontend',
        'asl-frontend-style',
        'asl-storelocator-style'
      ];
      
      foreach ($asl_handles as $handle) {
        if (isset($wp_styles->registered[$handle])) {
          $dependencies[] = $handle;
        }
      }
      
      // Also check for any stylesheet with 'asl' or 'store' in the handle
      foreach ($wp_styles->registered as $handle => $style) {
        if (stripos($handle, 'asl') !== false || stripos($handle, 'store') !== false) {
          if (!in_array($handle, $dependencies)) {
            $dependencies[] = $handle;
          }
        }
      }
    }
    
    // Enqueue with all plugin dependencies to ensure it loads last
    wp_enqueue_style('lfa-find-us', LFA_URI . '/assets/css/find-us.css', $dependencies, LFA_VER);
  }
}, 999); // Very high priority to run after all plugins
