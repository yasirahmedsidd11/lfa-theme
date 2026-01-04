<?php
/**
 * Product Custom Fields
 * 
 * Adds custom meta boxes for products:
 * - Care Instructions (WYSIWYG)
 * - Model Details (Text)
 * - Why You Need This Section Fields
 * 
 * Works with or without ACF
 */

if (!defined('ABSPATH')) exit;

// Register ACF fields programmatically (if ACF is active)
add_action('acf/init', function() {
    if (function_exists('acf_add_local_field_group')) {
        // Product Custom Fields
        acf_add_local_field_group(array(
            'key' => 'group_product_custom_fields',
            'title' => 'Product Custom Fields',
            'fields' => array(
                array(
                    'key' => 'field_care_instructions',
                    'label' => 'Care Instructions',
                    'name' => 'care_instructions',
                    'type' => 'wysiwyg',
                    'instructions' => 'Enter care instructions for this product.',
                    'required' => 0,
                    'default_value' => '',
                    'tabs' => 'all',
                    'toolbar' => 'basic',
                    'media_upload' => 0,
                    'delay' => 0,
                ),
                array(
                    'key' => 'field_model_details',
                    'label' => 'Model Details',
                    'name' => 'model_details',
                    'type' => 'text',
                    'instructions' => 'Enter model details (e.g., "Model is 5\'9 and wears a Small - Tall length").',
                    'required' => 0,
                    'default_value' => '',
                    'placeholder' => 'Model is 5\'9 and wears a Small - Tall length',
                ),
            ),
            'location' => array(
                array(
                    array(
                        'param' => 'post_type',
                        'operator' => '==',
                        'value' => 'product',
                    ),
                ),
            ),
            'menu_order' => 0,
            'position' => 'normal',
            'style' => 'default',
            'label_placement' => 'top',
            'instruction_placement' => 'label',
            'active' => true,
        ));
        
        // Why You Need This Section
        acf_add_local_field_group(array(
            'key' => 'group_why_you_need_this',
            'title' => 'Why You Need This Section',
            'fields' => array(
                array(
                    'key' => 'field_why_left_image',
                    'label' => 'Left Image',
                    'name' => 'why_left_image',
                    'type' => 'image',
                    'instructions' => 'Upload the left image for "Why you need this" section.',
                    'required' => 0,
                    'return_format' => 'id',
                    'preview_size' => 'medium',
                    'library' => 'all',
                ),
                array(
                    'key' => 'field_why_right_image',
                    'label' => 'Right Image',
                    'name' => 'why_right_image',
                    'type' => 'image',
                    'instructions' => 'Upload the right image for "Why you need this" section.',
                    'required' => 0,
                    'return_format' => 'id',
                    'preview_size' => 'medium',
                    'library' => 'all',
                ),
                array(
                    'key' => 'field_why_length',
                    'label' => 'Length',
                    'name' => 'why_length',
                    'type' => 'text',
                    'instructions' => 'Enter the length value (e.g., "Full Length").',
                    'required' => 0,
                    'placeholder' => 'Full Length',
                ),
                array(
                    'key' => 'field_why_material',
                    'label' => 'Material',
                    'name' => 'why_material',
                    'type' => 'text',
                    'instructions' => 'Enter the material value (e.g., "Ultra Flex Double").',
                    'required' => 0,
                    'placeholder' => 'Ultra Flex Double',
                ),
                array(
                    'key' => 'field_size_chart_images',
                    'label' => 'Size Chart Images',
                    'name' => 'size_chart_images',
                    'type' => 'gallery',
                    'instructions' => 'Upload size chart images (multiple allowed).',
                    'required' => 0,
                    'return_format' => 'id',
                    'preview_size' => 'medium',
                    'library' => 'all',
                    'min' => 0,
                    'max' => 10,
                ),
            ),
            'location' => array(
                array(
                    array(
                        'param' => 'post_type',
                        'operator' => '==',
                        'value' => 'product',
                    ),
                ),
            ),
            'menu_order' => 1,
            'position' => 'normal',
            'style' => 'default',
            'label_placement' => 'top',
            'instruction_placement' => 'label',
            'active' => true,
        ));
    }
});

/**
 * Native WordPress Meta Box (fallback when ACF is not installed)
 */

