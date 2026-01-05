<?php
if (!defined('ABSPATH')) exit;

/**
 * Find Your Fit Meta Box
 * Adds a repeater field for tabs with name, image, product IDs, and description
 */

// Add meta box only for pages using "Find Your Fit" template
add_action('add_meta_boxes', function() {
    global $post;
    
    // Only show on pages using "Find Your Fit" template
    $template = get_page_template_slug($post->ID);
    if ($template !== 'find-your-fit.php') {
        return;
    }
    
    add_meta_box(
        'lfa_find_your_fit_tabs',
        __('Find Your Fit Tabs', 'livingfitapparel'),
        'lfa_find_your_fit_meta_box_callback',
        'page',
        'normal',
        'high'
    );
});

// Meta box callback
function lfa_find_your_fit_meta_box_callback($post) {
    wp_nonce_field('lfa_find_your_fit_save', 'lfa_find_your_fit_nonce');
    
    $tabs = get_post_meta($post->ID, '_lfa_fyf_tabs', true);
    $tabs = is_array($tabs) ? $tabs : array();
    
    ?>
    <div class="lfa-fyf-meta-box">
        <p class="description"><?php _e('Add tabs for the Find Your Fit page. Each tab should have a name, image, products, and description.', 'livingfitapparel'); ?></p>
        
        <div id="lfa-fyf-tabs-repeater" class="lfa-fyf-repeater">
            <?php if (!empty($tabs)): ?>
                <?php foreach ($tabs as $index => $tab): ?>
                    <div class="lfa-fyf-row" data-index="<?php echo esc_attr($index); ?>">
                        <div class="lfa-fyf-row-header">
                            <h4><?php _e('Tab', 'livingfitapparel'); ?> #<?php echo esc_html($index + 1); ?></h4>
                            <button type="button" class="button lfa-remove-row"><?php _e('Remove', 'livingfitapparel'); ?></button>
                        </div>
                        
                        <div class="lfa-fyf-row-fields">
                            <p>
                                <label><strong><?php _e('Name', 'livingfitapparel'); ?>:</strong></label><br>
                                <input type="text" 
                                       name="lfa_fyf_tabs[<?php echo esc_attr($index); ?>][name]" 
                                       value="<?php echo esc_attr($tab['name'] ?? ''); ?>" 
                                       class="widefat" 
                                       placeholder="<?php esc_attr_e('e.g., SOFTRIB', 'livingfitapparel'); ?>" />
                            </p>
                            
                            <p>
                                <label><strong><?php _e('Image', 'livingfitapparel'); ?>:</strong></label><br>
                                <input type="hidden" 
                                       name="lfa_fyf_tabs[<?php echo esc_attr($index); ?>][image_id]" 
                                       id="lfa_fyf_image_<?php echo esc_attr($index); ?>" 
                                       value="<?php echo esc_attr($tab['image_id'] ?? ''); ?>" />
                                <div id="lfa_fyf_image_preview_<?php echo esc_attr($index); ?>" class="lfa-fyf-image-preview">
                                    <?php if (!empty($tab['image_id'])): ?>
                                        <?php echo wp_get_attachment_image($tab['image_id'], 'medium', false, array('style' => 'max-width:150px;height:auto;border:1px solid #ddd;border-radius:4px;')); ?>
                                    <?php endif; ?>
                                </div>
                                <button type="button" 
                                        class="button lfa-fyf-media-btn" 
                                        data-target="lfa_fyf_image_<?php echo esc_attr($index); ?>" 
                                        data-preview="lfa_fyf_image_preview_<?php echo esc_attr($index); ?>">
                                    <?php _e('Select Image', 'livingfitapparel'); ?>
                                </button>
                                <button type="button" 
                                        class="button lfa-fyf-remove-image" 
                                        data-target="lfa_fyf_image_<?php echo esc_attr($index); ?>" 
                                        data-preview="lfa_fyf_image_preview_<?php echo esc_attr($index); ?>">
                                    <?php _e('Remove Image', 'livingfitapparel'); ?>
                                </button>
                            </p>
                            
                            <p>
                                <label><strong><?php _e('Products', 'livingfitapparel'); ?>:</strong></label><br>
                                <select name="lfa_fyf_tabs[<?php echo esc_attr($index); ?>][product_ids][]" 
                                        class="lfa-fyf-product-select widefat" 
                                        multiple="multiple" 
                                        style="min-height: 150px;">
                                    <?php
                                    $selected_ids = isset($tab['product_ids']) && is_array($tab['product_ids']) ? array_map('intval', $tab['product_ids']) : array();
                                    
                                    if (class_exists('WooCommerce') && !empty($selected_ids)) {
                                        $products = wc_get_products(array(
                                            'include' => $selected_ids,
                                            'limit' => -1,
                                            'orderby' => 'title',
                                            'order' => 'ASC',
                                        ));
                                        
                                        foreach ($products as $product) {
                                            echo '<option value="' . esc_attr($product->get_id()) . '" selected="selected">' . esc_html($product->get_name()) . ' (ID: ' . esc_html($product->get_id()) . ')</option>';
                                        }
                                    }
                                    ?>
                                </select>
                                <button type="button" 
                                        class="button lfa-fyf-add-product" 
                                        data-index="<?php echo esc_attr($index); ?>">
                                    <?php _e('Add Products', 'livingfitapparel'); ?>
                                </button>
                                <span class="description"><?php _e('Click "Add Products" to search and select products. Selected products will appear above.', 'livingfitapparel'); ?></span>
                            </p>
                            
                            <p>
                                <label><strong><?php _e('Description', 'livingfitapparel'); ?>:</strong></label><br>
                                <?php
                                $description = isset($tab['description']) ? $tab['description'] : '';
                                // Ensure description is properly decoded if it was stored with slashes
                                $description = wp_unslash($description);
                                ?>
                                <textarea name="lfa_fyf_tabs[<?php echo esc_attr($index); ?>][description]" 
                                          class="widefat" 
                                          rows="8"><?php echo esc_textarea($description); ?></textarea>
                            </p>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
        
        <p>
            <button type="button" class="button button-primary lfa-fyf-add-row"><?php _e('+ Add Tab', 'livingfitapparel'); ?></button>
        </p>
        
        <!-- Template for new row -->
        <script type="text/html" id="lfa-fyf-row-template">
            <div class="lfa-fyf-row" data-index="{{INDEX}}">
                <div class="lfa-fyf-row-header">
                    <h4><?php _e('Tab', 'livingfitapparel'); ?> #{{INDEX_PLUS_ONE}}</h4>
                    <button type="button" class="button lfa-remove-row"><?php _e('Remove', 'livingfitapparel'); ?></button>
                </div>
                
                <div class="lfa-fyf-row-fields">
                    <p>
                        <label><strong><?php _e('Name', 'livingfitapparel'); ?>:</strong></label><br>
                        <input type="text" 
                               name="lfa_fyf_tabs[{{INDEX}}][name]" 
                               value="" 
                               class="widefat" 
                               placeholder="<?php esc_attr_e('e.g., SOFTRIB', 'livingfitapparel'); ?>" />
                    </p>
                    
                    <p>
                        <label><strong><?php _e('Image', 'livingfitapparel'); ?>:</strong></label><br>
                        <input type="hidden" 
                               name="lfa_fyf_tabs[{{INDEX}}][image_id]" 
                               id="lfa_fyf_image_{{INDEX}}" 
                               value="" />
                        <div id="lfa_fyf_image_preview_{{INDEX}}" class="lfa-fyf-image-preview"></div>
                        <button type="button" 
                                class="button lfa-fyf-media-btn" 
                                data-target="lfa_fyf_image_{{INDEX}}" 
                                data-preview="lfa_fyf_image_preview_{{INDEX}}">
                            <?php _e('Select Image', 'livingfitapparel'); ?>
                        </button>
                        <button type="button" 
                                class="button lfa-fyf-remove-image" 
                                data-target="lfa_fyf_image_{{INDEX}}" 
                                data-preview="lfa_fyf_image_preview_{{INDEX}}">
                            <?php _e('Remove Image', 'livingfitapparel'); ?>
                        </button>
                    </p>
                    
                    <p>
                        <label><strong><?php _e('Products', 'livingfitapparel'); ?>:</strong></label><br>
                        <select name="lfa_fyf_tabs[{{INDEX}}][product_ids][]" 
                                class="lfa-fyf-product-select widefat" 
                                multiple="multiple" 
                                style="min-height: 150px;">
                        </select>
                        <button type="button" 
                                class="button lfa-fyf-add-product" 
                                data-index="{{INDEX}}">
                            <?php _e('Add Products', 'livingfitapparel'); ?>
                        </button>
                        <span class="description"><?php _e('Click "Add Products" to search and select products.', 'livingfitapparel'); ?></span>
                    </p>
                    
                    <p>
                        <label><strong><?php _e('Description', 'livingfitapparel'); ?>:</strong></label><br>
                        <textarea name="lfa_fyf_tabs[{{INDEX}}][description]" 
                                  class="widefat" 
                                  rows="8"></textarea>
                    </p>
                </div>
            </div>
        </script>
    </div>
    
    <style>
        .lfa-fyf-meta-box .lfa-fyf-row {
            border: 1px solid #ddd;
            border-radius: 4px;
            padding: 15px;
            margin-bottom: 20px;
            background: #fff;
        }
        .lfa-fyf-meta-box .lfa-fyf-row-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 1px solid #eee;
        }
        .lfa-fyf-meta-box .lfa-fyf-row-header h4 {
            margin: 0;
        }
        .lfa-fyf-meta-box .lfa-fyf-row-fields p {
            margin-bottom: 15px;
        }
        .lfa-fyf-meta-box .lfa-fyf-image-preview {
            margin: 10px 0;
            min-height: 50px;
        }
        .lfa-fyf-meta-box .lfa-fyf-image-preview img {
            max-width: 150px;
            height: auto;
            border: 1px solid #ddd;
            border-radius: 4px;
            display: block;
            margin: 10px 0;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .lfa-fyf-meta-box .lfa-fyf-image-preview:empty::before {
            content: 'No image selected';
            color: #999;
            font-style: italic;
            display: block;
            padding: 10px;
            border: 1px dashed #ddd;
            border-radius: 4px;
            text-align: center;
        }
        .lfa-fyf-meta-box .lfa-fyf-product-select {
            font-size: 13px;
        }
        .lfa-fyf-meta-box .lfa-fyf-product-select option {
            padding: 5px;
        }
    </style>
    <?php
}

