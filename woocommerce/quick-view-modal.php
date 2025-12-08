<?php
/**
 * Quick View Modal Template
 *
 * @package livingfitapparel
 */

defined('ABSPATH') || exit;
?>
<div class="lfa-quick-view-modal" id="lfa-quick-view-modal" role="dialog" aria-modal="true" aria-labelledby="lfa-quick-view-title" style="display: none;">
    <div class="lfa-quick-view-overlay"></div>
    <div class="lfa-quick-view-content">
        <button type="button" class="lfa-quick-view-close" aria-label="<?php esc_attr_e('Close quick view', 'livingfitapparel'); ?>">
            <span>&times;</span>
        </button>
        <div class="lfa-quick-view-inner">
            <div class="lfa-quick-view-loading">
                <span><?php esc_html_e('Loading...', 'livingfitapparel'); ?></span>
            </div>
            <div class="lfa-quick-view-data" style="display: none;">
                <!-- Content will be loaded via AJAX -->
            </div>
        </div>
    </div>
</div>

