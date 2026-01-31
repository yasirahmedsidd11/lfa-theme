<?php
/**
 * Popup System
 * 
 * Lightweight popup engine with PHP condition evaluation and JavaScript triggers.
 * 
 * Architecture:
 * - Custom Post Type for popup management
 * - PHP templates for popup markup
 * - Condition evaluation in PHP (audience, location)
 * - JavaScript for triggers and frequency control
 * - Priority-based conflict resolution
 */

if (!defined('ABSPATH')) exit;

/**
 * Template Registry
 * 
 * Register available popup templates here.
 * Format: 'slug' => 'Display Name'
 */
function lfa_popup_get_templates() {
	return apply_filters('lfa_popup_templates', [
		'first-time-visitor' => __('First Time Visitor', 'livingfitapparel'),
		'logged-in-offer'    => __('Logged In Offer', 'livingfitapparel'),
	]);
}

/**
 * Register Popup Custom Post Type
 */
add_action('init', function() {
	register_post_type('popup', [
		'labels' => [
			'name'               => __('Popups', 'livingfitapparel'),
			'singular_name'      => __('Popup', 'livingfitapparel'),
			'add_new'            => __('Add New', 'livingfitapparel'),
			'add_new_item'       => __('Add New Popup', 'livingfitapparel'),
			'edit_item'          => __('Edit Popup', 'livingfitapparel'),
			'new_item'           => __('New Popup', 'livingfitapparel'),
			'view_item'          => __('View Popup', 'livingfitapparel'),
			'search_items'       => __('Search Popups', 'livingfitapparel'),
			'not_found'          => __('No popups found', 'livingfitapparel'),
			'not_found_in_trash' => __('No popups found in Trash', 'livingfitapparel'),
		],
		'public'              => false,
		'publicly_queryable'  => false,
		'show_ui'             => true,
		'show_in_menu'        => true,
		'menu_icon'           => 'dashicons-megaphone',
		'query_var'           => false,
		'rewrite'             => false,
		'capability_type'     => 'post',
		'has_archive'         => false,
		'hierarchical'        => false,
		'supports'            => ['title', 'thumbnail'],
		'show_in_rest'        => false,
	]);
});

/**
 * Register Popup Meta Boxes
 */
add_action('add_meta_boxes', function() {
	add_meta_box(
		'lfa_popup_settings',
		__('Popup Settings', 'livingfitapparel'),
		'lfa_popup_settings_callback',
		'popup',
		'normal',
		'high'
	);
});

/**
 * Add Custom Columns to Popup Listing
 */
add_filter('manage_popup_posts_columns', function($columns) {
	// Remove date column and add custom ones
	unset($columns['date']);
	
	// Add custom columns
	$new_columns = [];
	$new_columns['cb'] = $columns['cb'];
	$new_columns['title'] = $columns['title'];
	$new_columns['template'] = __('Template', 'livingfitapparel');
	$new_columns['priority'] = __('Priority', 'livingfitapparel');
	$new_columns['audience'] = __('Audience', 'livingfitapparel');
	$new_columns['location'] = __('Location', 'livingfitapparel');
	$new_columns['trigger'] = __('Trigger', 'livingfitapparel');
	$new_columns['frequency'] = __('Frequency', 'livingfitapparel');
	$new_columns['status'] = __('Status', 'livingfitapparel');
	$new_columns['preview'] = __('Preview', 'livingfitapparel');
	$new_columns['date'] = $columns['date'];
	
	return $new_columns;
});

/**
 * Populate Custom Columns
 */
