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
        <a class="hdr-icon hdr-cart" href="<?php echo esc_url(wc_get_cart_url()); ?>" aria-label="<?php esc_attr_e('Cart','livingfitapparel'); ?>">
          <!-- bag -->
          <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor"><path d="M6 7h12l-1 13H7L6 7z"/><path d="M9 7a3 3 0 0 1 6 0"/></svg>
          <span class="hdr-cart-badge"><?php echo WC()->cart ? WC()->cart->get_cart_contents_count() : 0; ?></span>
        </a>
      <?php endif; ?>
    </div>

  </div>

  <!-- Mega menu: Shop -->
  <?php if (class_exists('WooCommerce')): ?>
  <?php
    $left_terms = get_terms(['taxonomy'=>'product_cat','hide_empty'=>false,'parent'=>0]);
    $clothing = get_term_by('slug','clothing','product_cat');
    $accessories = get_term_by('slug','accessories','product_cat');
    $clothing_terms = $clothing ? get_terms(['taxonomy'=>'product_cat','hide_empty'=>false,'parent'=>$clothing->term_id]) : [];
    $accessories_terms = $accessories ? get_terms(['taxonomy'=>'product_cat','hide_empty'=>false,'parent'=>$accessories->term_id]) : [];
    $mmimg = intval(lfa_get('shop.megamenu.image'));
  ?>
  <div class="lfa-mega" data-mega-panel="shop" hidden>
    <div class="container lfa-mega-inner">
      <div class="lfa-mega-col lfa-mega-col--left">
        <ul class="lfa-mega-list">
          <?php foreach ($left_terms as $t): ?>
            <li><a href="<?php echo esc_url(get_term_link($t)); ?>"><?php echo esc_html($t->name); ?></a></li>
          <?php endforeach; ?>
        </ul>
      </div>
      <div class="lfa-mega-col">
        <div class="lfa-mega-title"><?php esc_html_e('CLOTHING','livingfitapparel'); ?></div>
        <ul class="lfa-mega-list">
          <?php foreach ($clothing_terms as $t): ?>
            <li><a href="<?php echo esc_url(get_term_link($t)); ?>"><?php echo esc_html($t->name); ?></a></li>
          <?php endforeach; ?>
        </ul>
      </div>
      <div class="lfa-mega-col">
        <div class="lfa-mega-title"><?php esc_html_e('ACCESSORIES','livingfitapparel'); ?></div>
        <ul class="lfa-mega-list">
          <?php foreach ($accessories_terms as $t): ?>
            <li><a href="<?php echo esc_url(get_term_link($t)); ?>"><?php echo esc_html($t->name); ?></a></li>
          <?php endforeach; ?>
        </ul>
      </div>
      <div class="lfa-mega-col lfa-mega-col--image">
        <?php if ($mmimg) echo wp_get_attachment_image($mmimg,'large'); ?>
      </div>
    </div>
  </div>
  <?php endif; ?>

    <!-- Search drawer trigger remains the same (class js-open-search on the icon) -->

      <!-- Drawer + dim layer -->
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

</header>

<main class="site-main">
