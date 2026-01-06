<?php if ( ! defined( 'ABSPATH' ) ) exit; ?><!doctype html>
<html <?php language_attributes(); ?>>
<head>
  <meta charset="<?php bloginfo('charset'); ?>">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <?php if ($fav = intval(lfa_get('general.favicon_id'))): $fav_url = wp_get_attachment_image_url($fav,'full'); if ($fav_url): ?>
  <link rel="icon" href="<?php echo esc_url($fav_url); ?>" sizes="32x32">
  <?php endif; endif; ?>
  <?php wp_head(); ?>
</head>
<?php
$layout = lfa_get_option('header_layout','logo-center-split');
$sticky = lfa_get_option('sticky_header') ? ' lfa-sticky' : '';
$h = fn($p,$d='') => lfa_get('header.'.$p,$d);
?>
<body <?php body_class(); ?>>

<?php if ($txt = $h('announce_text')): ?>
  <div class="lfa-announcement" style="background:<?php echo esc_attr($h('announce_bg','#F6F4EF')); ?>">
    <div class="container">
      <?php if ($link = $h('announce_link')): ?>
        <a href="<?php echo esc_url($link); ?>"><?php echo esc_html($txt); ?></a>
      <?php else: ?>
        <?php echo esc_html($txt); ?>
      <?php endif; ?>
    </div>
  </div>
<?php endif; ?>

