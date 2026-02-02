<?php
if ( ! defined( 'ABSPATH' ) ) exit;

define('LFA_VER', '1.0.0');
define('LFA_DIR', get_template_directory());
define('LFA_URI', get_template_directory_uri());

// Inc
require LFA_DIR . '/inc/helpers.php';
require LFA_DIR . '/inc/setup.php';
require LFA_DIR . '/inc/assets.php';
require LFA_DIR . '/inc/options-page.php';
require LFA_DIR . '/inc/markets.php';
require LFA_DIR . '/inc/i18n.php';
require LFA_DIR . '/inc/router.php';
require LFA_DIR . '/inc/search.php';
require LFA_DIR . '/inc/quick-view.php';
require LFA_DIR . '/inc/find-your-fit-meta.php';
require LFA_DIR . '/inc/faq-meta.php';
require LFA_DIR . '/inc/my-account.php';
require LFA_DIR . '/inc/contact-meta.php';
require LFA_DIR . '/inc/product-acf-fields.php';
require LFA_DIR . '/inc/popups.php';

// Show Theme settins link in wordpress admin bar
add_action( 'admin_bar_menu', 'lfa_add_admin_bar_dropdown', 100 );

function lfa_add_admin_bar_dropdown( $wp_admin_bar ) {

    // Admins only
    if ( ! current_user_can( 'manage_options' ) ) {
        return;
    }

    $base_url = admin_url( 'admin.php?page=lfa-theme' );

    // Parent menu
    $wp_admin_bar->add_node( array(
        'id'    => 'lfa-settings',
        'title' => __('LFA Settings', 'livingfitapparel'),
        'href'  => $base_url,
    ) );

    // Dropdown items
    $items = array(
        'general'     => __('General', 'livingfitapparel'),
        'header'      => __('Header', 'livingfitapparel'),
        'footer'      => __('Footer', 'livingfitapparel'),
        'home'        => __('Home', 'livingfitapparel'),
        'shop'        => __('Shop', 'livingfitapparel'),
        '404'         => __('404 Page', 'livingfitapparel'),
        'perf'        => __('Performance', 'livingfitapparel'),
    );

    foreach ( $items as $slug => $label ) {
        $wp_admin_bar->add_node( array(
            'id'     => 'lfa-settings-' . $slug,
            'parent' => 'lfa-settings',
            'title'  => $label,
            'href'   => $base_url . '#' . $slug,
        ) );
    }
}
