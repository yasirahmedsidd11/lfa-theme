<?php
if ( ! defined('ABSPATH') ) exit;

/**
 * AJAX: product search
 * POST/GET: q (string), page (int)
 * Returns JSON { html, next } where html is result items markup.
 */
add_action('wp_ajax_lfa_search', 'lfa_ajax_search');
add_action('wp_ajax_nopriv_lfa_search', 'lfa_ajax_search');

function lfa_ajax_search() {
  // Basic nonce is optional here since this is read-only search, but check if provided
  if ( isset($_REQUEST['nonce']) && ! wp_verify_nonce( $_REQUEST['nonce'], 'lfa-nonce' ) ) {
    wp_send_json_error(['message'=>'bad nonce'], 403);
  }

  $q    = isset($_REQUEST['q']) ? sanitize_text_field( wp_unslash($_REQUEST['q']) ) : '';
  $page = max(1, intval($_REQUEST['page'] ?? 1));
  $ppp  = 10;

  // Build query
  $args = [
    'post_type'      => 'product',
    'post_status'    => 'publish',
    's'              => $q,
    'paged'          => $page,
    'posts_per_page' => $ppp,
    'meta_query'     => WC()->query->get_meta_query(),
    'tax_query'      => WC()->query->get_tax_query(),
  ];

  // If empty query => show Featured (Trending)
  if ($q === '') {
    $args['tax_query'][] = [
      'taxonomy' => 'product_visibility',
      'field'    => 'name',
      'terms'    => ['featured'],
      'operator' => 'IN',
    ];
  }

  $qobj = new WP_Query($args);

  ob_start();
  if ( $qobj->have_posts() ) {
    echo '<div class="lfa-sr-grid">';
    while ( $qobj->have_posts() ) { $qobj->the_post();
      $product = wc_get_product( get_the_ID() );
      ?>
      <a class="lfa-sr-item" href="<?php the_permalink(); ?>">
        <div class="lfa-sr-thumb">
          <?php echo $product->get_image( 'woocommerce_thumbnail' ); ?>
        </div>
        <div class="lfa-sr-meta">
          <div class="lfa-sr-title"><?php echo esc_html( wp_trim_words( get_the_title(), 12 ) ); ?></div>
          <div class="lfa-sr-price"><?php echo $product->get_price_html(); ?></div>
        </div>
      </a>
      <?php
    }
    echo '</div>';
    wp_reset_postdata();
  } else {
    ?>
    <div class="lfa-sr-empty">
      <?php esc_html_e('No products found. Try a different keyword.', 'livingfitapparel'); ?>
    </div>
    <?php
  }
  $html = ob_get_clean();

  $next = $qobj->max_num_pages > $page;

  wp_send_json_success([
    'html' => $html,
    'next' => $next,
    'page' => $page,
  ]);
}
