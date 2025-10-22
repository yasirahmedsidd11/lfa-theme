<?php
if ( ! defined( 'ABSPATH' ) ) exit;

function lfa_get_option($key, $default = '') {
  $opts = get_option('lfa_options', []);
  return isset($opts[$key]) ? $opts[$key] : $default;
}

/** Dot-path getter for nested arrays in lfa_options, e.g. lfa_get('home.announcement.text') */
function lfa_get($path, $default = '') {
  $opts = get_option('lfa_options', []);
  $keys = explode('.', $path);
  $val = $opts;
  foreach ($keys as $k) {
    if (!is_array($val) || !array_key_exists($k, $val)) return $default;
    $val = $val[$k];
  }
  return $val === '' ? $default : $val;
}

/** Output esc_html quickly */
function lfa_e($str){ echo esc_html($str); }

/** Media field preview (admin) */
function lfa_media_preview($id) {
  $src = $id ? wp_get_attachment_image_url($id, 'medium') : '';
  if ($src) echo '<img src="'.esc_url($src).'" alt="" style="max-width:120px;border:1px solid #eee;border-radius:6px;">';
}