add_action('manage_popup_posts_custom_column', function($column, $post_id) {
	switch ($column) {
		case 'thumbnail':
			if (has_post_thumbnail($post_id)) {
				echo get_the_post_thumbnail($post_id, 'thumbnail', ['style' => 'max-width: 60px; height: auto;']);
			} else {
				echo '<span style="color: #999;">—</span>';
			}
			break;
			
		case 'template':
			$template = get_post_meta($post_id, '_lfa_popup_template', true);
			if ($template) {
				$templates = lfa_popup_get_templates();
				echo esc_html($templates[$template] ?? $template);
			} else {
				echo '<span style="color: #d63638;">' . __('Not Set', 'livingfitapparel') . '</span>';
			}
			break;
			
		case 'priority':
			$priority = get_post_meta($post_id, '_lfa_popup_priority', true);
			echo esc_html($priority ?: '10');
			break;
			
		case 'audience':
			$login_state = get_post_meta($post_id, '_lfa_popup_login_state', true);
			$customer_state = get_post_meta($post_id, '_lfa_popup_customer_state', true);
			
			$audience_parts = [];
			if ($login_state) {
				$login_labels = [
					'all' => __('All Users', 'livingfitapparel'),
					'logged_in' => __('Logged In', 'livingfitapparel'),
					'logged_out' => __('Logged Out', 'livingfitapparel'),
				];
				$audience_parts[] = $login_labels[$login_state] ?? $login_state;
			}
			
			if ($customer_state && $customer_state !== 'any' && $login_state === 'logged_in') {
				$customer_labels = [
					'has_orders' => __('Has Orders', 'livingfitapparel'),
					'no_orders' => __('No Orders', 'livingfitapparel'),
				];
				$audience_parts[] = $customer_labels[$customer_state] ?? $customer_state;
			}
			
			echo esc_html(implode(' + ', $audience_parts) ?: '—');
			break;
			
		case 'location':
			$location_mode = get_post_meta($post_id, '_lfa_popup_location_mode', true);
			$location_pages = get_post_meta($post_id, '_lfa_popup_location_pages', true);
			
			if (!$location_mode) {
				echo '<span style="color: #d63638;">' . __('Not Set', 'livingfitapparel') . '</span>';
				break;
			}
			
			$location_labels = [
				'entire_site' => __('Entire Site', 'livingfitapparel'),
				'front_page' => __('Front Page', 'livingfitapparel'),
				'blog_pages' => __('Blog Pages', 'livingfitapparel'),
				'shop_pages' => __('Shop Pages', 'livingfitapparel'),
				'specific_pages' => __('Specific Pages', 'livingfitapparel'),
			];
			
			$location_text = $location_labels[$location_mode] ?? $location_mode;
			
			if ($location_mode === 'specific_pages' && !empty($location_pages) && is_array($location_pages)) {
				$page_count = count($location_pages);
				$location_text .= ' (' . $page_count . ' ' . _n('page', 'pages', $page_count, 'livingfitapparel') . ')';
			}
			
			echo esc_html($location_text);
			break;
			
		case 'trigger':
			$trigger_type = get_post_meta($post_id, '_lfa_popup_trigger_type', true);
			$scroll_percent = get_post_meta($post_id, '_lfa_popup_trigger_scroll_percent', true);
			
			if (!$trigger_type) {
				echo '<span style="color: #d63638;">' . __('Not Set', 'livingfitapparel') . '</span>';
				break;
			}
			
			$trigger_labels = [
				'page_load' => __('Page Load', 'livingfitapparel'),
				'scroll_percent' => __('Scroll', 'livingfitapparel'),
			];
			
			$trigger_text = $trigger_labels[$trigger_type] ?? $trigger_type;
			
			if ($trigger_type === 'scroll_percent' && $scroll_percent) {
				$trigger_text .= ' ' . intval($scroll_percent) . '%';
			}
			
			echo esc_html($trigger_text);
			break;
			
		case 'frequency':
			$frequency_type = get_post_meta($post_id, '_lfa_popup_frequency_type', true);
			
			if (!$frequency_type) {
				echo '<span style="color: #d63638;">' . __('Not Set', 'livingfitapparel') . '</span>';
				break;
			}
			
			$frequency_labels = [
				'every_time' => __('Every Time', 'livingfitapparel'),
				'once_ever' => __('Once Ever', 'livingfitapparel'),
				'once_per_session' => __('Once Per Session', 'livingfitapparel'),
			];
			
			echo esc_html($frequency_labels[$frequency_type] ?? $frequency_type);
			break;
			
		case 'status':
			$post_status = get_post_status($post_id);
			$qualified = lfa_popup_get_qualified();
			$is_active = ($qualified && $qualified['id'] == $post_id);
			
			// Check if popup would qualify (even if not currently active)
			$would_qualify = false;
			if ($post_status === 'publish') {
				if (lfa_popup_evaluate_audience($post_id) && lfa_popup_evaluate_location($post_id)) {
					$would_qualify = true;
				}
			}
			
			if ($is_active) {
				echo '<span style="color: #00a32a; font-weight: 600;">● ' . __('Active', 'livingfitapparel') . '</span>';
			} elseif ($post_status === 'publish' && $would_qualify) {
				echo '<span style="color: #2271b1;">○ ' . __('Qualified', 'livingfitapparel') . '</span>';
			} elseif ($post_status === 'publish') {
				echo '<span style="color: #d63638;">○ ' . __('Not Qualified', 'livingfitapparel') . '</span>';
			} else {
				echo '<span style="color: #999;">— ' . ucfirst($post_status) . '</span>';
			}
			break;
			
		case 'preview':
			$preview_url = admin_url('admin-ajax.php?action=lfa_popup_preview&popup_id=' . $post_id);
			echo '<a href="' . esc_url($preview_url) . '" class="button button-small lfa-popup-preview-btn" target="_blank" data-popup-id="' . esc_attr($post_id) . '">' . __('Preview', 'livingfitapparel') . '</a>';
			break;
	}
}, 10, 2);

