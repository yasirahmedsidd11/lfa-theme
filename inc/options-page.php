<?php
if ( ! defined( 'ABSPATH' ) ) exit;

add_action('admin_menu', function () {
  add_menu_page(
    __('LivingFit Apparel', 'livingfitapparel'),
    __('LivingFit Apparel', 'livingfitapparel'),
    'manage_options',
    'lfa-theme',
    'lfa_theme_dashboard',
    'dashicons-store',
    59
  );
});

add_action('admin_init', function () {
  register_setting('lfa_options_group', 'lfa_options', [
    'type' => 'array',
    'sanitize_callback' => function($input){
      // We allow nested arrays (home.*). Basic sanitize.
      if (!is_array($input)) return [];
      
      // Get current saved options to merge with
      $current = get_option('lfa_options', []);
      
      // Handle checkboxes - if not in input, set to empty (unchecked)
      $checkbox_keys = ['quick_view', 'wishlist', 'show_colors', 'show_new_tag', 'show_sale_tag', 'show_sale_percentage', 'catalog_mode', 'sticky_header', 'performance_dequeue_blocks'];
      foreach ($checkbox_keys as $key) {
        if (!isset($input[$key])) {
          $input[$key] = '';
        }
      }
      
      // Merge with current to preserve nested values that might not be in this form submission
      $merged = array_merge($current, $input);
      
      // Allow HTML for footer.big_text and 404.description fields - sanitize them separately
      $footer_big_text = null;
      if (isset($merged['footer']['big_text']) && is_string($merged['footer']['big_text'])) {
        $footer_big_text = wp_kses_post($merged['footer']['big_text']);
        $merged['footer']['big_text'] = ''; // Temporarily clear to avoid double sanitization
      }
      
      $error_404_description = null;
      if (isset($merged['404']['description']) && is_string($merged['404']['description'])) {
        $error_404_description = wp_kses_post($merged['404']['description']);
        $merged['404']['description'] = ''; // Temporarily clear to avoid double sanitization
      }
      
      // Sanitize all other fields
      array_walk_recursive($merged, function (&$v){ 
        if (is_string($v)) {
          $v = sanitize_text_field($v);
        }
      });
      
      // Restore fields with HTML allowed
      if ($footer_big_text !== null) {
        $merged['footer']['big_text'] = $footer_big_text;
      }
      if ($error_404_description !== null) {
        $merged['404']['description'] = $error_404_description;
      }
      // Integers for attachment IDs
      $int_keys = [
        'home'=>[
          'logo_id','banner.image','footer.payment_image'
        ]
      ];
      // (Light-touch: attachment IDs will also be sent as strings; it's OK.)
      return $merged;
    }
  ]);
});

