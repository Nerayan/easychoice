<?php
/**
 * Plugin Name: WC Ukr Shipping
 * Plugin URI: https://kirillbdev.pro/plugins/wc-ukr-shipping/?ref=repository
 * Description: Плагин доставки Украинской службой Нова Пошта для WooCommerce
 * Version: 1.7.7
 * Author: kirillbdev
 * License URI: license.txt
 * Requires PHP: 7.0
 * Tested up to: 5.7
 * WC tested up to: 5.2
*/

if ( ! defined('ABSPATH')) {
  exit;
}

define('WC_UKR_SHIPPING_PLUGIN_NAME', plugin_basename(__FILE__));
define('WC_UKR_SHIPPING_PLUGIN_URL', plugin_dir_url(__FILE__));
define('WC_UKR_SHIPPING_PLUGIN_ENTRY', __FILE__);
define('WC_UKR_SHIPPING_PLUGIN_DIR', plugin_dir_path(__FILE__));

define('WCUS_TRANSLATE_DOMAIN', 'wc-ukr-shipping-l10n');
define('WCUS_MIGRATOR_HISTORY_KEY', 'wcus_migrations_history');

define('WCUS_TRANSLATE_TYPE_PLUGIN', 0);
define('WCUS_TRANSLATE_TYPE_MO_FILE', 1);

define('WC_UKR_SHIPPING_NP_SHIPPING_NAME', 'nova_poshta_shipping');
define('WC_UKR_SHIPPING_NP_SHIPPING_TITLE', 'Новая почта');

include_once __DIR__ . '/vendor/autoload.php';
include_once __DIR__ . '/globals.php';

kirillbdev\WCUkrShipping\Foundation\WCUkrShipping::instance()->init();