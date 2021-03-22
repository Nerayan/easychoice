<?php
/*
 * Plugin Name: WooCommerce - 1C - Data Exchange
 * Plugin URI: https://codecanyon.net/item/woocommerce-1c-data-exchange/24768513
 * Description: Data exchange with 1C according to the protocol developed for 1C Bitrix. Import of the nomenclature, prices and stocks, unloading orders in 1C.
 * Version: 1.84.4
 * Author: itgalaxycompany
 * Author URI: https://codecanyon.net/user/itgalaxycompany
 * License: GPLv3
 * Tested up to: 5.6
 * WC tested up to: 4.9
 * Text Domain: itgalaxy-woocommerce-1c
 * Domain Path: /languages/
*/

use Itgalaxy\Wc\Exchange1c\Includes\Bootstrap;

if (!defined('ABSPATH')) {
    exit();
}

define('ITGALAXY_WC_1C_PLUGIN_URL', plugin_dir_url(__FILE__));
define('ITGALAXY_WC_1C_PLUGIN_VERSION', '1.84.4');
define('ITGALAXY_WC_1C_PLUGIN_DIR', plugin_dir_path(__FILE__));

/**
 * Require for `is_plugin_active` function.
 */
require_once ABSPATH . 'wp-admin/includes/plugin.php';

/**
 * Not execute if WooCommerce not exists.
 *
 * @link https://developer.wordpress.org/reference/functions/is_plugin_active/
 */
if (!is_plugin_active('woocommerce/woocommerce.php')) {
    return;
}

/**
 * Use composer autoloader.
 */
require ITGALAXY_WC_1C_PLUGIN_DIR . 'vendor/autoload.php';

/**
 * Registration and load of translations.
 *
 * @link https://developer.wordpress.org/reference/functions/load_theme_textdomain/
 */
load_theme_textdomain('itgalaxy-woocommerce-1c', ITGALAXY_WC_1C_PLUGIN_DIR . 'languages');

/**
 * Register plugin uninstall hook.
 *
 * @link https://developer.wordpress.org/reference/functions/register_uninstall_hook/
 */
register_uninstall_hook(__FILE__, ['Itgalaxy\Wc\Exchange1c\Includes\Bootstrap', 'pluginUninstall']);

/**
 * Load plugin.
 */
Bootstrap::getInstance(__FILE__);
