<?php
/**
 * FAQ Meta Box - Native WordPress implementation
 * 
 * Adds a custom meta box for FAQ pages to manage FAQs without ACF
 */

if (!defined('ABSPATH')) exit;

/**
 * Add FAQ meta box
 */
function lfa_add_faq_meta_box() {
    add_meta_box(
        'lfa_faq_meta_box',
        'FAQs',
        'lfa_faq_meta_box_callback',
        'page',
        'normal',
        'high'
    );
}
add_action('add_meta_boxes', 'lfa_add_faq_meta_box');

/**
 * FAQ meta box callback
 */
function lfa_faq_meta_box_callback($post) {
    wp_nonce_field('lfa_faq_meta_box', 'lfa_faq_meta_box_nonce');
    
    $faqs = get_post_meta($post->ID, '_lfa_faqs', true);
    if (!is_array($faqs)) {
        $faqs = array();
    }
    
    ?>
    <div id="lfa-faq-container">
        <p><strong>Add FAQs for this page. Each FAQ has a title and description.</strong></p>
        <p><em>Note: This meta box is for pages using the "FAQ" template. FAQs will only display on the frontend if the page uses the FAQ template.</em></p>
        <div id="lfa-faq-items">
            <?php
            if (!empty($faqs)) {
                foreach ($faqs as $index => $faq) {
                    lfa_render_faq_item($index, $faq);
                }
            }
            ?>
        </div>
        <p>
            <button type="button" id="lfa-add-faq" class="button button-secondary">+ Add FAQ</button>
        </p>
    </div>
    
    <script type="text/javascript">
    jQuery(document).ready(function($) {
        var faqIndex = <?php echo count($faqs); ?>;
        
        $('#lfa-add-faq').on('click', function() {
            var editorId = 'lfa_faq_description_' + faqIndex;
            var wrapper = $('<div class="lfa-faq-item-wrapper" style="border: 1px solid #ddd; padding: 15px; margin-bottom: 15px; background: #f9f9f9;"></div>');
            
            var html = '<p><strong>FAQ ' + (faqIndex + 1) + '</strong> <button type="button" class="button button-link lfa-remove-faq" style="color: #a00;">Remove</button></p>';
            html += '<p><label><strong>Title:</strong><br><input type="text" name="lfa_faqs[' + faqIndex + '][title]" value="" style="width: 100%;" /></label></p>';
            html += '<p><label><strong>Description:</strong><br><div id="' + editorId + '-wrap"><textarea id="' + editorId + '" name="lfa_faqs[' + faqIndex + '][description]" rows="8" class="wp-editor-area"></textarea></div></label></p>';
            
            wrapper.html(html);
            $('#lfa-faq-items').append(wrapper);
            
            // Initialize WordPress editor
            if (typeof wp !== 'undefined' && wp.editor && wp.editor.initialize) {
                wp.editor.initialize(editorId, {
                    tinymce: {
                        height: 200,
                        menubar: false,
                        plugins: 'lists, link, paste',
                        toolbar: 'bold italic | bullist numlist | link',
                        branding: false,
                        resize: false
                    },
                    quicktags: true
                });
            } else if (typeof tinyMCE !== 'undefined' && tinyMCE.init) {
                // Fallback to direct TinyMCE initialization
                tinyMCE.init({
                    selector: '#' + editorId,
                    height: 200,
                    menubar: false,
                    plugins: 'lists, link, paste',
                    toolbar: 'bold italic | bullist numlist | link',
                    branding: false,
                    resize: false
                });
            }
            
            faqIndex++;
        });
        
        $(document).on('click', '.lfa-remove-faq', function() {
            var wrapper = $(this).closest('.lfa-faq-item-wrapper');
            var editorId = wrapper.find('textarea').attr('id');
            
            // Remove editor instance before removing the element
            if (typeof wp !== 'undefined' && wp.editor && wp.editor.remove && editorId) {
                wp.editor.remove(editorId);
            } else if (typeof tinyMCE !== 'undefined' && editorId) {
                var editor = tinyMCE.get(editorId);
                if (editor) {
                    editor.remove();
                }
            }
            
            wrapper.remove();
        });
    });
    </script>
    
    <style>
    .lfa-faq-item-wrapper {
        border: 1px solid #ddd;
        padding: 15px;
        margin-bottom: 15px;
        background: #f9f9f9;
    }
    .lfa-faq-item-wrapper label {
        display: block;
        margin-bottom: 5px;
    }
    .lfa-faq-item-wrapper input[type="text"] {
        width: 100%;
    }
    .lfa-faq-item-wrapper .wp-editor-container {
        width: 100%;
    }
    .lfa-faq-item-wrapper .wp-editor-area {
        width: 100%;
    }
    </style>
    <?php
}

/**
 * Render a single FAQ item in the meta box
 */
function lfa_render_faq_item($index, $faq) {
    $title = isset($faq['title']) ? esc_attr($faq['title']) : '';
    $description = isset($faq['description']) ? $faq['description'] : '';
    $editor_id = 'lfa_faq_description_' . $index;
    ?>
    <div class="lfa-faq-item-wrapper" style="border: 1px solid #ddd; padding: 15px; margin-bottom: 15px; background: #f9f9f9;">
        <p><strong>FAQ <?php echo ($index + 1); ?></strong> <button type="button" class="button button-link lfa-remove-faq" style="color: #a00;">Remove</button></p>
        <p>
            <label><strong>Title:</strong><br>
                <input type="text" name="lfa_faqs[<?php echo $index; ?>][title]" value="<?php echo $title; ?>" style="width: 100%;" />
            </label>
        </p>
        <p>
            <label><strong>Description:</strong><br>
                <?php
                wp_editor($description, $editor_id, array(
                    'textarea_name' => 'lfa_faqs[' . $index . '][description]',
                    'textarea_rows' => 8,
                    'media_buttons' => false,
                    'teeny' => true,
                    'quicktags' => false,
                ));
                ?>
            </label>
        </p>
    </div>
    <?php
}

/**
 * Save FAQ meta box data
 */
function lfa_save_faq_meta_box($post_id) {
    // Check if nonce is set
    if (!isset($_POST['lfa_faq_meta_box_nonce'])) {
        return;
    }
    
    // Verify nonce
    if (!wp_verify_nonce($_POST['lfa_faq_meta_box_nonce'], 'lfa_faq_meta_box')) {
        return;
    }
    
    // Check if autosave
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }
    
    // Check user permissions
    if (!current_user_can('edit_page', $post_id)) {
        return;
    }
    
    // Save FAQs
    if (isset($_POST['lfa_faqs']) && is_array($_POST['lfa_faqs'])) {
        $faqs = array();
        
        foreach ($_POST['lfa_faqs'] as $faq) {
            if (!empty($faq['title'])) {
                $faqs[] = array(
                    'title' => sanitize_text_field($faq['title']),
                    'description' => wp_kses_post($faq['description'])
                );
            }
        }
        
        update_post_meta($post_id, '_lfa_faqs', $faqs);
    } else {
        delete_post_meta($post_id, '_lfa_faqs');
    }
}
add_action('save_post', 'lfa_save_faq_meta_box');

