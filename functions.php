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
