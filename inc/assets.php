<?php
if ( ! defined( 'ABSPATH' ) ) exit;

add_action('wp_enqueue_scripts', function () {
  // Frontend fonts
  wp_enqueue_style('lfa-fonts', 'https://fonts.googleapis.com/css2?family=Questrial&display=swap', [], null);
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

  wp_enqueue_script('lfa-main', LFA_URI . '/assets/js/main.js', ['jquery'], LFA_VER, true);
  wp_enqueue_script('lfa-markets', LFA_URI . '/assets/js/markets.js', [], LFA_VER, true);

  wp_localize_script('lfa-main', 'LFA', [
    'ajaxUrl' => admin_url('admin-ajax.php'),
    'nonce'   => wp_create_nonce('lfa-nonce'),
  ]);
});