<header class="site-header <?php echo esc_attr($layout.$sticky); ?>">
  <div class="container lfa-hdr-row">

    <!-- Left: primary nav -->
    <nav class="primary-nav" aria-label="<?php esc_attr_e('Primary','livingfitapparel'); ?>">
      <button class="lfa-burger" aria-label="Open menu">
        <span></span><span></span><span></span>
      </button>
      <div class="lfa-nav-inner">
        <?php
          // Use Header tab override menu if set; else Primary
          $hdr_menu_id = intval(lfa_get('header.menu_id', lfa_get('home.menu_id')));
          if ($hdr_menu_id) {
            wp_nav_menu(['menu'=>$hdr_menu_id,'container'=>false,'menu_class'=>'lfa-menu','fallback_cb'=>'__return_false']);
          } else {
            wp_nav_menu(['theme_location'=>'primary','container'=>false,'menu_class'=>'lfa-menu','fallback_cb'=>'__return_false']);
          }
        ?>
      </div>
    </nav>

    <!-- Center: brand -->
    <div class="brand">
      <a href="<?php echo esc_url(home_url('/')); ?>" class="brand-link" aria-label="<?php bloginfo('name'); ?>">
        <?php
          $site_logo_id = lfa_get('general.logo_id', lfa_get('home.logo_id'));
          $h_logo_h = intval(lfa_get('header.logo_h',34));
          if ($site_logo_id) {
            echo wp_get_attachment_image($site_logo_id, 'medium', false, ['style'=>"max-height:{$h_logo_h}px;height:auto;width:auto"]);
          } elseif ( has_custom_logo() ) {
            the_custom_logo();
          } else {
            bloginfo('name');
          }
        ?>
      </a>
    </div>

    <!-- Right: icons -->
    <div class="hdr-icons">
      <?php if ($h('show_search')): ?>
        <a href="#" class="hdr-icon js-open-search" aria-label="<?php esc_attr_e('Search','livingfitapparel'); ?>">
          <!-- magnifier -->
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor"><circle cx="11" cy="11" r="7"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
        </a>
      <?php endif; ?>

      <?php if ($h('show_wishlist')): ?>
        <a href="<?php echo esc_url($h('wishlist_url','/wishlist/')); ?>" class="hdr-icon" aria-label="<?php esc_attr_e('Wishlist','livingfitapparel'); ?>">
          <!-- heart -->
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M20.8 4.6a5.5 5.5 0 0 0-7.8 0L12 5.6l-1-1a5.5 5.5 0 1 0-7.8 7.8l1 1L12 22l7.8-8.6 1-1a5.5 5.5 0 0 0 0-7.8z"/></svg>
        </a>
      <?php endif; ?>

      <?php if ($h('show_cart',1) && class_exists('WooCommerce')): ?>
        <button class="hdr-icon hdr-cart js-open-cart-drawer" type="button" aria-label="<?php esc_attr_e('Cart','livingfitapparel'); ?>">
          <!-- bag -->
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M6 7h12l-1 13H7L6 7z"/><path d="M9 7a3 3 0 0 1 6 0"/></svg>
          <span class="hdr-cart-badge"><?php echo WC()->cart ? WC()->cart->get_cart_contents_count() : 0; ?></span>
        </button>
      <?php endif; ?>
    </div>

  </div>

  <!-- Mega menu: General (for menu items with mega-menu class) -->
  <?php
    // Get mega menu settings
    $col1_title = lfa_get('header.megamenu.col1.title');
    $col1_cats = lfa_get('header.megamenu.col1.category_ids');
    $col2_title = lfa_get('header.megamenu.col2.title');
    $col2_cats = lfa_get('header.megamenu.col2.category_ids');
    $col3_title = lfa_get('header.megamenu.col3.title');
    $col3_cats = lfa_get('header.megamenu.col3.category_ids');
    $col4_image = intval(lfa_get('header.megamenu.col4.image'));

    // Helper function to get category links from comma-separated IDs
    if (!function_exists('lfa_get_category_links')) {
      function lfa_get_category_links($category_ids_str) {
        if (empty($category_ids_str) || !class_exists('WooCommerce')) {
          return [];
        }
        $ids = array_map('trim', explode(',', $category_ids_str));
        $ids = array_filter(array_map('intval', $ids));
        if (empty($ids)) {
          return [];
        }
        $terms = get_terms([
          'taxonomy' => 'product_cat',
          'include' => $ids,
          'hide_empty' => false,
          'orderby' => 'include',
        ]);
        if (is_wp_error($terms) || empty($terms)) {
          return [];
        }
        return $terms;
      }
    }
  ?>
  <div class="lfa-mega" data-mega-panel="menu" hidden>
    <div class="container lfa-mega-inner">
      <?php if (!empty($col1_cats)): ?>
        <?php $col1_terms = lfa_get_category_links($col1_cats); ?>
        <?php if (!empty($col1_terms)): ?>
          <div class="lfa-mega-col lfa-mega-col--left">
            <?php if (!empty($col1_title)): ?>
              <div class="lfa-mega-title"><?php echo esc_html($col1_title); ?></div>
            <?php endif; ?>
            <ul class="lfa-mega-list">
              <?php foreach ($col1_terms as $term): ?>
                <li><a href="<?php echo esc_url(get_term_link($term)); ?>"><?php echo esc_html($term->name); ?></a></li>
              <?php endforeach; ?>
            </ul>
          </div>
        <?php endif; ?>
      <?php endif; ?>

      <?php if (!empty($col2_cats)): ?>
        <?php $col2_terms = lfa_get_category_links($col2_cats); ?>
        <?php if (!empty($col2_terms)): ?>
          <div class="lfa-mega-col">
            <?php if (!empty($col2_title)): ?>
              <div class="lfa-mega-title"><?php echo esc_html($col2_title); ?></div>
            <?php endif; ?>
            <ul class="lfa-mega-list">
              <?php foreach ($col2_terms as $term): ?>
                <li><a href="<?php echo esc_url(get_term_link($term)); ?>"><?php echo esc_html($term->name); ?></a></li>
              <?php endforeach; ?>
            </ul>
          </div>
        <?php endif; ?>
      <?php endif; ?>

      <?php if (!empty($col3_cats)): ?>
        <?php $col3_terms = lfa_get_category_links($col3_cats); ?>
        <?php if (!empty($col3_terms)): ?>
          <div class="lfa-mega-col">
            <?php if (!empty($col3_title)): ?>
              <div class="lfa-mega-title"><?php echo esc_html($col3_title); ?></div>
            <?php endif; ?>
            <ul class="lfa-mega-list">
              <?php foreach ($col3_terms as $term): ?>
                <li><a href="<?php echo esc_url(get_term_link($term)); ?>"><?php echo esc_html($term->name); ?></a></li>
              <?php endforeach; ?>
            </ul>
          </div>
        <?php endif; ?>
      <?php endif; ?>

      <?php if ($col4_image): ?>
        <div class="lfa-mega-col lfa-mega-col--image">
          <?php echo wp_get_attachment_image($col4_image, 'large'); ?>
        </div>
      <?php endif; ?>
    </div>
  </div>

    <!-- Search drawer trigger remains the same (class js-open-search on the icon) -->

      <!-- Search Drawer + dim layer -->
      <div class="lfa-search-dim" data-search-dim hidden></div>
      <aside class="lfa-search-drawer" data-search-drawer hidden>
        <div class="lfa-search-head">
          <div class="lfa-search-input">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor"><circle cx="11" cy="11" r="7"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
            <input type="search" placeholder="<?php esc_attr_e('Search products…','livingfitapparel'); ?>" autocomplete="off" data-search-input>
          </div>
          <button class="lfa-search-close" type="button" title="<?php esc_attr_e('Close','livingfitapparel'); ?>" data-search-close>×</button>
        </div>
    
        <div class="lfa-search-body" data-search-body>
          <div class="lfa-search-section" data-search-section>
            <div class="lfa-search-section-title" data-search-title>
              <?php esc_html_e('TRENDING PRODUCTS','livingfitapparel'); ?>
            </div>
            <div class="lfa-search-results" data-search-results>
              <!-- AJAX HTML injects here -->
            </div>
            <button class="lfa-load-more" data-search-more hidden><?php esc_html_e('Load more','livingfitapparel'); ?></button>
          </div>
        </div>
      </aside>

      <!-- Cart Drawer + dim layer -->
      <div class="lfa-cart-dim" data-cart-dim hidden></div>
      <aside class="lfa-cart-drawer" data-cart-drawer hidden>
        <div class="lfa-cart-drawer-inner">
          <div class="lfa-cart-drawer-content" data-cart-drawer-content>
            <!-- Cart content will be loaded here via AJAX -->
            <div class="lfa-cart-drawer-loading">
              <span><?php esc_html_e('Loading cart...', 'livingfitapparel'); ?></span>
            </div>
          </div>
        </div>
        <button class="lfa-cart-drawer-close" type="button" title="<?php esc_attr_e('Close','livingfitapparel'); ?>" data-cart-drawer-close>×</button>
      </aside>

</header>

<main class="site-main">
