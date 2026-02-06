<?php
if ( ! defined('ABSPATH') ) exit;

function lfa_current_language_code() {
  $market = lfa_current_market();

  if (!empty($_GET['lang'])) {
    $lang = sanitize_text_field($_GET['lang']);
    if (in_array($lang, $market['languages'], true)) return $lang;
  }
  if (!empty($_COOKIE['lfa_lang'])) {
    $lang = sanitize_text_field($_COOKIE['lfa_lang']);
    if (in_array($lang, $market['languages'], true)) return $lang;
  }
  // No theme override: use WordPress dashboard language (Settings → General)
  $wp_locale = get_option('WPLANG');
  if (empty($wp_locale)) {
    $wp_locale = 'en_US';
  }
  foreach ($market['languages'] as $code) {
    if (lfa_normalize_locale($code) === $wp_locale) {
      return $code;
    }
  }
  return $market['default_lang'];
}

// Set WordPress locale: respect dashboard language when no URL/cookie override; never override in admin
add_filter('locale', function($locale) {
  if (is_admin()) {
    return $locale;
  }
  if (!empty($_GET['lang']) || !empty($_COOKIE['lfa_lang'])) {
    $wp_locale = lfa_normalize_locale(lfa_current_language_code());
    return $wp_locale ?: $locale;
  }
  return $locale;
}, 1);

// Persist selection via query
add_action('init', function(){
  if (!headers_sent() && !empty($_GET['lang'])) {
    $lang = sanitize_text_field($_GET['lang']);
    $market = lfa_current_market();
    if (in_array($lang, $market['languages'], true)) {
      setcookie('lfa_lang', $lang, time()+3600*24*365, '/', '.livingfitapparel.com', is_ssl(), true);
    }
  }
  if (!headers_sent() && !empty($_GET['market'])) {
    $m = sanitize_text_field($_GET['market']);
    $reg = lfa_markets_registry();
    if (isset($reg[$m])) {
      setcookie('lfa_market', $m, time()+3600*24*365, '/', '.livingfitapparel.com', is_ssl(), true);
    }
  }
});
