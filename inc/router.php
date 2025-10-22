<?php
if ( ! defined('ABSPATH') ) exit;

// Woo currency per market
add_filter('woocommerce_currency', function($currency){
  $market = lfa_current_market();
  return $market['currency'] ?? $currency;
});

// Store/customer country per market
add_filter('pre_option_woocommerce_default_country', function($value){
  $market = lfa_current_market();
  return $market['country'] ?? $value;
});
add_action('init', function(){
  if (function_exists('WC') && WC()->customer) {
    $market = lfa_current_market();
    if (!empty($market['country'])) {
      WC()->customer->set_billing_country($market['country']);
      WC()->customer->set_shipping_country($market['country']);
    }
  }
});

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
