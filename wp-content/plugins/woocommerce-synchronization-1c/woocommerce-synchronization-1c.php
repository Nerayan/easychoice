<?php
/*
 * Plugin Name: WooCommerce - 1C - Data Exchange
 * Plugin URI: https://codecanyon.net/item/woocommerce-1c-data-exchange/24768513
 * Description: Data exchange with 1C according to the protocol developed for 1C Bitrix. Import of the nomenclature and prices, unloading orders in 1C.
 * Version: 1.57.1
 * Author: itgalaxycompany
 * Author URI: https://codecanyon.net/user/itgalaxycompany
 * License: GPLv3
 * Tested up to: 5.4
 * WC tested up to: 4.2
 * Text Domain: itgalaxy-woocommerce-1c
 * Domain Path: /languages/
*/

use Itgalaxy\Wc\Exchange1c\Includes\Bootstrap;

if (!defined('ABSPATH')) {
    exit();
}

define('ITGALAXY_WC_1C_PLUGIN_URL', plugin_dir_url(__FILE__));
define('ITGALAXY_WC_1C_PLUGIN_VERSION', '1.57.1');
define('ITGALAXY_WC_1C_PLUGIN_DIR', plugin_dir_path(__FILE__));

/*
* Require for `is_plugin_active` function.
*/
require_once ABSPATH . 'wp-admin/includes/plugin.php';

// not execute if WooCommerce not exists
// https://developer.wordpress.org/reference/functions/is_plugin_active/
if (!is_plugin_active('woocommerce/woocommerce.php')) {
    return;
}

// use composer autoloader
require __DIR__ . '/vendor/autoload.php';

// https://developer.wordpress.org/reference/functions/load_theme_textdomain/
load_theme_textdomain('itgalaxy-woocommerce-1c', ITGALAXY_WC_1C_PLUGIN_DIR . 'languages');

Bootstrap::getInstance(__FILE__);
