<?php
/*
* Template Name: Homepage
*/
/* Homepage v2 (design-matched) */
get_header();
?>
<?php $H = fn($p, $d = '') => lfa_get('home.' . $p, $d); ?>

<!-- 01. Announcement bar -->
<?php if ($txt = $H('announcement.text')): ?>
  <div class="lfa-announcement">
    <div class="container"><?php echo esc_html($txt); ?></div>
  </div>
<?php endif; ?>

<!-- 02. Hero (Revolution Slider) -->
<?php if ($sc = $H('slider_shortcode')): ?>
  <section class="lfa-hero"><?php echo do_shortcode($sc); ?></section>
<?php endif; ?>

<!-- 03. Shop by Category (4-up with centered labels) -->
<?php $cats = $H('shop_by_category.items', []);
$cats = is_array($cats) ? array_values(array_filter($cats, fn($i) => !empty($i['image']))) : [];
$cats = array_slice($cats, 0, 4); ?>
<?php if ($cats): ?>
  <section class="lfa-sbc container">
    <div class="lfa-sec-head">
      <h2><?php _e('Shop by Category', 'livingfitapparel'); ?></h2>
    </div>
    <div class="lfa-grid lfa-grid-4">
      <?php foreach ($cats as $c): $img = wp_get_attachment_image(intval($c['image']), 'large', ['class' => 'lfa-card-img']); ?>
        <a class="lfa-card lfa-card--cat" href="<?php echo esc_url($c['link'] ?? '#'); ?>">
          <?php echo $img; ?>
          <span class="lfa-chip lfa-chip--label"><?php echo esc_html($c['title'] ?? ''); ?></span>
        </a>
      <?php endforeach; ?>
    </div>
  </section>
<?php endif; ?>

<!-- 04. Featured Products (clean grid, subtle meta) -->
<?php if (class_exists('WooCommerce')): ?>
  <section class="lfa-featured container" id="lfa-featured-slider">
    <div class="lfa-sec-head">
      <h2>
        <?php _e('Featured Products', 'livingfitapparel'); ?>
      </h2>
      <?php if (function_exists('wc_get_page_id')): ?>
        <a class="lfa-viewall" href="<?php echo esc_url(get_permalink(wc_get_page_id('shop'))); ?>"><?php _e('View All', 'livingfitapparel'); ?></a>
      <?php endif; ?>
    </div>
    <?php echo do_shortcode('[products limit="' . intval($H('featured.count', 8)) . '" columns="4" visibility="featured"]'); ?>
  </section>
<?php endif; ?>



<!-- 05. Our Story (centered blurb + button) -->
<?php if ($story = $H('story.text')): ?>
  <section class="lfa-story container">
    <div class="lfa-sec-head">
      <h2><?php _e('Our Story', 'livingfitapparel'); ?></h2>
    </div>
    <p class="lfa-lead"><?php echo esc_html($story); ?></p>
    <?php if ($bt = $H('story.btn_text')): ?>
      <a class="lfa-btn lfa-btn--dark lfa-btn--story-readmore" href="<?php echo esc_url($H('story.btn_link', '#')); ?>"><?php echo esc_html($bt); ?></a>
    <?php endif; ?>
  </section>
<?php endif; ?>

<!-- 06. Shop by Color (slider with color tabs) -->
<?php if (class_exists('WooCommerce') && ($csv = trim($H('shop_by_color.terms', '')))): ?>
  <?php $terms = array_filter(array_map('trim', explode(',', $csv))); ?>
  <section class="lfa-bycolor container" id="lfa-bycolor-slider">
    <div class="lfa-sec-head">
      <h2><?php _e('Shop by color', 'livingfitapparel'); ?></h2>
      <div class="lfa-chips" data-color-tabs>
        <?php $first = true;
        foreach ($terms as $t): ?>
          <button class="lfa-chip <?php echo $first ? 'is-active' : ''; ?>" data-tab="<?php echo esc_attr($t); ?>"><?php echo esc_html(ucfirst($t)); ?></button>
        <?php $first = false;
        endforeach; ?>
      </div>
    </div>
    <?php $first = true;
    foreach ($terms as $t): ?>
      <div class="lfa-tabpanel <?php echo $first ? 'is-active' : ''; ?>" data-panel="<?php echo esc_attr($t); ?>">
        <?php echo do_shortcode('[products limit="' . intval($H('shop_by_color.count', 8)) . '" columns="4" attribute="pa_color" terms="' . esc_attr($t) . '"]'); ?>
        <div class="lfa-no-products" style="display: none;">
          <p><?php _e('No products found for this color.', 'livingfitapparel'); ?></p>
        </div>
      </div>
    <?php $first = false;
    endforeach; ?>
  </section>
<?php endif; ?>