/** Admin assets for media uploader */
add_action('admin_enqueue_scripts', function($hook){
  if ($hook !== 'toplevel_page_lfa-theme') return;
  wp_enqueue_media();
  
  wp_add_inline_script('jquery-core', "
    jQuery(function($){
      function bindMedia(btnSel){
        $(document).on('click', btnSel, function(e){
          e.preventDefault();
          var btn = $(this);
          var target = $('#'+btn.data('target'));
          var imgWrap = $('#'+btn.data('preview'));
          var frame = wp.media({title:'Select image', multiple:false});
          frame.on('select', function(){
            var at = frame.state().get('selection').first().toJSON();
            target.val(at.id);
            if (imgWrap.length) imgWrap.html('<img src=\"'+at.sizes.medium.url+'\" style=\"max-width:120px;border:1px solid #eee;border-radius:6px;\"/>');
          });
          frame.open();
        });
      }
      bindMedia('.lfa-media-btn');

      // Simple repeater add/remove
      $(document).on('click','.lfa-add-row',function(e){
        e.preventDefault();
        var tpl = $($(this).data('template')).html();
        $($(this).data('append')).append(tpl);
      });
      $(document).on('click','.lfa-remove-row',function(e){
        e.preventDefault();
        $(this).closest('.lfa-row').remove();
      });
      
    });
  ");
});

function lfa_theme_dashboard() {
  $opts = get_option('lfa_options', []);
  $menus = wp_get_nav_menus();
  ?>
  <div class="wrap lfa-admin">
    <h1><?php _e('LivingFit Apparel — Theme Dashboard', 'livingfitapparel'); ?></h1>

    <div class="lfa-settings">
      <aside class="lfa-sidebar" aria-label="<?php esc_attr_e('Settings sections', 'livingfitapparel'); ?>">
        <a href="#general" class="lfa-tab-link is-active"><?php _e('General', 'livingfitapparel'); ?></a>
        <a href="#header" class="lfa-tab-link"><?php _e('Header', 'livingfitapparel'); ?></a>
        <a href="#footer" class="lfa-tab-link"><?php _e('Footer', 'livingfitapparel'); ?></a>
        <a href="#home" class="lfa-tab-link"><?php _e('Home', 'livingfitapparel'); ?></a>
        <a href="#shop" class="lfa-tab-link"><?php _e('Shop', 'livingfitapparel'); ?></a>
        <a href="#404" class="lfa-tab-link"><?php _e('404 Page', 'livingfitapparel'); ?></a>
        <a href="#perf" class="lfa-tab-link"><?php _e('Performance', 'livingfitapparel'); ?></a>
      </aside>

      <div class="lfa-panels">
    <form method="post" action="options.php">
      <?php settings_fields('lfa_options_group'); ?>

      <!-- HOME TAB -->
      <div id="home" class="lfa-tab">
        <h2>Header & Hero</h2>
        <table class="form-table">
          

          

          <tr>
            <th><label for="home_slider_sc">Hero Slider (Revolution Slider shortcode)</label></th>
            <td><input type="text" id="home_slider_sc" name="lfa_options[home][slider_shortcode]" value="<?php echo esc_attr(lfa_get('home.slider_shortcode')); ?>" class="regular-text" placeholder='[rev_slider alias="home"]'></td>
          </tr>
        </table>

        <hr><h2>Shop by Category (6 cards)</h2>
        <?php
          $cat_items = lfa_get('home.shop_by_category.items', []);
          if (!is_array($cat_items)) $cat_items = [];
          $count = max(6, count($cat_items));
        ?>
        <table class="form-table">
          <?php for ($i=0;$i<6;$i++):
            $item = $cat_items[$i] ?? ['title'=>'','link'=>'','image'=>0]; ?>
            <tr>
              <th>Item <?php echo $i+1; ?></th>
              <td>
                <div class="lfa-row" style="display:flex;gap:12px;align-items:center;flex-wrap:wrap">
                  <label>Title <input type="text" name="lfa_options[home][shop_by_category][items][<?php echo $i; ?>][title]" value="<?php echo esc_attr($item['title']); ?>" style="width:200px;"></label>
                  <label>Link <input type="text" name="lfa_options[home][shop_by_category][items][<?php echo $i; ?>][link]"  value="<?php echo esc_attr($item['link']); ?>" style="width:260px;" placeholder="/product-category/leggings/"></label>
                  <span>
                    <input type="hidden" id="sbc_img_<?php echo $i; ?>" name="lfa_options[home][shop_by_category][items][<?php echo $i; ?>][image]" value="<?php echo esc_attr(intval($item['image'])); ?>">
                    <div id="sbc_prev_<?php echo $i; ?>"><?php lfa_media_preview(intval($item['image'])); ?></div>
                    <a href="#" class="button lfa-media-btn" data-target="sbc_img_<?php echo $i; ?>" data-preview="sbc_prev_<?php echo $i; ?>"><?php _e('Select Image', 'livingfitapparel'); ?></a>
                  </span>
                </div>
              </td>
            </tr>
          <?php endfor; ?>
        </table>

        <hr><h2>Featured Products</h2>
        <table class="form-table">
          <tr>
            <th><label for="home_feat_count">Show featured products (Woo “Featured”)</label></th>
            <td>
              <input type="number" id="home_feat_count" name="lfa_options[home][featured][count]" min="1" max="24" value="<?php echo esc_attr(lfa_get('home.featured.count', 8)); ?>">
              <span class="description">Products flagged as Featured in WooCommerce → Products.</span>
            </td>
          </tr>
        </table>



        <hr><h2>Our Story</h2>
        <table class="form-table">
          <tr>
            <th><label for="home_story_text">Text</label></th>
            <td><textarea id="home_story_text" name="lfa_options[home][story][text]" class="large-text" rows="4"><?php echo esc_textarea(lfa_get('home.story.text')); ?></textarea></td>
          </tr>
          <tr>
            <th><label for="home_story_btn_text">Button</label></th>
            <td>
              <input type="text" id="home_story_btn_text" name="lfa_options[home][story][btn_text]" value="<?php echo esc_attr(lfa_get('home.story.btn_text')); ?>" placeholder="Read our story"> &nbsp;
              <input type="text" id="home_story_btn_link" name="lfa_options[home][story][btn_link]" value="<?php echo esc_attr(lfa_get('home.story.btn_link')); ?>" placeholder="/our-story/">
            </td>
          </tr>
        </table>

        <hr><h2>Shop By Color</h2>
        <table class="form-table">
          <tr>
            <th><label for="home_color_terms">Color terms (comma-separated slugs)</label></th>
            <td><input type="text" id="home_color_terms" name="lfa_options[home][shop_by_color][terms]" value="<?php echo esc_attr(lfa_get('home.shop_by_color.terms')); ?>" class="regular-text" placeholder="black,grey,lilac"></td>
          </tr>
          <tr>
            <th><label for="home_color_count">Products per color</label></th>
            <td><input type="number" id="home_color_count" name="lfa_options[home][shop_by_color][count]" value="<?php echo esc_attr(lfa_get('home.shop_by_color.count', 8)); ?>" min="1" max="24"></td>
          </tr>
        </table>

        <hr><h2>Banner</h2>
        <table class="form-table">
          <tr>
            <th>Banner image</th>
            <td>
              <?php $bimg = intval(lfa_get('home.banner.image')); ?>
              <div id="home_banner_prev"><?php lfa_media_preview($bimg); ?></div>
              <input type="hidden" id="home_banner_img" name="lfa_options[home][banner][image]" value="<?php echo esc_attr($bimg); ?>">
              <p><a href="#" class="button lfa-media-btn" data-target="home_banner_img" data-preview="home_banner_prev">Select Image</a></p>
              <p><label>Link <input type="text" name="lfa_options[home][banner][link]" value="<?php echo esc_attr(lfa_get('home.banner.link')); ?>" class="regular-text" placeholder="/shop/"></label></p>
            </td>
          </tr>
        </table>

        <hr><h2>Find your fit (Carousel)</h2>
        <?php $fyf = lfa_get('home.find_your_fit.items', []); if (!is_array($fyf)) $fyf=[]; ?>
        <table class="form-table">
          <tr><td colspan="2">
            <div id="fyf-list">
              <?php foreach ($fyf as $i=>$row): ?>
                <div class="lfa-row" style="margin:10px 0;padding:10px;border:1px solid #eee;border-radius:8px;">
                  <div style="display:flex;gap:12px;align-items:center;flex-wrap:wrap">
                    <label>Title <input type="text" name="lfa_options[home][find_your_fit][items][<?php echo $i; ?>][title]" value="<?php echo esc_attr($row['title']??''); ?>" style="width:200px;"></label>
                    <label>Link <input type="text" name="lfa_options[home][find_your_fit][items][<?php echo $i; ?>][link]" value="<?php echo esc_attr($row['link']??''); ?>" style="width:260px;"></label>
                    <span>
                      <input type="hidden" id="fyf_img_<?php echo $i; ?>" name="lfa_options[home][find_your_fit][items][<?php echo $i; ?>][image]" value="<?php echo esc_attr(intval($row['image']??0)); ?>">
                      <div id="fyf_prev_<?php echo $i; ?>"><?php lfa_media_preview(intval($row['image']??0)); ?></div>
                      <a href="#" class="button lfa-media-btn" data-target="fyf_img_<?php echo $i; ?>" data-preview="fyf_prev_<?php echo $i; ?>">Select Image</a>
                    </span>
                    <a href="#" class="button-link-delete lfa-remove-row" style="color:#b91c1c;margin-left:auto">Remove</a>
                  </div>
                </div>
              <?php endforeach; ?>
            </div>
            <p><a href="#" class="button lfa-add-row" data-template="#tpl-fyf" data-append="#fyf-list"><?php _e('Add item', 'livingfitapparel'); ?></a></p>
          </td></tr>
        </table>

        <script type="text/html" id="tpl-fyf">
          <div class="lfa-row" style="margin:10px 0;padding:10px;border:1px solid #eee;border-radius:8px;">
            <div style="display:flex;gap:12px;align-items:center;flex-wrap:wrap">
              <label>Title <input type="text" name="lfa_options[home][find_your_fit][items][__INDEX__][title]" value="" style="width:200px;"></label>
              <label>Link <input type="text" name="lfa_options[home][find_your_fit][items][__INDEX__][link]" value="" style="width:260px;"></label>
              <span>
                <input type="hidden" id="fyf_img_new___INDEX__" name="lfa_options[home][find_your_fit][items][__INDEX__][image]" value="">
                <div id="fyf_prev_new___INDEX__"></div>
                <a href="#" class="button lfa-media-btn" data-target="fyf_img_new___INDEX__" data-preview="fyf_prev_new___INDEX__">Select Image</a>
              </span>
              <a href="#" class="button-link-delete lfa-remove-row" style="color:#b91c1c;margin-left:auto">Remove</a>
            </div>
          </div>
        </script>
        <script>
          // Replace __INDEX__ with current count (simple indexer)
          document.addEventListener('click', function(e){
            if(!e.target.classList.contains('lfa-add-row')) return;
            setTimeout(function(){
              var list = document.querySelector('#fyf-list');
              var rows = list.querySelectorAll('.lfa-row').length;
              list.querySelectorAll('input[name*="__INDEX__"]').forEach(function(inp){
                inp.name = inp.name.replace('__INDEX__', rows-1);
                if (inp.id) inp.id = inp.id.replace('__INDEX__', rows-1);
              });
              list.querySelectorAll('[id*="__INDEX__"]').forEach(function(el){
                el.id = el.id.replace('__INDEX__', rows-1);
              });
            },50);
          });
        </script>

        <hr><h2>Customer Reviews (select specific Woo reviews)</h2>
        <table class="form-table">
          <tr>
            <th><label for="home_reviews_ids">Review comment IDs (comma separated)</label></th>
            <td><input type="text" id="home_reviews_ids" name="lfa_options[home][reviews][comment_ids]" value="<?php echo esc_attr(lfa_get('home.reviews.comment_ids')); ?>" class="regular-text" placeholder="1234,2345,3456"></td>
          </tr>
        </table>

        <hr><h2>Our Community (Instagram Reels)</h2>
        <table class="form-table">
          <tr>
            <th><label for="home_ig_reels">Reels URLs (one per line)</label></th>
            <td><textarea id="home_ig_reels" name="lfa_options[home][instagram][reel_urls]" class="large-text" rows="4" placeholder="https://www.instagram.com/reel/xxxx/"><?php echo esc_textarea(lfa_get('home.instagram.reel_urls')); ?></textarea></td>
          </tr>
        </table>

        
      </div>

      <!-- Other tabs kept from earlier build -->
      <div id="general" class="lfa-tab active">
        <table class="form-table">
          <tr><th><label for="container_width">Container width</label></th><td><input type="text" id="container_width" name="lfa_options[container_width]" value="<?php echo esc_attr(lfa_get_option('container_width','1180px')); ?>"></td></tr>
          <tr><th><label for="accent_color">Accent color</label></th><td><input type="text" id="accent_color" name="lfa_options[accent_color]" value="<?php echo esc_attr(lfa_get_option('accent_color','#0F766E')); ?>"></td></tr>
          <tr>
            <th>Site logo (override)</th>
            <td>
              <?php $gen_logo_id = lfa_get('general.logo_id'); ?>
              <div id="gen_logo_preview"><?php lfa_media_preview(intval($gen_logo_id)); ?></div>
              <input type="hidden" id="gen_logo_id" name="lfa_options[general][logo_id]" value="<?php echo esc_attr(intval($gen_logo_id)); ?>">
              <p><button class="button lfa-media-btn" data-target="gen_logo_id" data-preview="gen_logo_preview">Select Logo</button> <small>Tip: You can also use the WordPress Site Identity logo.</small></p>
            </td>
          </tr>
          <tr>
            <th>Favicon (site icon)</th>
            <td>
              <?php $fav_id = intval(lfa_get('general.favicon_id')); ?>
              <div id="gen_fav_preview"><?php lfa_media_preview($fav_id); ?></div>
              <input type="hidden" id="gen_fav_id" name="lfa_options[general][favicon_id]" value="<?php echo esc_attr($fav_id); ?>">
              <p><button class="button lfa-media-btn" data-target="gen_fav_id" data-preview="gen_fav_preview"><?php _e('Select Favicon', 'livingfitapparel'); ?></button> <small>Recommended: square PNG 32×32 or 64×64.</small></p>
            </td>
          </tr>
        </table>
      </div>

        <div id="header" class="lfa-tab">
          <table class="form-table">
            <tr>
              <th><label for="header_layout">Header layout</label></th>
              <td>
                <?php $sel = lfa_get_option('header_layout','logo-left'); ?>
                <select id="header_layout" name="lfa_options[header_layout]">
                  <?php foreach(['logo-left','logo-center','logo-left-split','logo-center-split'] as $l)
                    printf('<option %s value="%s">%s</option>', selected($sel,$l,false), esc_attr($l), esc_html($l)); ?>
                </select>
                <p class="description">Use <strong>logo-center-split</strong> for this design (nav left, logo center, icons right).</p>
              </td>
            </tr>
        
            <tr>
              <th>Sticky header</th>
              <td><label><input type="checkbox" name="lfa_options[sticky_header]" value="1" <?php checked(!empty($opts['sticky_header'])); ?>> Enable</label></td>
            </tr>

            <tr>
              <th><label for="header_menu">Header Menu</label></th>
              <td>
                <select id="header_menu" name="lfa_options[header][menu_id]">
                  <option value=""><?php _e('Use Primary Menu setting','livingfitapparel'); ?></option>
                  <?php foreach($menus as $m): ?>
                    <option value="<?php echo esc_attr($m->term_id); ?>" <?php selected(intval(lfa_get('header.menu_id')), intval($m->term_id)); ?>><?php echo esc_html($m->name); ?></option>
                  <?php endforeach; ?>
                </select>
              </td>
            </tr>
        
            <tr><th colspan="2"><h3 style="margin:0">Announcement bar</h3></th></tr>
            <tr>
              <th><label for="hdr_announce_text">Text</label></th>
              <td><input type="text" id="hdr_announce_text" class="regular-text" name="lfa_options[header][announce_text]" value="<?php echo esc_attr(lfa_get('header.announce_text')); ?>" placeholder="THE LIVINGFIT APPAREL — Find out more"></td>
            </tr>
            <tr>
              <th><label for="hdr_announce_link">Link (optional)</label></th>
              <td><input type="text" id="hdr_announce_link" class="regular-text" name="lfa_options[header][announce_link]" value="<?php echo esc_attr(lfa_get('header.announce_link')); ?>" placeholder="/about/"></td>
            </tr>
            <tr>
              <th><label for="hdr_announce_bg">Background</label></th>
              <td><input type="text" id="hdr_announce_bg" class="regular-text" name="lfa_options[header][announce_bg]" value="<?php echo esc_attr(lfa_get('header.announce_bg','#F6F4EF')); ?>" placeholder="#F6F4EF"></td>
            </tr>
        
            <tr><th colspan="2"><h3 style="margin:0">Logo & Icons</h3></th></tr>
            <tr>
              <th><label for="hdr_logo_height">Logo max-height (px)</label></th>
              <td><input type="number" id="hdr_logo_height" name="lfa_options[header][logo_h]" value="<?php echo esc_attr(lfa_get('header.logo_h',34)); ?>" min="20" max="120"></td>
            </tr>
            <tr>
              <th>Right icons</th>
              <td style="display:flex;gap:18px;align-items:center;flex-wrap:wrap">
                <label><input type="checkbox" name="lfa_options[header][show_search]" value="1" <?php checked(!empty(lfa_get('header.show_search'))); ?>> Search</label>
                <label><input type="checkbox" name="lfa_options[header][show_wishlist]" value="1" <?php checked(!empty(lfa_get('header.show_wishlist'))); ?>> Wishlist</label>
                <label><input type="checkbox" name="lfa_options[header][show_cart]" value="1" <?php checked(!empty(lfa_get('header.show_cart',1))); ?>> Cart</label>
                <span>
                  <label>Wishlist URL
                    <input type="text" name="lfa_options[header][wishlist_url]" value="<?php echo esc_attr(lfa_get('header.wishlist_url','/wishlist/')); ?>" class="regular-text">
                  </label>
                </span>
              </td>
            </tr>

            <tr><th colspan="2"><h3 style="margin:0">Mega Menu (for menu items with children)</h3></th></tr>
            <tr>
              <th colspan="2">
                <p class="description">Configure the mega menu that appears when hovering over menu items with nested items. Add the CSS class <code>mega-menu</code> to a menu item to enable the mega menu.</p>
              </th>
            </tr>
            <tr>
              <th><label for="mega_col1_title">Column 1 - Title</label></th>
              <td><input type="text" id="mega_col1_title" name="lfa_options[header][megamenu][col1][title]" value="<?php echo esc_attr(lfa_get('header.megamenu.col1.title')); ?>" class="regular-text" placeholder="Leave empty to hide title"></td>
            </tr>
            <tr>
              <th><label for="mega_col1_cats">Column 1 - Category IDs</label></th>
              <td><input type="text" id="mega_col1_cats" name="lfa_options[header][megamenu][col1][category_ids]" value="<?php echo esc_attr(lfa_get('header.megamenu.col1.category_ids')); ?>" class="regular-text" placeholder="1,2,3 (comma separated product category IDs)"></td>
            </tr>
            <tr>
              <th><label for="mega_col2_title">Column 2 - Title</label></th>
              <td><input type="text" id="mega_col2_title" name="lfa_options[header][megamenu][col2][title]" value="<?php echo esc_attr(lfa_get('header.megamenu.col2.title')); ?>" class="regular-text" placeholder="Leave empty to hide title"></td>
            </tr>
            <tr>
              <th><label for="mega_col2_cats">Column 2 - Category IDs</label></th>
              <td><input type="text" id="mega_col2_cats" name="lfa_options[header][megamenu][col2][category_ids]" value="<?php echo esc_attr(lfa_get('header.megamenu.col2.category_ids')); ?>" class="regular-text" placeholder="1,2,3 (comma separated product category IDs)"></td>
            </tr>
            <tr>
              <th><label for="mega_col3_title">Column 3 - Title</label></th>
              <td><input type="text" id="mega_col3_title" name="lfa_options[header][megamenu][col3][title]" value="<?php echo esc_attr(lfa_get('header.megamenu.col3.title')); ?>" class="regular-text" placeholder="Leave empty to hide title"></td>
            </tr>
            <tr>
              <th><label for="mega_col3_cats">Column 3 - Category IDs</label></th>
              <td><input type="text" id="mega_col3_cats" name="lfa_options[header][megamenu][col3][category_ids]" value="<?php echo esc_attr(lfa_get('header.megamenu.col3.category_ids')); ?>" class="regular-text" placeholder="1,2,3 (comma separated product category IDs)"></td>
            </tr>
            <tr>
              <th>Column 4 - Image</th>
              <td>
                <?php $mega_img = intval(lfa_get('header.megamenu.col4.image')); ?>
                <div id="mega_col4_prev"><?php lfa_media_preview($mega_img); ?></div>
                <input type="hidden" id="mega_col4_img" name="lfa_options[header][megamenu][col4][image]" value="<?php echo esc_attr($mega_img); ?>">
                <p><a href="#" class="button lfa-media-btn" data-target="mega_col4_img" data-preview="mega_col4_prev">Select Image</a></p>
              </td>
            </tr>
          </table>
        </div>


      <div id="footer" class="lfa-tab">
        <table class="form-table">
          <tr><th><label for="footer_cols">Footer columns</label></th><td>
            <?php $fc = lfa_get_option('footer_cols','4'); ?>
            <select id="footer_cols" name="lfa_options[footer_cols]">
              <?php foreach(['2','3','4'] as $c) printf('<option %s value="%s">%s</option>', selected($fc,$c,false), esc_attr($c), esc_html($c)); ?>
            </select></td></tr>
        </table>

        <hr><h2>Footer (newsletter + links + socials)</h2>
        <table class="form-table">
          <tr>
            <th><label for="footer_news_sc">Newsletter form shortcode</label></th>
            <td><input type="text" id="footer_news_sc" name="lfa_options[footer][newsletter_sc]" value="<?php echo esc_attr(lfa_get('footer.newsletter_sc', lfa_get('home.footer.newsletter_sc'))); ?>" class="regular-text" placeholder='[contact-form-7 id="123"]'></td>
          </tr>
          <tr>
            <th><label for="footer_right_menu">Right-side links menu</label></th>
            <td>
              <?php $footer_menu_selected = intval(lfa_get('footer.menu_id', lfa_get('home.footer.menu_id'))); ?>
              <select id="footer_right_menu" name="lfa_options[footer][menu_id]">
                <option value=""><?php _e('Select a menu','livingfitapparel'); ?></option>
                <?php foreach($menus as $m): ?>
                  <option value="<?php echo esc_attr($m->term_id); ?>" <?php selected($footer_menu_selected, intval($m->term_id)); ?>><?php echo esc_html($m->name); ?></option>
                <?php endforeach; ?>
              </select>
            </td>
          </tr>
          <tr>
            <th>Socials</th>
            <td>
              <input type="text" name="lfa_options[footer][socials][instagram]" value="<?php echo esc_attr(lfa_get('footer.socials.instagram', lfa_get('home.footer.socials.instagram'))); ?>" placeholder="Instagram URL" class="regular-text"><br>
              <input type="text" name="lfa_options[footer][socials][facebook]" value="<?php echo esc_attr(lfa_get('footer.socials.facebook', lfa_get('home.footer.socials.facebook'))); ?>" placeholder="Facebook URL" class="regular-text"><br>
              <input type="text" name="lfa_options[footer][socials][tiktok]" value="<?php echo esc_attr(lfa_get('footer.socials.tiktok', lfa_get('home.footer.socials.tiktok'))); ?>" placeholder="TikTok URL" class="regular-text">
            </td>
          </tr>
          <tr>
            <th>Big text (row 2)</th>
            <td><textarea id="footer_big_text" name="lfa_options[footer][big_text]" class="large-text" rows="3" style="width:420px;"><?php echo esc_textarea(lfa_get('footer.big_text', lfa_get('home.footer.big_text','LIVINGFIT APPAREL'))); ?></textarea>
              <p class="description">HTML is allowed in this field.</p>
            </td>
          </tr>
          <tr>
            <th>Copyright & payments (row 3)</th>
            <td>
              <input type="text" name="lfa_options[footer][copyright]" value="<?php echo esc_attr(lfa_get('footer.copyright', lfa_get('home.footer.copyright','© LivingFit Apparel '.date('Y').' All Rights Reserved'))); ?>" class="regular-text" style="width:420px;"><br><br>
              <?php $pimg2 = intval(lfa_get('footer.payment_image', lfa_get('home.footer.payment_image'))); ?>
              <div id="footer_pay_prev"><?php lfa_media_preview($pimg2); ?></div>
              <input type="hidden" id="footer_pay_img" name="lfa_options[footer][payment_image]" value="<?php echo esc_attr($pimg2); ?>">
              <p><a href="#" class="button lfa-media-btn" data-target="footer_pay_img" data-preview="footer_pay_prev">Select Payment Icons Image</a></p>
            </td>
          </tr>
        </table>
      </div>

      <div id="shop" class="lfa-tab">
        <table class="form-table">
          <tr><th><label for="shop_card_style">Product card style</label></th><td>
            <?php $ps = lfa_get_option('shop_card_style','card'); ?>
            <select id="shop_card_style" name="lfa_options[shop_card_style]">
              <?php foreach(['minimal','card','bordered'] as $s) printf('<option %s value="%s">%s</option>', selected($ps,$s,false), esc_attr($s), esc_html($s)); ?>
            </select></td></tr>
          <tr><th>Quick view</th><td><label><input type="checkbox" name="lfa_options[quick_view]" value="1" <?php checked(!empty($opts['quick_view'])); ?>> Enable quick view button</label></td></tr>
          <tr><th>Wishlist</th><td><label><input type="checkbox" name="lfa_options[wishlist]" value="1" <?php checked(!empty($opts['wishlist'])); ?>> Enable wishlist button</label></td></tr>
          <tr><th>Show colors</th><td><label><input type="checkbox" name="lfa_options[show_colors]" value="1" <?php checked(!empty($opts['show_colors'])); ?>> Show color count and swatches</label></td></tr>
          <tr><th>Show new tag</th><td><label><input type="checkbox" name="lfa_options[show_new_tag]" value="1" <?php checked(!empty($opts['show_new_tag'])); ?>> Show "New" tag on products</label></td></tr>
          <tr><th>Show sale tag</th><td><label><input type="checkbox" name="lfa_options[show_sale_tag]" value="1" <?php checked(!empty($opts['show_sale_tag'])); ?>> Show "Sale" tag on products</label></td></tr>
          <tr><th>Show sale percentage</th><td><label><input type="checkbox" name="lfa_options[show_sale_percentage]" value="1" <?php checked(!empty($opts['show_sale_percentage'])); ?>> Show discount percentage instead of sale text</label></td></tr>
          <tr><th><label for="sale_badge_text">Sale badge text</label></th><td><input type="text" id="sale_badge_text" name="lfa_options[sale_badge_text]" value="<?php echo esc_attr(lfa_get_option('sale_badge_text','Sale')); ?>"></td></tr>
          <tr><th>Catalog mode</th><td><label><input type="checkbox" name="lfa_options[catalog_mode]" value="1" <?php checked(!empty($opts['catalog_mode'])); ?>> Hide Add to Cart</label></td></tr>
          <tr><th colspan="2"><h3 style="margin:0">Mega menu</h3></th></tr>
          <tr>
            <th>Mega menu featured image</th>
            <td>
              <?php $mmimg = intval(lfa_get('shop.megamenu.image')); ?>
              <div id="shop_mm_prev"><?php lfa_media_preview($mmimg); ?></div>
              <input type="hidden" id="shop_mm_img" name="lfa_options[shop][megamenu][image]" value="<?php echo esc_attr($mmimg); ?>">
              <p><a href="#" class="button lfa-media-btn" data-target="shop_mm_img" data-preview="shop_mm_prev">Select Image</a> <small>Add CSS class <code>mega-shop</code> to your “Shop” menu item to enable the mega menu.</small></p>
            </td>
          </tr>
        </table>
      </div>

      <div id="404" class="lfa-tab">
        <h2>404 Error Page Settings</h2>
        <table class="form-table">
          <tr>
            <th><label for="404_title">Page Title</label></th>
            <td><input type="text" id="404_title" name="lfa_options[404][title]" value="<?php echo esc_attr(lfa_get('404.title', '404')); ?>" class="regular-text" placeholder="404"></td>
          </tr>
          <tr>
            <th><label for="404_description">Description</label></th>
            <td><textarea id="404_description" name="lfa_options[404][description]" class="large-text" rows="4" placeholder="AN UNEXPECTED ERROR OCCURED - DISCOVER OUR PRODUCTS OR - CONTACT US IF YOU NEED ASSISTANCE"><?php echo esc_textarea(lfa_get('404.description', 'AN UNEXPECTED ERROR OCCURED - DISCOVER OUR PRODUCTS OR - CONTACT US IF YOU NEED ASSISTANCE')); ?></textarea></td>
          </tr>
          <tr>
            <th>Products</th>
            <td>
              <p class="description">Featured products will be displayed in the slider on the 404 page. To manage which products appear, mark products as "Featured" in WooCommerce → Products.</p>
            </td>
          </tr>
        </table>
      </div>

      <div id="perf" class="lfa-tab">
        <table class="form-table">
          <tr><th>Dequeue default block CSS</th><td><label><input type="checkbox" name="lfa_options[performance_dequeue_blocks]" value="1" <?php checked(!empty($opts['performance_dequeue_blocks'])); ?>> Enable</label></td></tr>
        </table>
      </div>

      <?php submit_button(); ?>
    </form>
      </div>
    </div>
  </div>

  <style>
    /* Layout */
    .lfa-settings{display:grid;grid-template-columns:220px 1fr;gap:20px;margin-top:12px}
    .lfa-sidebar{background:#fff;border:1px solid #e5e7eb;border-radius:8px;padding:10px;position:sticky;top:32px;align-self:start}
    .lfa-tab-link{display:flex;align-items:center;gap:8px;padding:10px 12px;border-radius:6px;text-decoration:none;color:#111;margin:2px 0}
    .lfa-tab-link:hover{background:#f9fafb}
    .lfa-tab-link.is-active{background:#111;color:#fff}
    .lfa-panels{background:#fff;border:1px solid #e5e7eb;border-radius:8px;padding:16px}
    .lfa-tab{display:none}
    .lfa-tab.active{display:block}
    .lfa-admin .form-table th { width: 240px; }
    
    @media (max-width: 960px){
      .lfa-settings{grid-template-columns:1fr}
      .lfa-sidebar{position:relative;top:auto;display:flex;flex-wrap:wrap;gap:8px}
      .lfa-tab-link{flex:1 1 auto}
    }
  </style>
  <script>
    (function(){
      var links = Array.prototype.slice.call(document.querySelectorAll('.lfa-tab-link'));
      var tabs  = Array.prototype.slice.call(document.querySelectorAll('.lfa-tab'));
      function activate(hash){
        if(!hash){ hash = links.length ? links[0].getAttribute('href') : '#home'; }
        links.forEach(function(a){ a.classList.toggle('is-active', a.getAttribute('href') === hash); });
        tabs.forEach(function(p){ p.classList.toggle('active', '#'+p.id === hash); });
        if(history.pushState){ history.replaceState(null,'',hash); }
      }
      document.addEventListener('click', function(e){
        var a = e.target.closest('.lfa-tab-link');
        if(!a) return;
        e.preventDefault();
        activate(a.getAttribute('href'));
      });
      // Initial activation from hash or default
      activate(location.hash);
    })();
  </script>
  <?php
}
