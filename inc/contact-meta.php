<?php
/**
 * Contact Page Meta Box
 * 
 * Adds a dropdown to select Contact Form 7 form for the contact page template
 */

if (!defined('ABSPATH')) exit;

/**
 * Add Contact Form meta box
 */
function lfa_add_contact_meta_box() {
    global $post;
    
    // Only show on pages using "Contact" template
    $template = get_page_template_slug($post->ID);
    if ($template !== 'page-contact.php') {
        return;
    }
    
    add_meta_box(
        'lfa_contact_form_meta_box',
        __('Contact Form Selection', 'livingfitapparel'),
        'lfa_contact_form_meta_box_callback',
        'page',
        'normal',
        'high'
    );
}
add_action('add_meta_boxes', 'lfa_add_contact_meta_box');

/**
 * Contact Form meta box callback
 */
function lfa_contact_form_meta_box_callback($post) {
    wp_nonce_field('lfa_contact_form_meta_box', 'lfa_contact_form_meta_box_nonce');
    
    $selected_form_id = get_post_meta($post->ID, '_lfa_contact_form_id', true);
    
    // Get all Contact Form 7 forms
    $forms = array();
    if (class_exists('WPCF7_ContactForm')) {
        $cf7_forms = WPCF7_ContactForm::find();
        foreach ($cf7_forms as $form) {
            $forms[] = array(
                'id' => $form->id(),
                'title' => $form->title()
            );
        }
    }
    
    ?>
    <div class="lfa-contact-meta-box">
        <p><strong><?php _e('Select Contact Form 7 Form', 'livingfitapparel'); ?></strong></p>
        <p><?php _e('Choose which Contact Form 7 form to display on this contact page.', 'livingfitapparel'); ?></p>
        
        <?php if (empty($forms)): ?>
            <p style="color: #d63638;">
                <strong><?php _e('No Contact Form 7 forms found.', 'livingfitapparel'); ?></strong><br>
                <?php _e('Please create a form using Contact Form 7 plugin first.', 'livingfitapparel'); ?>
            </p>
        <?php else: ?>
            <select name="lfa_contact_form_id" id="lfa_contact_form_id" style="width: 100%; max-width: 500px;">
                <option value=""><?php _e('-- Select a Form --', 'livingfitapparel'); ?></option>
                <?php foreach ($forms as $form): ?>
                    <option value="<?php echo esc_attr($form['id']); ?>" <?php selected($selected_form_id, $form['id']); ?>>
                        <?php echo esc_html($form['title']); ?> (ID: <?php echo esc_html($form['id']); ?>)
                    </option>
                <?php endforeach; ?>
            </select>
            <?php if ($selected_form_id): ?>
                <p style="margin-top: 10px;">
                    <strong><?php _e('Shortcode:', 'livingfitapparel'); ?></strong> 
                    <code>[contact-form-7 id="<?php echo esc_attr($selected_form_id); ?>"]</code>
                </p>
            <?php endif; ?>
        <?php endif; ?>
    </div>
    <?php
}

/**
 * Save Contact Form meta box data
 */
function lfa_save_contact_form_meta_box($post_id) {
    // Check nonce
    if (!isset($_POST['lfa_contact_form_meta_box_nonce']) || !wp_verify_nonce($_POST['lfa_contact_form_meta_box_nonce'], 'lfa_contact_form_meta_box')) {
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
    
    // Save form ID
    if (isset($_POST['lfa_contact_form_id'])) {
        $form_id = sanitize_text_field($_POST['lfa_contact_form_id']);
        if (!empty($form_id)) {
            update_post_meta($post_id, '_lfa_contact_form_id', $form_id);
        } else {
            delete_post_meta($post_id, '_lfa_contact_form_id');
        }
    }
}
add_action('save_post', 'lfa_save_contact_form_meta_box');