<!-- 07. Trending banner (full-width image, no title) -->
<?php if (intval($H('banner.image'))): $src = wp_get_attachment_image_url(intval($H('banner.image')), 'full'); ?>
  <section class="lfa-trending lfa-trending--full">
    <a class="lfa-trending-full" href="<?php echo esc_url($H('banner.link', '#')); ?>">
      <img src="<?php echo esc_url($src); ?>" alt="">
    </a>
  </section>
<?php endif; ?>

<!-- 08. Find your fit (4-up grid tiles) -->
<?php $fyf = $H('find_your_fit.items', []);
$fyf = is_array($fyf) ? array_values(array_filter($fyf, fn($i) => !empty($i['image']))) : []; ?>
<?php if ($fyf): ?>
  <section class="lfa-fyf container">
    <div class="lfa-sec-head">
      <h2>
        <?php _e('Find your fit', 'livingfitapparel'); ?>
      </h2>
    </div>
    <div class="lfa-grid lfa-grid-4">
      <?php foreach ($fyf as $i): $img = wp_get_attachment_image(intval($i['image']), 'large', ['class' => 'lfa-card-img']); ?>
        <a class="lfa-card lfa-card--cat" href="<?php echo esc_url($i['link'] ?? '#'); ?>">
          <?php echo $img; ?>
          <?php if (!empty($i['title'])): ?><span class="lfa-chip lfa-chip--label"><?php echo esc_html($i['title']); ?></span><?php endif; ?>
        </a>
      <?php endforeach; ?>
    </div>
  </section>
<?php endif; ?>

<!-- 09. Customer Reviews (slider with 3 items) -->
<?php if ($ids = array_filter(array_map('absint', explode(',', $H('reviews.comment_ids', ''))))): ?>
  <section class="lfa-reviews container" id="lfa-reviews-slider">
    <div class="lfa-sec-head">
      <h2><?php _e('Customer Reviews', 'livingfitapparel'); ?></h2>
    </div>
    <div class="lfa-reviews-slider">
      <?php foreach ($ids as $cid): if (!$c = get_comment($cid)) continue;
        $rating = (int) get_comment_meta($cid, 'rating', true);
        $stars = str_repeat('★', $rating) . str_repeat('☆', max(0, 5 - $rating)); ?>
        <div class="lfa-review-slide">
          <div class="lfa-review">
            <div class="lfa-review-head">
              <div class="lfa-quote-icon"><img src="<?php echo get_template_directory_uri(); ?>/assets/images/review-quotes.svg" alt="&ldquo;" /></div>
              <div class="lfa-stars"><?php echo esc_html($stars); ?></div>
            </div>
            <blockquote class="lfa-review-content"><?php echo esc_html(wp_trim_words($c->comment_content, 40)); ?></blockquote>
            <div class="lfa-review-author"><?php 
              $author = esc_html($c->comment_author);
              $maxLength = 20; // Adjust this value as needed
              if (mb_strlen($author) > $maxLength) {
                echo mb_substr($author, 0, $maxLength) . '...';
              } else {
                echo $author;
              }
            ?></div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
    <div class="lfa-reviews-controls">
      <button class="lfa-reviews-prev" type="button" aria-label="<?php esc_attr_e('Previous', 'livingfitapparel'); ?>">
        <img src="<?php echo get_template_directory_uri(); ?>/assets/images/arrow-left_reviews.svg" alt="<?php esc_attr_e('Previous', 'livingfitapparel'); ?>" />
      </button>
      <button class="lfa-reviews-next" type="button" aria-label="<?php esc_attr_e('Next', 'livingfitapparel'); ?>">
        <img src="<?php echo get_template_directory_uri(); ?>/assets/images/arrow-right_reviews.svg" alt="<?php esc_attr_e('Next', 'livingfitapparel'); ?>" />
      </button>
    </div>
  </section>
<?php endif; ?>

<!-- 10. Our Community (horizontal reels slider) -->
<?php if ($reels = trim($H('instagram.reel_urls', ''))): $urls = array_slice(array_filter(array_map('trim', preg_split('/\r\n|\r|\n/', $reels))), 0, 10); ?>
  <section class="lfa-community container">
    <div class="lfa-sec-head">
      <h2><?php _e('Our Community', 'livingfitapparel'); ?></h2>
      <div class="lfa-subhead"><?php echo esc_html__('Everybody’s everyday uniform', 'livingfitapparel'); ?></div>
    </div>
    <div class="lfa-ugc" data-carousel>
      <?php foreach ($urls as $u):
        $embed = wp_oembed_get($u, ['width' => 320]); ?>
        <a class="lfa-ugc-item" href="<?php echo esc_url($u); ?>" target="_blank" rel="noopener">
          <div class="lfa-ugc-embed"><?php echo $embed ?: ''; ?></div>
          <span class="lfa-ugc-play">▶</span>
        </a>
      <?php endforeach; ?>
    </div>
    <div class="lfa-carousel-controls"><button class="lfa-prev" type="button">‹</button><button class="lfa-next" type="button">›</button></div>
  </section>
<?php endif; ?>

<!-- 11. Footer block moved to global footer -->

<?php get_footer(); ?>