/**
 * Make Priority Column Sortable
 */
add_filter('manage_edit-popup_sortable_columns', function($columns) {
	$columns['priority'] = 'priority';
	return $columns;
});

/**
 * Handle Priority Column Sorting
 */
add_action('pre_get_posts', function($query) {
	if (!is_admin() || !$query->is_main_query()) {
		return;
	}
	
	if ($query->get('post_type') === 'popup' && $query->get('orderby') === 'priority') {
		$query->set('meta_key', '_lfa_popup_priority');
		$query->set('orderby', 'meta_value_num');
	}
});

/**
 * Enqueue Admin Styles and Scripts for Popup Listing
 */
add_action('admin_enqueue_scripts', function($hook) {
	if ($hook !== 'edit.php') {
		return;
	}
	
	global $post_type;
	if ($post_type !== 'popup') {
		return;
	}
	
	// Enqueue popup CSS and JS for preview
	wp_enqueue_style('lfa-popups', LFA_URI . '/assets/css/popups.css', [], LFA_VER);
	wp_enqueue_script('jquery');
	wp_enqueue_script('lfa-popups', LFA_URI . '/assets/js/popups.js', ['jquery'], LFA_VER, true);
	
	// Localize script
	wp_localize_script('lfa-popups', 'LFA', array(
		'ajaxUrl' => admin_url('admin-ajax.php'),
		'nonce'   => wp_create_nonce('lfa-nonce'),
	));
	
	// Add inline styles for admin columns
	wp_add_inline_style('lfa-popups', '
		.wp-list-table .column-template { width: 120px; }
		.wp-list-table .column-priority { width: 80px; }
		.wp-list-table .column-audience { width: 140px; }
		.wp-list-table .column-location { width: 140px; }
		.wp-list-table .column-trigger { width: 100px; }
		.wp-list-table .column-frequency { width: 120px; }
		.wp-list-table .column-status { width: 120px; }
		.wp-list-table .column-preview { width: 100px; }
		.wp-list-table td.column-status { font-size: 12px; }
		.wp-list-table td.column-audience,
		.wp-list-table td.column-location { font-size: 12px; line-height: 1.5; }
	');
});

/**
 * AJAX Handler for Popup Preview
 */
add_action('wp_ajax_lfa_popup_preview', 'lfa_popup_preview_handler');

function lfa_popup_preview_handler() {
	// Check permissions
	if (!current_user_can('edit_posts')) {
		wp_die(__('You do not have permission to preview popups.', 'livingfitapparel'));
	}
	
	$popup_id = isset($_GET['popup_id']) ? intval($_GET['popup_id']) : 0;
	
	if (!$popup_id) {
		wp_die(__('Invalid popup ID.', 'livingfitapparel'));
	}
	
	// Get popup data
	$popup = get_post($popup_id);
	
	if (!$popup || $popup->post_type !== 'popup') {
		wp_die(__('Popup not found.', 'livingfitapparel'));
	}
	
	// Get popup settings
	$template = get_post_meta($popup_id, '_lfa_popup_template', true);
	$trigger_type = get_post_meta($popup_id, '_lfa_popup_trigger_type', true);
	$trigger_config = [
		'scroll_percent' => get_post_meta($popup_id, '_lfa_popup_trigger_scroll_percent', true),
	];
	$frequency_type = get_post_meta($popup_id, '_lfa_popup_frequency_type', true);
	
	if (empty($template)) {
		wp_die(__('Popup template not set.', 'livingfitapparel'));
	}
	
	// Build popup data array
	$popup_data = [
		'id'              => $popup_id,
		'title'           => $popup->post_title,
		'template'        => $template,
		'trigger_type'    => $trigger_type ?: 'page_load',
		'trigger_config'  => $trigger_config,
		'frequency_type'  => $frequency_type ?: 'every_time',
	];
	
	// Render preview page
	?>
	<!DOCTYPE html>
	<html <?php language_attributes(); ?>>
	<head>
		<meta charset="<?php bloginfo('charset'); ?>">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<title><?php echo esc_html($popup->post_title . ' - ' . __('Popup Preview', 'livingfitapparel')); ?></title>
		<?php wp_head(); ?>
		<style>
			body {
				margin: 0;
				padding: 20px;
				background: #f0f0f1;
				font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen-Sans, Ubuntu, Cantarell, "Helvetica Neue", sans-serif;
			}
			.preview-header {
				background: #fff;
				padding: 15px 20px;
				margin-bottom: 20px;
				border-left: 4px solid #2271b1;
				box-shadow: 0 1px 3px rgba(0,0,0,0.1);
			}
			.preview-header h1 {
				margin: 0;
				font-size: 18px;
				font-weight: 600;
			}
			.preview-header .close-preview {
				float: right;
				margin-top: -5px;
			}
		</style>
	</head>
	<body>
		<div class="preview-header">
			<h1><?php echo esc_html($popup->post_title); ?> - <?php _e('Popup Preview', 'livingfitapparel'); ?></h1>
			<a href="javascript:window.close();" class="button close-preview"><?php _e('Close', 'livingfitapparel'); ?></a>
			<div style="clear: both;"></div>
		</div>
		
		<?php
		// Render popup
		lfa_popup_render($popup_data);
		?>
		
		<script>
		// Auto-show popup on preview page (bypass frequency checks)
		jQuery(document).ready(function($) {
			var $popup = $('.lfa-popup-container');
			if ($popup.length) {
				// Remove display:none inline style
				$popup.css('display', 'block');
				// Show immediately
				setTimeout(function() {
					$popup.fadeIn(300);
					$('body').addClass('lfa-popup-open');
				}, 300);
			}
		});
		</script>
		
		<?php wp_footer(); ?>
	</body>
	</html>
	<?php
	exit;
}

/**
 * Popup Settings Meta Box Callback
 */
function lfa_popup_settings_callback($post) {
	wp_nonce_field('lfa_popup_save', 'lfa_popup_nonce');
	
	// Get saved values
	$template = get_post_meta($post->ID, '_lfa_popup_template', true);
	$priority = get_post_meta($post->ID, '_lfa_popup_priority', true);
	$priority = $priority !== '' ? intval($priority) : 10;
	
	// Audience conditions
	$login_state = get_post_meta($post->ID, '_lfa_popup_login_state', true);
	$customer_state = get_post_meta($post->ID, '_lfa_popup_customer_state', true);
	
	// Location conditions
	$location_mode = get_post_meta($post->ID, '_lfa_popup_location_mode', true);
	$location_pages = get_post_meta($post->ID, '_lfa_popup_location_pages', true);
	$location_pages = is_array($location_pages) ? $location_pages : [];
	
	// Trigger settings
	$trigger_type = get_post_meta($post->ID, '_lfa_popup_trigger_type', true);
	$trigger_scroll_percent = get_post_meta($post->ID, '_lfa_popup_trigger_scroll_percent', true);
	$trigger_scroll_percent = $trigger_scroll_percent !== '' ? intval($trigger_scroll_percent) : 50;
	
	// Frequency settings
	$frequency_type = get_post_meta($post->ID, '_lfa_popup_frequency_type', true);
	
	// Get available templates
	$templates = lfa_popup_get_templates();
	
	// Get all pages for location selection
	$all_pages = get_pages(['sort_column' => 'post_title', 'sort_order' => 'ASC']);
	?>
	<div class="lfa-popup-settings">
		<!-- Template Selection -->
		<div class="lfa-popup-field">
			<label for="lfa_popup_template"><strong><?php _e('Template', 'livingfitapparel'); ?></strong></label>
			<select name="lfa_popup_template" id="lfa_popup_template" required>
				<option value=""><?php _e('Select Template', 'livingfitapparel'); ?></option>
				<?php foreach ($templates as $slug => $name): ?>
					<option value="<?php echo esc_attr($slug); ?>" <?php selected($template, $slug); ?>>
						<?php echo esc_html($name); ?>
					</option>
				<?php endforeach; ?>
			</select>
			<p class="description"><?php _e('Select the template to use for this popup.', 'livingfitapparel'); ?></p>
		</div>
		
		<!-- Priority -->
		<div class="lfa-popup-field">
			<label for="lfa_popup_priority"><strong><?php _e('Priority', 'livingfitapparel'); ?></strong></label>
			<input type="number" name="lfa_popup_priority" id="lfa_popup_priority" value="<?php echo esc_attr($priority); ?>" min="1" max="100" required>
			<p class="description"><?php _e('Higher priority popups display first when multiple qualify. Range: 1-100.', 'livingfitapparel'); ?></p>
		</div>
		
		<!-- Audience Conditions -->
		<div class="lfa-popup-section">
			<h3><?php _e('Audience Conditions', 'livingfitapparel'); ?></h3>
			
			<div class="lfa-popup-field">
				<label for="lfa_popup_login_state"><strong><?php _e('Login State', 'livingfitapparel'); ?></strong> <span class="required">*</span></label>
				<select name="lfa_popup_login_state" id="lfa_popup_login_state" required>
					<option value="all" <?php selected($login_state, 'all'); ?>><?php _e('All Users', 'livingfitapparel'); ?></option>
					<option value="logged_in" <?php selected($login_state, 'logged_in'); ?>><?php _e('Logged In Only', 'livingfitapparel'); ?></option>
					<option value="logged_out" <?php selected($login_state, 'logged_out'); ?>><?php _e('Logged Out Only', 'livingfitapparel'); ?></option>
				</select>
			</div>
			
			<div class="lfa-popup-field" id="lfa_customer_state_field" style="<?php echo ($login_state === 'logged_in' || $login_state === '') ? '' : 'display:none;'; ?>">
				<label for="lfa_popup_customer_state"><strong><?php _e('Customer State', 'livingfitapparel'); ?></strong></label>
				<select name="lfa_popup_customer_state" id="lfa_popup_customer_state">
					<option value="any" <?php selected($customer_state, 'any'); ?>><?php _e('Any Customer', 'livingfitapparel'); ?></option>
					<option value="has_orders" <?php selected($customer_state, 'has_orders'); ?>><?php _e('Has Orders', 'livingfitapparel'); ?></option>
					<option value="no_orders" <?php selected($customer_state, 'no_orders'); ?>><?php _e('No Orders', 'livingfitapparel'); ?></option>
				</select>
				<p class="description"><?php _e('Only applies to logged-in users. Requires WooCommerce.', 'livingfitapparel'); ?></p>
			</div>
		</div>
		
		<!-- Location Conditions -->
		<div class="lfa-popup-section">
			<h3><?php _e('Location Conditions', 'livingfitapparel'); ?></h3>
			
			<div class="lfa-popup-field">
				<label for="lfa_popup_location_mode"><strong><?php _e('Location Mode', 'livingfitapparel'); ?></strong> <span class="required">*</span></label>
				<select name="lfa_popup_location_mode" id="lfa_popup_location_mode" required>
					<option value="entire_site" <?php selected($location_mode, 'entire_site'); ?>><?php _e('Entire Site', 'livingfitapparel'); ?></option>
					<option value="front_page" <?php selected($location_mode, 'front_page'); ?>><?php _e('Front Page Only', 'livingfitapparel'); ?></option>
					<option value="blog_pages" <?php selected($location_mode, 'blog_pages'); ?>><?php _e('Blog Pages', 'livingfitapparel'); ?></option>
					<option value="shop_pages" <?php selected($location_mode, 'shop_pages'); ?>><?php _e('Shop Pages (WooCommerce)', 'livingfitapparel'); ?></option>
					<option value="specific_pages" <?php selected($location_mode, 'specific_pages'); ?>><?php _e('Specific Pages', 'livingfitapparel'); ?></option>
				</select>
			</div>
			
			<div class="lfa-popup-field" id="lfa_location_pages_field" style="<?php echo ($location_mode === 'specific_pages') ? '' : 'display:none;'; ?>">
				<label><strong><?php _e('Select Pages', 'livingfitapparel'); ?></strong></label>
				<div style="max-height:200px; overflow-y:auto; border:1px solid #ddd; padding:10px;">
					<?php foreach ($all_pages as $page): ?>
						<label style="display:block; margin-bottom:5px;">
							<input type="checkbox" name="lfa_popup_location_pages[]" value="<?php echo esc_attr($page->ID); ?>" 
								<?php checked(in_array($page->ID, $location_pages)); ?>>
							<?php echo esc_html($page->post_title); ?>
						</label>
					<?php endforeach; ?>
				</div>
			</div>
		</div>
		
		<!-- Trigger Settings -->
		<div class="lfa-popup-section">
			<h3><?php _e('Trigger Settings', 'livingfitapparel'); ?></h3>
			
			<div class="lfa-popup-field">
				<label for="lfa_popup_trigger_type"><strong><?php _e('Trigger Type', 'livingfitapparel'); ?></strong> <span class="required">*</span></label>
				<select name="lfa_popup_trigger_type" id="lfa_popup_trigger_type" required>
					<option value="page_load" <?php selected($trigger_type, 'page_load'); ?>><?php _e('Page Load', 'livingfitapparel'); ?></option>
					<option value="scroll_percent" <?php selected($trigger_type, 'scroll_percent'); ?>><?php _e('Scroll Percentage', 'livingfitapparel'); ?></option>
				</select>
			</div>
			
			<div class="lfa-popup-field" id="lfa_trigger_scroll_field" style="<?php echo ($trigger_type === 'scroll_percent') ? '' : 'display:none;'; ?>">
				<label for="lfa_popup_trigger_scroll_percent"><strong><?php _e('Scroll Percentage', 'livingfitapparel'); ?></strong></label>
				<input type="number" name="lfa_popup_trigger_scroll_percent" id="lfa_popup_trigger_scroll_percent" 
					value="<?php echo esc_attr($trigger_scroll_percent); ?>" min="1" max="100" required>
				<p class="description"><?php _e('Trigger popup when user scrolls this percentage down the page (1-100).', 'livingfitapparel'); ?></p>
			</div>
		</div>
		
		<!-- Frequency Settings -->
		<div class="lfa-popup-section">
			<h3><?php _e('Frequency Settings', 'livingfitapparel'); ?></h3>
			
			<div class="lfa-popup-field">
				<label for="lfa_popup_frequency_type"><strong><?php _e('Frequency Type', 'livingfitapparel'); ?></strong> <span class="required">*</span></label>
				<select name="lfa_popup_frequency_type" id="lfa_popup_frequency_type" required>
					<option value="every_time" <?php selected($frequency_type, 'every_time'); ?>><?php _e('Every Time', 'livingfitapparel'); ?></option>
					<option value="once_ever" <?php selected($frequency_type, 'once_ever'); ?>><?php _e('Once Ever', 'livingfitapparel'); ?></option>
					<option value="once_per_session" <?php selected($frequency_type, 'once_per_session'); ?>><?php _e('Once Per Session', 'livingfitapparel'); ?></option>
				</select>
				<p class="description"><?php _e('Controls how often this popup appears to users.', 'livingfitapparel'); ?></p>
			</div>
		</div>
	</div>
	
	<style>
		.lfa-popup-settings { padding: 20px 0; }
		.lfa-popup-section { margin: 20px 0; padding: 15px; background: #f9f9f9; border-left: 4px solid #2271b1; }
		.lfa-popup-section h3 { margin-top: 0; }
		.lfa-popup-field { margin: 15px 0; }
		.lfa-popup-field label { display: block; margin-bottom: 5px; }
		.lfa-popup-field select,
		.lfa-popup-field input[type="number"] { width: 100%; max-width: 400px; }
		.lfa-popup-field .description { margin-top: 5px; color: #666; font-style: italic; }
		.required { color: #d63638; }
	</style>
	
	<script>
	jQuery(document).ready(function($) {
		// Show/hide customer state field based on login state
		$('#lfa_popup_login_state').on('change', function() {
			if ($(this).val() === 'logged_in') {
				$('#lfa_customer_state_field').show();
			} else {
				$('#lfa_customer_state_field').hide();
			}
		});
		
		// Show/hide location pages field based on location mode
		$('#lfa_popup_location_mode').on('change', function() {
			if ($(this).val() === 'specific_pages') {
				$('#lfa_location_pages_field').show();
			} else {
				$('#lfa_location_pages_field').hide();
			}
		});
		
		// Show/hide scroll percent field based on trigger type
		$('#lfa_popup_trigger_type').on('change', function() {
			if ($(this).val() === 'scroll_percent') {
				$('#lfa_trigger_scroll_field').show();
				$('#lfa_popup_trigger_scroll_percent').prop('required', true);
			} else {
				$('#lfa_trigger_scroll_field').hide();
				$('#lfa_popup_trigger_scroll_percent').prop('required', false);
			}
		});
	});
	</script>
	<?php
}

/**
 * Save Popup Meta Data
 */
add_action('save_post_popup', function($post_id) {
	// Verify nonce
	if (!isset($_POST['lfa_popup_nonce']) || !wp_verify_nonce($_POST['lfa_popup_nonce'], 'lfa_popup_save')) {
		return;
	}
	
	// Check autosave
	if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
		return;
	}
	
	// Check permissions
	if (!current_user_can('edit_post', $post_id)) {
		return;
	}
	
	// Save template
	if (isset($_POST['lfa_popup_template'])) {
		update_post_meta($post_id, '_lfa_popup_template', sanitize_text_field($_POST['lfa_popup_template']));
	}
	
	// Save priority
	if (isset($_POST['lfa_popup_priority'])) {
		$priority = intval($_POST['lfa_popup_priority']);
		$priority = max(1, min(100, $priority)); // Clamp between 1-100
		update_post_meta($post_id, '_lfa_popup_priority', $priority);
	}
	
	// Save audience conditions
	if (isset($_POST['lfa_popup_login_state'])) {
		update_post_meta($post_id, '_lfa_popup_login_state', sanitize_text_field($_POST['lfa_popup_login_state']));
	}
	
	if (isset($_POST['lfa_popup_customer_state'])) {
		update_post_meta($post_id, '_lfa_popup_customer_state', sanitize_text_field($_POST['lfa_popup_customer_state']));
	}
	
	// Save location conditions
	if (isset($_POST['lfa_popup_location_mode'])) {
		update_post_meta($post_id, '_lfa_popup_location_mode', sanitize_text_field($_POST['lfa_popup_location_mode']));
	}
	
	if (isset($_POST['lfa_popup_location_pages']) && is_array($_POST['lfa_popup_location_pages'])) {
		$pages = array_map('intval', $_POST['lfa_popup_location_pages']);
		update_post_meta($post_id, '_lfa_popup_location_pages', $pages);
	} else {
		delete_post_meta($post_id, '_lfa_popup_location_pages');
	}
	
	// Save trigger settings
	if (isset($_POST['lfa_popup_trigger_type'])) {
		update_post_meta($post_id, '_lfa_popup_trigger_type', sanitize_text_field($_POST['lfa_popup_trigger_type']));
	}
	
	if (isset($_POST['lfa_popup_trigger_scroll_percent'])) {
		$scroll = intval($_POST['lfa_popup_trigger_scroll_percent']);
		$scroll = max(1, min(100, $scroll)); // Clamp between 1-100
		update_post_meta($post_id, '_lfa_popup_trigger_scroll_percent', $scroll);
	}
	
	// Save frequency settings
	if (isset($_POST['lfa_popup_frequency_type'])) {
		update_post_meta($post_id, '_lfa_popup_frequency_type', sanitize_text_field($_POST['lfa_popup_frequency_type']));
	}
});

/**
 * Condition Evaluators
 */

/**
 * Evaluate Audience Conditions
 * 
 * @param int $popup_id Popup post ID
 * @return bool True if audience conditions pass
 */
function lfa_popup_evaluate_audience($popup_id) {
	$login_state = get_post_meta($popup_id, '_lfa_popup_login_state', true);
	$customer_state = get_post_meta($popup_id, '_lfa_popup_customer_state', true);
	
	// Login state check (required)
	if (empty($login_state)) {
		return false;
	}
	
	$is_logged_in = is_user_logged_in();
	
	// Evaluate login state
	switch ($login_state) {
		case 'all':
			// All users pass, continue to customer state check
			break;
			
		case 'logged_in':
			if (!$is_logged_in) {
				return false;
			}
			break;
			
		case 'logged_out':
			if ($is_logged_in) {
				return false;
			}
			// Logged out users skip customer state check
			return true;
			
		default:
			return false;
	}
	
	// Customer state check (only for logged-in users)
	if ($is_logged_in && !empty($customer_state) && $customer_state !== 'any') {
		// Requires WooCommerce
		if (!class_exists('WooCommerce')) {
			// If WooCommerce not available, skip customer check
			return true;
		}
		
		$user_id = get_current_user_id();
		$customer = new WC_Customer($user_id);
		$order_count = $customer->get_order_count();
		
		switch ($customer_state) {
			case 'has_orders':
				if ($order_count === 0) {
					return false;
				}
				break;
				
			case 'no_orders':
				if ($order_count > 0) {
					return false;
				}
				break;
		}
	}
	
	return true;
}

/**
 * Evaluate Location Conditions
 * 
 * @param int $popup_id Popup post ID
 * @return bool True if location conditions pass
 */
function lfa_popup_evaluate_location($popup_id) {
	$location_mode = get_post_meta($popup_id, '_lfa_popup_location_mode', true);
	
	if (empty($location_mode)) {
		return false;
	}
	
	switch ($location_mode) {
		case 'entire_site':
			return true;
			
		case 'front_page':
			return is_front_page();
			
		case 'blog_pages':
			return is_home() || is_category() || is_tag() || is_archive() || is_single();
			
		case 'shop_pages':
			if (!class_exists('WooCommerce')) {
				return false;
			}
			return is_woocommerce() || is_cart() || is_checkout() || is_account_page();
			
		case 'specific_pages':
			$location_pages = get_post_meta($popup_id, '_lfa_popup_location_pages', true);
			if (empty($location_pages) || !is_array($location_pages)) {
				return false;
			}
			return is_page($location_pages);
			
		default:
			return false;
	}
}

/**
 * Get Qualified Popups
 * 
 * Evaluates all popups and returns the one with highest priority that qualifies.
 * 
 * @return array|null Popup data array or null if none qualify
 */
function lfa_popup_get_qualified() {
	// Get all published popups
	$popups = get_posts([
		'post_type'      => 'popup',
		'post_status'    => 'publish',
		'posts_per_page' => -1,
		'orderby'        => 'meta_value_num',
		'meta_key'       => '_lfa_popup_priority',
		'order'          => 'DESC',
	]);
	
	if (empty($popups)) {
		return null;
	}
	
	$qualified = [];
	
	// Evaluate each popup
	foreach ($popups as $popup) {
		// Check audience conditions
		if (!lfa_popup_evaluate_audience($popup->ID)) {
			continue;
		}
		
		// Check location conditions
		if (!lfa_popup_evaluate_location($popup->ID)) {
			continue;
		}
		
		// Both conditions passed, add to qualified list
		$qualified[] = $popup;
	}
	
	// Return highest priority popup (already sorted by priority DESC)
	if (!empty($qualified)) {
		$popup = $qualified[0];
		
		return [
			'id'              => $popup->ID,
			'title'           => $popup->post_title,
			'template'        => get_post_meta($popup->ID, '_lfa_popup_template', true),
			'trigger_type'    => get_post_meta($popup->ID, '_lfa_popup_trigger_type', true),
			'trigger_config'  => [
				'scroll_percent' => get_post_meta($popup->ID, '_lfa_popup_trigger_scroll_percent', true),
			],
			'frequency_type'  => get_post_meta($popup->ID, '_lfa_popup_frequency_type', true),
		];
	}
	
	return null;
}

/**
 * Render Popup HTML
 * 
 * @param array $popup_data Popup data array from lfa_popup_get_qualified()
 */
function lfa_popup_render($popup_data) {
	if (empty($popup_data) || empty($popup_data['template'])) {
		return;
	}
	
	$template_file = LFA_DIR . '/popups/templates/' . sanitize_file_name($popup_data['template']) . '.php';
	
	if (!file_exists($template_file)) {
		// Debug: Log missing template file
		if (defined('WP_DEBUG') && WP_DEBUG) {
			error_log('LFA Popup: Template file not found: ' . $template_file);
		}
		return;
	}
	
	$popup_id = $popup_data['id'];
	$trigger_type = $popup_data['trigger_type'];
	$trigger_config = $popup_data['trigger_config'];
	$frequency_type = $popup_data['frequency_type'];
	
	// Build data attributes
	$data_attrs = [
		'data-popup-id'           => esc_attr($popup_id),
		'data-trigger-type'        => esc_attr($trigger_type),
		'data-frequency-type'     => esc_attr($frequency_type),
	];
	
	if ($trigger_type === 'scroll_percent' && isset($trigger_config['scroll_percent'])) {
		$data_attrs['data-trigger-scroll-percent'] = esc_attr($trigger_config['scroll_percent']);
	}
	
	$data_attr_string = '';
	foreach ($data_attrs as $key => $value) {
		$data_attr_string .= ' ' . $key . '="' . $value . '"';
	}
	
	?>
	<div class="lfa-popup-container"<?php echo $data_attr_string; ?> style="display:none;">
		<div class="lfa-popup-overlay"></div>
		<div class="lfa-popup-content">
			<?php
			// Include template with popup_id available
			include $template_file;
			?>
		</div>
	</div>
	<?php
}

/**
 * Hook into wp_footer to render popup
 */
add_action('wp_footer', function() {
	$popup_data = lfa_popup_get_qualified();
	if ($popup_data) {
		lfa_popup_render($popup_data);
	}
}, 999);

/**
 * AJAX Handler for Newsletter Subscription
 */
add_action('wp_ajax_lfa_popup_newsletter_subscribe', 'lfa_popup_newsletter_subscribe_handler');
add_action('wp_ajax_nopriv_lfa_popup_newsletter_subscribe', 'lfa_popup_newsletter_subscribe_handler');

function lfa_popup_newsletter_subscribe_handler() {
	// Verify nonce
	if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'lfa-nonce')) {
		wp_send_json_error(['message' => __('Security check failed.', 'livingfitapparel')]);
	}
	
	$email = isset($_POST['email']) ? sanitize_email($_POST['email']) : '';
	$interest = isset($_POST['interest']) ? sanitize_text_field($_POST['interest']) : 'both';
	
	if (empty($email) || !is_email($email)) {
		wp_send_json_error(['message' => __('Please enter a valid email address.', 'livingfitapparel')]);
	}
	
	// Get newsletter shortcode
	$newsletter_shortcode = lfa_get('footer.newsletter_sc', lfa_get('home.footer.newsletter_sc', ''));
	
	// If Contact Form 7 is available, try to submit to it
	if (!empty($newsletter_shortcode) && function_exists('wpcf7_contact_form')) {
		// Extract form ID from shortcode
		preg_match('/id="(\d+)"/', $newsletter_shortcode, $matches);
		if (!empty($matches[1])) {
			$form_id = intval($matches[1]);
			$form = wpcf7_contact_form($form_id);
			
			if ($form) {
				// Prepare submission data
				$submission = WPCF7_Submission::get_instance($form, [
					'your-email' => $email,
					'interest' => $interest,
				]);
				
				if ($submission) {
					$result = $submission->submit();
					
					if ($result['status'] === 'mail_sent') {
						wp_send_json_success(['message' => __('Thank you! Check your email for your discount code.', 'livingfitapparel')]);
					} else {
						wp_send_json_error(['message' => $result['message'] ? $result['message'] : __('There was an error. Please try again.', 'livingfitapparel')]);
					}
				}
			}
		}
	}
	
	// Fallback: Store email in user meta or send to admin
	// You can integrate with your newsletter service here
	do_action('lfa_popup_newsletter_subscribe', $email, $interest);
	
	// For now, just return success
	wp_send_json_success(['message' => __('Thank you! Check your email for your discount code.', 'livingfitapparel')]);
}