// Add meta boxes for products
add_action('add_meta_boxes', function() {
    // Only add if ACF is not active
    if (function_exists('acf_add_local_field_group')) {
        return;
    }
    
    // Product Custom Fields
    add_meta_box(
        'lfa_product_custom_fields',
        __('Product Custom Fields', 'livingfitapparel'),
        'lfa_product_custom_fields_callback',
        'product',
        'normal',
        'high'
    );
    
    // Why You Need This Section
    add_meta_box(
        'lfa_why_you_need_this',
        __('Why You Need This Section', 'livingfitapparel'),
        'lfa_why_you_need_this_callback',
        'product',
        'normal',
        'high'
    );
});

// Product Custom Fields meta box callback
function lfa_product_custom_fields_callback($post) {
    wp_nonce_field('lfa_product_fields_save', 'lfa_product_fields_nonce');
    
    $model_details = get_post_meta($post->ID, '_lfa_model_details', true);
    $care_instructions = get_post_meta($post->ID, '_lfa_care_instructions', true);
    ?>
    <div class="lfa-product-meta-fields">
        <style>
            .lfa-product-meta-fields label { display: block; font-weight: 600; margin: 15px 0 5px; }
            .lfa-product-meta-fields label:first-child { margin-top: 0; }
            .lfa-product-meta-fields input[type="text"] { width: 100%; padding: 8px; }
            .lfa-product-meta-fields .description { color: #666; font-style: italic; margin-top: 5px; }
        </style>
        
        <label for="lfa_model_details"><?php _e('Model Details', 'livingfitapparel'); ?></label>
        <input type="text" id="lfa_model_details" name="lfa_model_details" value="<?php echo esc_attr($model_details); ?>" placeholder="<?php esc_attr_e('Model is 5\'9 and wears a Small - Tall length', 'livingfitapparel'); ?>">
        <p class="description"><?php _e('Enter model details (e.g., "Model is 5\'9 and wears a Small - Tall length").', 'livingfitapparel'); ?></p>
        
        <label for="lfa_care_instructions"><?php _e('Care Instructions', 'livingfitapparel'); ?></label>
        <?php
        wp_editor($care_instructions, 'lfa_care_instructions', array(
            'textarea_name' => 'lfa_care_instructions',
            'textarea_rows' => 6,
            'media_buttons' => false,
            'teeny' => true,
            'quicktags' => true,
        ));
        ?>
        <p class="description"><?php _e('Enter care instructions for this product.', 'livingfitapparel'); ?></p>
    </div>
    <?php
}

// Why You Need This meta box callback
function lfa_why_you_need_this_callback($post) {
    wp_nonce_field('lfa_why_fields_save', 'lfa_why_fields_nonce');
    
    $why_left_image = get_post_meta($post->ID, '_lfa_why_left_image', true);
    $why_right_image = get_post_meta($post->ID, '_lfa_why_right_image', true);
    $why_length = get_post_meta($post->ID, '_lfa_why_length', true);
    $why_material = get_post_meta($post->ID, '_lfa_why_material', true);
    $size_chart_images = get_post_meta($post->ID, '_lfa_size_chart_images', true);
    ?>
    <div class="lfa-why-meta-fields">
        <style>
            .lfa-why-meta-fields { padding: 10px 0; }
            .lfa-why-meta-fields label { display: block; font-weight: 600; margin: 15px 0 8px; }
            .lfa-why-meta-fields label:first-child { margin-top: 0; }
            .lfa-why-meta-fields input[type="text"] { width: 100%; padding: 8px; max-width: 400px; }
            .lfa-why-meta-fields .description { color: #666; font-style: italic; margin-top: 5px; font-size: 12px; }
            .lfa-why-meta-fields .image-preview { margin-top: 10px; max-width: 200px; }
            .lfa-why-meta-fields .image-preview img { max-width: 100%; height: auto; border: 1px solid #ddd; }
            .lfa-why-meta-fields .image-upload-btn { margin-top: 8px; }
            .lfa-why-meta-fields .image-remove-btn { margin-left: 10px; color: #a00; cursor: pointer; }
            .lfa-why-meta-fields .gallery-preview { display: flex; flex-wrap: wrap; gap: 10px; margin-top: 10px; }
            .lfa-why-meta-fields .gallery-item { position: relative; width: 100px; }
            .lfa-why-meta-fields .gallery-item img { width: 100%; height: auto; border: 1px solid #ddd; }
            .lfa-why-meta-fields .gallery-item .remove-gallery-item { position: absolute; top: -8px; right: -8px; background: #a00; color: #fff; border-radius: 50%; width: 20px; height: 20px; text-align: center; line-height: 18px; cursor: pointer; font-size: 14px; }
            .lfa-field-row { display: flex; gap: 30px; flex-wrap: wrap; }
            .lfa-field-col { flex: 1; min-width: 300px; }
        </style>
        
        <div class="lfa-field-row">
            <div class="lfa-field-col">
                <label><?php _e('Left Image', 'livingfitapparel'); ?></label>
                <input type="hidden" id="lfa_why_left_image" name="lfa_why_left_image" value="<?php echo esc_attr($why_left_image); ?>">
                <div class="image-preview" id="why_left_image_preview">
                    <?php if ($why_left_image): ?>
                        <?php echo wp_get_attachment_image($why_left_image, 'medium'); ?>
                    <?php endif; ?>
                </div>
                <button type="button" class="button image-upload-btn" data-target="lfa_why_left_image" data-preview="why_left_image_preview"><?php _e('Upload Image', 'livingfitapparel'); ?></button>
                <span class="image-remove-btn" data-target="lfa_why_left_image" data-preview="why_left_image_preview" <?php echo !$why_left_image ? 'style="display:none;"' : ''; ?>><?php _e('Remove', 'livingfitapparel'); ?></span>
                <p class="description"><?php _e('Upload the left image for "Why you need this" section.', 'livingfitapparel'); ?></p>
            </div>
            
            <div class="lfa-field-col">
                <label><?php _e('Right Image', 'livingfitapparel'); ?></label>
                <input type="hidden" id="lfa_why_right_image" name="lfa_why_right_image" value="<?php echo esc_attr($why_right_image); ?>">
                <div class="image-preview" id="why_right_image_preview">
                    <?php if ($why_right_image): ?>
                        <?php echo wp_get_attachment_image($why_right_image, 'medium'); ?>
                    <?php endif; ?>
                </div>
                <button type="button" class="button image-upload-btn" data-target="lfa_why_right_image" data-preview="why_right_image_preview"><?php _e('Upload Image', 'livingfitapparel'); ?></button>
                <span class="image-remove-btn" data-target="lfa_why_right_image" data-preview="why_right_image_preview" <?php echo !$why_right_image ? 'style="display:none;"' : ''; ?>><?php _e('Remove', 'livingfitapparel'); ?></span>
                <p class="description"><?php _e('Upload the right image for "Why you need this" section.', 'livingfitapparel'); ?></p>
            </div>
        </div>
        
        <label for="lfa_why_length"><?php _e('Length', 'livingfitapparel'); ?></label>
        <input type="text" id="lfa_why_length" name="lfa_why_length" value="<?php echo esc_attr($why_length); ?>" placeholder="<?php esc_attr_e('Full Length', 'livingfitapparel'); ?>">
        <p class="description"><?php _e('Enter the length value (e.g., "Full Length").', 'livingfitapparel'); ?></p>
        
        <label for="lfa_why_material"><?php _e('Material', 'livingfitapparel'); ?></label>
        <input type="text" id="lfa_why_material" name="lfa_why_material" value="<?php echo esc_attr($why_material); ?>" placeholder="<?php esc_attr_e('Ultra Flex Double', 'livingfitapparel'); ?>">
        <p class="description"><?php _e('Enter the material value (e.g., "Ultra Flex Double").', 'livingfitapparel'); ?></p>
        
        <label><?php _e('Size Chart Images', 'livingfitapparel'); ?></label>
        <input type="hidden" id="lfa_size_chart_images" name="lfa_size_chart_images" value="<?php echo esc_attr($size_chart_images); ?>">
        <div class="gallery-preview" id="size_chart_gallery_preview">
            <?php 
            if ($size_chart_images) {
                $image_ids = explode(',', $size_chart_images);
                foreach ($image_ids as $image_id) {
                    if ($image_id) {
                        echo '<div class="gallery-item" data-id="' . esc_attr($image_id) . '">';
                        echo wp_get_attachment_image($image_id, 'thumbnail');
                        echo '<span class="remove-gallery-item">&times;</span>';
                        echo '</div>';
                    }
                }
            }
            ?>
        </div>
        <button type="button" class="button gallery-upload-btn" data-target="lfa_size_chart_images" data-preview="size_chart_gallery_preview"><?php _e('Add Size Chart Images', 'livingfitapparel'); ?></button>
        <p class="description"><?php _e('Upload size chart images (multiple allowed). These will be shown in the Size Chart tab.', 'livingfitapparel'); ?></p>
    </div>
    
    <script>
    jQuery(document).ready(function($) {
        // Single image upload
        $('.image-upload-btn').on('click', function(e) {
            e.preventDefault();
            var button = $(this);
            var targetInput = $('#' + button.data('target'));
            var previewDiv = $('#' + button.data('preview'));
            var removeBtn = button.siblings('.image-remove-btn');
            
            var frame = wp.media({
                title: '<?php _e('Select Image', 'livingfitapparel'); ?>',
                button: { text: '<?php _e('Use Image', 'livingfitapparel'); ?>' },
                multiple: false
            });
            
            frame.on('select', function() {
                var attachment = frame.state().get('selection').first().toJSON();
                targetInput.val(attachment.id);
                previewDiv.html('<img src="' + attachment.sizes.medium.url + '">');
                removeBtn.show();
            });
            
            frame.open();
        });
        
        // Remove single image
        $('.image-remove-btn').on('click', function(e) {
            e.preventDefault();
            var targetInput = $('#' + $(this).data('target'));
            var previewDiv = $('#' + $(this).data('preview'));
            targetInput.val('');
            previewDiv.html('');
            $(this).hide();
        });
        
        // Gallery upload
        $('.gallery-upload-btn').on('click', function(e) {
            e.preventDefault();
            var button = $(this);
            var targetInput = $('#' + button.data('target'));
            var previewDiv = $('#' + button.data('preview'));
            
            var frame = wp.media({
                title: '<?php _e('Select Size Chart Images', 'livingfitapparel'); ?>',
                button: { text: '<?php _e('Add Images', 'livingfitapparel'); ?>' },
                multiple: true
            });
            
            frame.on('select', function() {
                var attachments = frame.state().get('selection').toJSON();
                var currentIds = targetInput.val() ? targetInput.val().split(',') : [];
                
                attachments.forEach(function(attachment) {
                    if (currentIds.indexOf(attachment.id.toString()) === -1) {
                        currentIds.push(attachment.id);
                        var thumbUrl = attachment.sizes.thumbnail ? attachment.sizes.thumbnail.url : attachment.url;
                        previewDiv.append('<div class="gallery-item" data-id="' + attachment.id + '"><img src="' + thumbUrl + '"><span class="remove-gallery-item">&times;</span></div>');
                    }
                });
                
                targetInput.val(currentIds.join(','));
            });
            
            frame.open();
        });
        
        // Remove gallery item
        $(document).on('click', '.remove-gallery-item', function() {
            var item = $(this).closest('.gallery-item');
            var galleryPreview = item.closest('.gallery-preview');
            var targetInput = $('#' + galleryPreview.next('.gallery-upload-btn').data('target'));
            var imageId = item.data('id').toString();
            var currentIds = targetInput.val().split(',');
            
            currentIds = currentIds.filter(function(id) {
                return id !== imageId;
            });
            
            targetInput.val(currentIds.join(','));
            item.remove();
        });
    });
    </script>
    <?php
}

// Save meta box data
add_action('save_post_product', function($post_id) {
    // Check nonce for product fields
    if (isset($_POST['lfa_product_fields_nonce']) && wp_verify_nonce($_POST['lfa_product_fields_nonce'], 'lfa_product_fields_save')) {
        // Check autosave
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }
        
        // Check permissions
        if (!current_user_can('edit_post', $post_id)) {
            return;
        }
        
        // Save model details
        if (isset($_POST['lfa_model_details'])) {
            update_post_meta($post_id, '_lfa_model_details', sanitize_text_field($_POST['lfa_model_details']));
        }
        
        // Save care instructions
        if (isset($_POST['lfa_care_instructions'])) {
            update_post_meta($post_id, '_lfa_care_instructions', wp_kses_post($_POST['lfa_care_instructions']));
        }
    }
    
    // Check nonce for why fields
    if (isset($_POST['lfa_why_fields_nonce']) && wp_verify_nonce($_POST['lfa_why_fields_nonce'], 'lfa_why_fields_save')) {
        // Check autosave
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }
        
        // Check permissions
        if (!current_user_can('edit_post', $post_id)) {
            return;
        }
        
        // Save why you need this fields
        if (isset($_POST['lfa_why_left_image'])) {
            update_post_meta($post_id, '_lfa_why_left_image', absint($_POST['lfa_why_left_image']));
        }
        
        if (isset($_POST['lfa_why_right_image'])) {
            update_post_meta($post_id, '_lfa_why_right_image', absint($_POST['lfa_why_right_image']));
        }
        
        if (isset($_POST['lfa_why_length'])) {
            update_post_meta($post_id, '_lfa_why_length', sanitize_text_field($_POST['lfa_why_length']));
        }
        
        if (isset($_POST['lfa_why_material'])) {
            update_post_meta($post_id, '_lfa_why_material', sanitize_text_field($_POST['lfa_why_material']));
        }
        
        if (isset($_POST['lfa_size_chart_images'])) {
            update_post_meta($post_id, '_lfa_size_chart_images', sanitize_text_field($_POST['lfa_size_chart_images']));
        }
    }
});

/**
 * Helper function to get product custom fields
 * Works with both ACF and native meta boxes
 */
function lfa_get_product_model_details($product_id = null) {
    if (!$product_id) {
        $product_id = get_the_ID();
    }
    
    // Try ACF first
    if (function_exists('get_field')) {
        $value = get_field('model_details', $product_id);
        if ($value) {
            return $value;
        }
    }
    
    // Fall back to native meta
    return get_post_meta($product_id, '_lfa_model_details', true);
}

function lfa_get_product_care_instructions($product_id = null) {
    if (!$product_id) {
        $product_id = get_the_ID();
    }
    
    // Try ACF first
    if (function_exists('get_field')) {
        $value = get_field('care_instructions', $product_id);
        if ($value) {
            return $value;
        }
    }
    
    // Fall back to native meta
    return get_post_meta($product_id, '_lfa_care_instructions', true);
}

// Why You Need This helper functions
function lfa_get_why_left_image($product_id = null) {
    if (!$product_id) {
        $product_id = get_the_ID();
    }
    
    if (function_exists('get_field')) {
        $value = get_field('why_left_image', $product_id);
        if ($value) {
            return $value;
        }
    }
    
    return get_post_meta($product_id, '_lfa_why_left_image', true);
}

function lfa_get_why_right_image($product_id = null) {
    if (!$product_id) {
        $product_id = get_the_ID();
    }
    
    if (function_exists('get_field')) {
        $value = get_field('why_right_image', $product_id);
        if ($value) {
            return $value;
        }
    }
    
    return get_post_meta($product_id, '_lfa_why_right_image', true);
}

function lfa_get_why_length($product_id = null) {
    if (!$product_id) {
        $product_id = get_the_ID();
    }
    
    if (function_exists('get_field')) {
        $value = get_field('why_length', $product_id);
        if ($value) {
            return $value;
        }
    }
    
    return get_post_meta($product_id, '_lfa_why_length', true);
}

function lfa_get_why_material($product_id = null) {
    if (!$product_id) {
        $product_id = get_the_ID();
    }
    
    if (function_exists('get_field')) {
        $value = get_field('why_material', $product_id);
        if ($value) {
            return $value;
        }
    }
    
    return get_post_meta($product_id, '_lfa_why_material', true);
}

function lfa_get_size_chart_images($product_id = null) {
    if (!$product_id) {
        $product_id = get_the_ID();
    }
    
    if (function_exists('get_field')) {
        $value = get_field('size_chart_images', $product_id);
        if ($value) {
            return is_array($value) ? $value : array($value);
        }
    }
    
    $images = get_post_meta($product_id, '_lfa_size_chart_images', true);
    if ($images) {
        return array_filter(explode(',', $images));
    }
    
    return array();
}
