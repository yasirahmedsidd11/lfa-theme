<?php
if ( ! defined('ABSPATH') ) exit;

function lfa_markets_registry() {
  return [
    'pk' => [
      'label'         => 'Pakistan',
      'domain'        => 'pk.livingfitapparel.com',
      'currency'      => 'PKR',
      'default_lang'  => 'en_PK',
      'languages'     => ['en_PK','ur_PK'],
      'country'       => 'PK'
    ],
    'uae' => [
      'label'         => 'United Arab Emirates',
      'domain'        => 'uae.livingfitapparel.com',
      'currency'      => 'AED',
      'default_lang'  => 'en_AE',
      'languages'     => ['en_AE','ar_AE'],
      'country'       => 'AE'
    ],
    'global' => [
      'label'         => 'Global',
      'domain'        => 'global.livingfitapparel.com',
      'currency'      => 'USD',
      'default_lang'  => 'en_US',
      'languages'     => ['en_US'],
      'country'       => 'US'
    ],
  ];
}

function lfa_normalize_locale($code) {
  $map = [
    'en_PK' => 'en_US',
    'en_AE' => 'en_US',
    'ar_AE' => 'ar',
    'ur_PK' => 'ur',
    'en_US' => 'en_US'
  ];
  return $map[$code] ?? 'en_US';
}

function lfa_current_market_key() {
  $host = $_SERVER['HTTP_HOST'] ?? '';

  if (stripos($host,'pk.') === 0)    return 'pk';
  if (stripos($host,'uae.') === 0)   return 'uae';
  if (stripos($host,'global.') === 0)return 'global';

  if (!empty($_COOKIE['lfa_market'])) {
    $m = sanitize_text_field($_COOKIE['lfa_market']);
    $reg = lfa_markets_registry();
    if (isset($reg[$m])) return $m;
  }
  return 'global';
}

function lfa_current_market() {
  $reg = lfa_markets_registry();
  $key = lfa_current_market_key();
  return $reg[$key] ?? $reg['global'];
}

function lfa_market_url($market_key, $path = null) {
  $reg = lfa_markets_registry();
  if (!isset($reg[$market_key])) return home_url('/');
  $domain = $reg[$market_key]['domain'];
  if ($path === null) $path = $_SERVER['REQUEST_URI'] ?? '/';
  $scheme = is_ssl() ? 'https://' : 'http://';
  return $scheme . $domain . $path;
}
