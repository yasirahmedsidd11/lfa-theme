<?php
if ( ! defined('ABSPATH') ) exit;

// Load theme text domain for translations
add_action('after_setup_theme', function() {
  load_theme_textdomain('livingfitapparel', get_template_directory() . '/languages');
});

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
  return $market['default_lang'];
}

// Set WordPress locale early
add_filter('locale', function($locale){
  $wp_locale = lfa_normalize_locale( lfa_current_language_code() );
  return $wp_locale ?: $locale;
}, 1);

// Add RTL support - ensure HTML dir attribute is set correctly
add_filter('language_attributes', function($output) {
  $lang_code = lfa_current_language_code();
  // Check if language is RTL (Arabic, Urdu, Hebrew, etc.)
  $rtl_languages = ['ar', 'ar_AE', 'ur', 'ur_PK', 'he', 'fa', 'ku'];
  $is_rtl = false;
  foreach ($rtl_languages as $rtl_lang) {
    if (stripos($lang_code, $rtl_lang) === 0) {
      $is_rtl = true;
      break;
    }
  }
  
  if ($is_rtl) {
    // Add dir="rtl" if not already present
    if (strpos($output, 'dir=') === false) {
      $output .= ' dir="rtl"';
    } else {
      // Replace existing dir attribute
      $output = preg_replace('/dir="[^"]*"/', 'dir="rtl"', $output);
    }
  } else {
    // Ensure dir="ltr" for LTR languages
    if (strpos($output, 'dir=') === false) {
      $output .= ' dir="ltr"';
    } else {
      $output = preg_replace('/dir="[^"]*"/', 'dir="ltr"', $output);
    }
  }
  
  return $output;
}, 10);

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