// Save meta box data
add_action('save_post', function($post_id) {
    // Check nonce
    if (!isset($_POST['lfa_find_your_fit_nonce']) || !wp_verify_nonce($_POST['lfa_find_your_fit_nonce'], 'lfa_find_your_fit_save')) {
        return;
    }
    
    // Check autosave
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }
    
    // Check permissions
    if (!current_user_can('edit_page', $post_id)) {
        return;
    }
    
    // Save tabs data
    if (isset($_POST['lfa_fyf_tabs']) && is_array($_POST['lfa_fyf_tabs'])) {
        $tabs = array();
        
        foreach ($_POST['lfa_fyf_tabs'] as $index => $tab) {
            $tab_data = array(
                'name' => isset($tab['name']) ? sanitize_text_field($tab['name']) : '',
                'image_id' => isset($tab['image_id']) ? intval($tab['image_id']) : 0,
                'product_ids' => isset($tab['product_ids']) && is_array($tab['product_ids']) 
                    ? array_map('intval', $tab['product_ids']) 
                    : array(),
                'description' => isset($tab['description']) ? wp_kses_post($tab['description']) : '',
            );
            
            // Only add if name is not empty
            if (!empty($tab_data['name'])) {
                $tabs[] = $tab_data;
            }
        }
        
        update_post_meta($post_id, '_lfa_fyf_tabs', $tabs);
    } else {
        // If no tabs submitted, delete the meta
        delete_post_meta($post_id, '_lfa_fyf_tabs');
    }
});

