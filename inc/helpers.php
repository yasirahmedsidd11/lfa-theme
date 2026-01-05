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

/** Get color hex code from color name (fallback helper) */
function lfa_get_color_hex_from_name($color_name) {
  $color_name = strtolower(trim($color_name));
  $color_map = array(
    'white' => '#ffffff',
    'black' => '#000000',
    'grey' => '#808080',
    'gray' => '#808080',
    'red' => '#ff0000',
    'blue' => '#0000ff',
    'green' => '#008000',
    'yellow' => '#ffff00',
    'orange' => '#ffa500',
    'purple' => '#800080',
    'pink' => '#ffc0cb',
    'brown' => '#a52a2a',
    'navy' => '#000080',
    'maroon' => '#800000',
    'olive' => '#808000',
    'lime' => '#00ff00',
    'aqua' => '#00ffff',
    'teal' => '#008080',
    'silver' => '#c0c0c0',
    'gold' => '#ffd700',
    'beige' => '#f5f5dc',
    'ivory' => '#fffff0',
    'khaki' => '#f0e68c',
    'coral' => '#ff7f50',
    'salmon' => '#fa8072',
    'tan' => '#d2b48c',
    'charcoal' => '#36454f',
    'graphite' => '#251607',
    'marshmallow' => '#fffef7',
  );
  
  // Check exact match
  if (isset($color_map[$color_name])) {
    return $color_map[$color_name];
  }
  
  // Check partial matches (e.g., "dark grey" contains "grey")
  foreach ($color_map as $key => $hex) {
    if (strpos($color_name, $key) !== false) {
      return $hex;
    }
  }
  
  // Default fallback
  return '#cccccc';
}