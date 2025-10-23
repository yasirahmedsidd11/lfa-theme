<?php
if ( ! defined( 'ABSPATH' ) ) exit;

add_action('after_setup_theme', function () {
  add_theme_support('title-tag');
  add_theme_support('post-thumbnails');
  add_theme_support('automatic-feed-links');
  add_theme_support('responsive-embeds');
  add_theme_support('wp-block-styles');
  add_theme_support('editor-styles');
  add_editor_style('assets/css/main.css');

  // WooCommerce
  add_theme_support('woocommerce', [
    'thumbnail_image_width' => 420,
    'single_image_width'    => 720,
    'product_grid'          => [
      'default_rows' => 3, 'min_rows' => 1, 'max_rows' => 10,
      'default_columns' => 4, 'min_columns' => 2, 'max_columns' => 6
    ]
  ]);
  add_theme_support('wc-product-gallery-zoom');
  add_theme_support('wc-product-gallery-lightbox');
  add_theme_support('wc-product-gallery-slider');

  register_nav_menus([
    'primary' => __('Primary Menu', 'livingfitapparel'),
    'footer'  => __('Footer Menu', 'livingfitapparel'),
  ]);

  register_sidebar([
    'name' => __('Shop Sidebar', 'livingfitapparel'),
    'id'   => 'shop-sidebar',
    'before_widget' => '<section id="%1$s" class="widget %2$s">',
    'after_widget'  => '</section>',
    'before_title'  => '<h3 class="widget-title">',
    'after_title'   => '</h3>',
  ]);
});

// Add data attribute to menu link when menu item has CSS class 'mega-shop'
add_filter('nav_menu_link_attributes', function($atts, $item, $args){
  if (!empty($item->classes) && is_array($item->classes) && in_array('mega-shop', $item->classes, true)) {
    $atts['data-mega'] = 'shop';
    $atts['aria-haspopup'] = 'true';
    $atts['aria-expanded'] = 'false';
  }
  return $atts;
}, 10, 3);

// Replace WooCommerce default column classes with custom grid classes
add_filter('woocommerce_product_loop_start', function($html) {
  // Replace column-4 with grid-4
  $html = str_replace('columns-4', 'lfa-grid lfa-grid-4', $html);
  $html = str_replace('column-4', 'lfa-grid lfa-grid-4', $html);
  return $html;
});