// Enqueue scripts and styles for meta box
add_action('admin_enqueue_scripts', function($hook) {
    global $post;
    
    // Only load on page edit screen
    if ($hook !== 'post.php' && $hook !== 'post-new.php') {
        return;
    }
    
    if (!$post || get_post_type($post) !== 'page') {
        return;
    }
    
    // Only load for pages using "Find Your Fit" template
    $template = get_page_template_slug($post->ID);
    if ($template !== 'find-your-fit.php') {
        return;
    }
    
    wp_enqueue_media();
    wp_enqueue_script('jquery-ui-autocomplete');
    
    $tabs_count = 0;
    if ($post && $post->ID) {
        $existing_tabs = get_post_meta($post->ID, '_lfa_fyf_tabs', true);
        $tabs_count = is_array($existing_tabs) ? count($existing_tabs) : 0;
    }
    
    wp_add_inline_script('jquery-core', "
        jQuery(function($) {
            var tabIndex = " . intval($tabs_count) . ";
            
            // Media uploader
            function bindMediaUploader() {
                $(document).on('click', '.lfa-fyf-media-btn', function(e) {
                    e.preventDefault();
                    var btn = $(this);
                    var targetInput = $('#' + btn.data('target'));
                    var previewDiv = $('#' + btn.data('preview'));
                    
                    var frame = wp.media({
                        title: 'Select Image',
                        multiple: false,
                        library: { type: 'image' }
                    });
                    
                    frame.on('select', function() {
                        var attachment = frame.state().get('selection').first().toJSON();
                        targetInput.val(attachment.id);
                        
                        // Get the best available image size
                        var imageUrl = attachment.sizes && attachment.sizes.medium ? attachment.sizes.medium.url : attachment.url;
                        
                        // Create preview with better styling
                        var previewHtml = '<img src=\"' + imageUrl + '\" alt=\"' + (attachment.alt || '') + '\" style=\"max-width:150px;height:auto;border:1px solid #ddd;border-radius:4px;display:block;margin:10px 0;box-shadow:0 2px 4px rgba(0,0,0,0.1);\" />';
                        previewDiv.html(previewHtml);
                        
                        // Add a small success indicator
                        previewDiv.css('background-color', '#f0f8f0');
                        setTimeout(function() {
                            previewDiv.css('background-color', '');
                        }, 1000);
                    });
                    
                    frame.open();
                });
            }
            
            // Remove image
            $(document).on('click', '.lfa-fyf-remove-image', function(e) {
                e.preventDefault();
                var btn = $(this);
                $('#' + btn.data('target')).val('');
                $('#' + btn.data('preview')).html('').css('background-color', '');
            });
            
            // Add product search
            function initProductSearch() {
                // Product search modal
                $(document).on('click', '.lfa-fyf-add-product', function(e) {
                    e.preventDefault();
                    var btn = $(this);
                    var index = btn.data('index');
                    var select = btn.siblings('.lfa-fyf-product-select');
                    
                    // Create modal
                    var modal = $('<div class=\"lfa-fyf-product-modal\" style=\"position:fixed;top:0;left:0;right:0;bottom:0;background:rgba(0,0,0,0.7);z-index:100000;display:flex;align-items:center;justify-content:center;\">' +
                        '<div style=\"background:#fff;padding:20px;border-radius:8px;max-width:600px;width:90%;max-height:80vh;overflow:auto;\">' +
                        '<h3>Search Products</h3>' +
                        '<input type=\"text\" class=\"lfa-fyf-product-search-input\" placeholder=\"Type to search products...\" style=\"width:100%;padding:10px;margin:10px 0;border:1px solid #ddd;border-radius:4px;\" />' +
                        '<div class=\"lfa-fyf-product-results\" style=\"max-height:400px;overflow-y:auto;border:1px solid #eee;padding:10px;margin:10px 0;\"></div>' +
                        '<div style=\"display:flex;gap:10px;justify-content:flex-end;\">' +
                        '<button class=\"button button-primary lfa-fyf-save-products\" data-index=\"' + index + '\">Add Selected</button>' +
                        '<button class=\"button lfa-fyf-cancel-modal\">Cancel</button>' +
                        '</div></div></div>');
                    
                    $('body').append(modal);
                    
                    var searchInput = modal.find('.lfa-fyf-product-search-input');
                    var resultsDiv = modal.find('.lfa-fyf-product-results');
                    var selectedProducts = {};
                    
                    // Load existing selected products
                    select.find('option:selected').each(function() {
                        var id = $(this).val();
                        var name = $(this).text();
                        selectedProducts[id] = {id: id, name: name, selected: true};
                    });
                    
                    function renderResults() {
                        var html = '';
                        $.each(selectedProducts, function(id, product) {
                            var checked = product.selected ? 'checked' : '';
                            html += '<label style=\"display:block;padding:8px;border-bottom:1px solid #eee;cursor:pointer;\"><input type=\"checkbox\" value=\"' + id + '\" ' + checked + ' style=\"margin-right:8px;\" />' + product.name + '</label>';
                        });
                        if (html === '') {
                            html = '<p style=\"color:#666;text-align:center;padding:20px;\">No products selected. Start typing to search.</p>';
                        }
                        resultsDiv.html(html);
                    }
                    
                    renderResults();
                    
                    // Search functionality
                    var searchTimeout;
                    searchInput.on('input', function() {
                        clearTimeout(searchTimeout);
                        var term = $(this).val();
                        
                        if (term.length < 2) {
                            renderResults();
                            return;
                        }
                        
                        searchTimeout = setTimeout(function() {
                            $.ajax({
                                url: ajaxurl,
                                type: 'POST',
                                data: {
                                    action: 'lfa_search_products_ajax',
                                    term: term
                                },
                                success: function(response) {
                                    if (response.success && response.data) {
                                        var html = '';
                                        $.each(response.data, function(i, product) {
                                            var id = product.id.toString();
                                            var isSelected = selectedProducts[id] && selectedProducts[id].selected;
                                            var checked = isSelected ? 'checked' : '';
                                            
                                            if (!selectedProducts[id]) {
                                                selectedProducts[id] = {id: id, name: product.text, selected: false};
                                            }
                                            
                                            html += '<label style=\"display:block;padding:8px;border-bottom:1px solid #eee;cursor:pointer;\"><input type=\"checkbox\" value=\"' + id + '\" ' + checked + ' style=\"margin-right:8px;\" />' + product.text + '</label>';
                                        });
                                        resultsDiv.html(html);
                                    }
                                }
                            });
                        }, 300);
                    });
                    
                    // Toggle selection
                    resultsDiv.on('change', 'input[type=\"checkbox\"]', function() {
                        var id = $(this).val();
                        if (selectedProducts[id]) {
                            selectedProducts[id].selected = $(this).is(':checked');
                        }
                    });
                    
                    // Save selected products
                    modal.find('.lfa-fyf-save-products').on('click', function() {
                        select.empty();
                        $.each(selectedProducts, function(id, product) {
                            if (product.selected) {
                                select.append('<option value=\"' + id + '\" selected=\"selected\">' + product.name + '</option>');
                            }
                        });
                        modal.remove();
                    });
                    
                    // Cancel
                    modal.find('.lfa-fyf-cancel-modal, .lfa-fyf-product-modal').on('click', function(e) {
                        if (e.target === this || $(e.target).hasClass('lfa-fyf-cancel-modal')) {
                            modal.remove();
                        }
                    });
                    
                    searchInput.focus();
                });
            }
            
            // Add new row
            $(document).on('click', '.lfa-fyf-add-row', function(e) {
                e.preventDefault();
                var template = $('#lfa-fyf-row-template').html();
                template = template.replace(/{{INDEX}}/g, tabIndex);
                template = template.replace(/{{INDEX_PLUS_ONE}}/g, tabIndex + 1);
                
                $('#lfa-fyf-tabs-repeater').append(template);
                tabIndex++;
            });
            
            // Remove row
            $(document).on('click', '.lfa-remove-row', function(e) {
                e.preventDefault();
                if (confirm('Are you sure you want to remove this tab?')) {
                    $(this).closest('.lfa-fyf-row').remove();
                }
            });
            
            // Initialize
            bindMediaUploader();
            initProductSearch();
        });
    ");
});

// AJAX handler for product search
add_action('wp_ajax_lfa_search_products', function() {
    if (!class_exists('WooCommerce')) {
        wp_send_json_error('WooCommerce not available');
        return;
    }
    
    $product_ids = isset($_POST['product_ids']) ? array_map('intval', explode(',', $_POST['product_ids'])) : array();
    $products = array();
    
    if (!empty($product_ids)) {
        $product_objects = wc_get_products(array(
            'include' => $product_ids,
            'limit' => -1,
        ));
        
        foreach ($product_objects as $product) {
            $products[$product->get_id()] = $product->get_name();
        }
    }
    
    wp_send_json_success($products);
});

// AJAX handler for product search (autocomplete)
add_action('wp_ajax_lfa_search_products_ajax', function() {
    if (!class_exists('WooCommerce')) {
        wp_send_json_error('WooCommerce not available');
        return;
    }
    
    $term = isset($_POST['term']) ? sanitize_text_field($_POST['term']) : '';
    $page = isset($_POST['page']) ? intval($_POST['page']) : 1;
    $results = array();
    
    if (strlen($term) >= 2) {
        $products = wc_get_products(array(
            'status' => 'publish',
            'limit' => 20,
            'page' => $page,
            's' => $term,
        ));
        
        foreach ($products as $product) {
            $results[] = array(
                'id' => $product->get_id(),
                'text' => $product->get_name() . ' (ID: ' . $product->get_id() . ')',
            );
        }
    }
    
    wp_send_json_success($results);
});

