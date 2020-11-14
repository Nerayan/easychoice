<?php
/**
 * Plugin Name: WC Ukr Shipping
 * Plugin URI: https://kirillbdev.pro/plugins/wc-ukr-shipping/?ref=repository
 * Description: Плагин доставки Украинской службой Нова Пошта для WooCommerce
 * Version: 1.7.1
 * Author: kirillbdev
 * License URI: license.txt
 * Tested up to: 5.5
*/

if ( ! defined('ABSPATH')) {
  exit;
}

define('WC_UKR_SHIPPING_PLUGIN_URL', plugin_dir_url(__FILE__));
define('WC_UKR_SHIPPING_PLUGIN_ENTRY', __FILE__);
define('WC_UKR_SHIPPING_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('WC_UKR_SHIPPING_MIGRATION_VERSION', '1.1');
define('WCUS_TRANSLATE_DOMAIN', 'wc-ukr-shipping-l10n');

define('WCUS_TRANSLATE_TYPE_PLUGIN', 0);
define('WCUS_TRANSLATE_TYPE_MO_FILE', 1);

define('WC_UKR_SHIPPING_NP_SHIPPING_NAME', 'nova_poshta_shipping');
define('WC_UKR_SHIPPING_NP_SHIPPING_TITLE', 'Доставка службой "Новая почта"');

include_once 'autoload.php';
include_once 'globals.php';

add_filter('plugin_action_links_' . plugin_basename(__FILE__), function ($links) {
  $settings_link = '<a href="' . home_url('wp-admin/admin.php?page=wc_ukr_shipping_options') . '">Настройки</a>';
  array_unshift($links, $settings_link);

  return $links;
});

\kirillbdev\WCUkrShipping\Classes\WCUkrShipping::instance()->init();