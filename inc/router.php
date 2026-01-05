<?php
if ( ! defined('ABSPATH') ) exit;

// Set default country only if not set, and run after WooCommerce processes shipping calculator
// Use a later priority to ensure it runs after shipping calculator has processed
add_action('woocommerce_cart_loaded_from_session', function(){
  // Don't override if shipping calculator form was just submitted
  if (isset($_POST['calc_shipping']) && isset($_POST['calc_shipping_country'])) {
    return; // Let WooCommerce handle the form submission
  }
  
  if (function_exists('WC') && WC()->customer) {
    $market = lfa_current_market();
    if (!empty($market['country'])) {
      // Only set billing country if not already set
      $billing_country = WC()->customer->get_billing_country();
      if (empty($billing_country) || $billing_country === '') {
        WC()->customer->set_billing_country($market['country']);
      }
      // Only set shipping country if not already set (don't override user's selection)
      // Check if shipping country exists and is not empty
      $shipping_country = WC()->customer->get_shipping_country();
      // Only set default if shipping country is truly empty (not set by user)
      // Also check if it's not "default" which is the placeholder value
      if ((empty($shipping_country) || $shipping_country === '' || $shipping_country === null) && $shipping_country !== 'default') {
        WC()->customer->set_shipping_country($market['country']);
      }
    }
  }
}, 20); // Higher priority to run after shipping calculator processes

// RTL stylesheet for Arabic
add_filter('locale_stylesheet', function($stylesheet){
  $lang = lfa_current_language_code();
  if (stripos($lang, 'ar') === 0) return 'rtl.css';
  return $stylesheet;
});

// Main domain selector redirect → subdomain
add_action('template_redirect', function(){
  $host = $_SERVER['HTTP_HOST'] ?? '';
  $is_main = (stripos($host, 'livingfitapparel.com') === strlen($host) - strlen('livingfitapparel.com'))
          || (stripos($host, 'www.livingfitapparel.com') === 0);

  $is_sub = (stripos($host, 'pk.') === 0) || (stripos($host, 'uae.') === 0) || (stripos($host, 'global.') === 0);

  if ($is_main && !$is_sub && !empty($_GET['market'])) {
    $m = sanitize_text_field($_GET['market']);
    $url = lfa_market_url($m);
    if ($url && !headers_sent()) {
      wp_safe_redirect($url);
      exit;
    }
  }
});

// Optional currency symbol overrides
add_filter('woocommerce_currency_symbol', function($symbol, $currency){
  switch ($currency) {
    case 'PKR': return '₨';
    case 'AED': return 'د.إ';
    case 'USD': return '$';
  }
  return $symbol;
}, 10, 2);
