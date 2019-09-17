<?php
/**
 * Plugin Name: WC Ukr Shipping
 * Plugin URI: https://kirillbdev.pro/plugins/wc-ukr-shipping/?ref=repository
 * Description: Плагин доставки Украинской службой Нова Пошта для WooCommerce
 * Version: 1.4.2
 * Author: kirillbdev
 * License URI: license.txt
 * Tested up to: 5.2.2
*/

if ( ! defined('ABSPATH')) {
  exit;
}

include_once 'autoload.php';

define('WC_UKR_SHIPPING_PLUGIN_URL', plugin_dir_url(__FILE__));
define('WC_UKR_SHIPPING_PLUGIN_ENTRY', __FILE__);
define('WC_UKR_SHIPPING_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('WC_UKR_SHIPPING_MIGRATION_VERSION', '1.1');

\kirillbdev\WCUkrShipping\Classes\WCUkrShipping::instance();

if ( ! function_exists('wc_ukr_shipping')) {

  function wc_ukr_shipping()
  {
    return \kirillbdev\WCUkrShipping\Classes\WCUkrShipping::instance();
  }

}

if ( ! function_exists('wc_ukr_shipping_import_svg')) {

  function wc_ukr_shipping_import_svg($image)
  {
    return file_get_contents(WC_UKR_SHIPPING_PLUGIN_DIR . '/image/' . $image);
  }

}

define('WC_UKR_SHIPPING_NP_SHIPPING_NAME', 'nova_poshta_shipping');
define('WC_UKR_SHIPPING_NP_SHIPPING_TITLE', 'Доставка службой "Новая почта"');

add_filter('woocommerce_shipping_methods', 'wc_ukr_shipping_add_np_shipping_method');
function wc_ukr_shipping_add_np_shipping_method($methods)
{
  include_once 'Classes/NovaPoshtaShipping.php';

  $methods[WC_UKR_SHIPPING_NP_SHIPPING_NAME] = 'NovaPoshtaShipping';

  return $methods;
}

new \kirillbdev\WCUkrShipping\Classes\NovaPoshtaFrontendInjector();
new \kirillbdev\WCUkrShipping\Classes\CheckoutValidator();
new \kirillbdev\WCUkrShipping\Classes\OrderCreator();

add_action('woocommerce_admin_order_data_after_shipping_address', function ($order) {
  $shippingMethod = $order->get_shipping_methods();
  $shippingMethod = reset($shippingMethod);

  if ($shippingMethod && $shippingMethod->get_method_id() === WC_UKR_SHIPPING_NP_SHIPPING_NAME) {
?>
    <input type="hidden" name="_shipping_state" value="<?= esc_attr($order->get_shipping_state()); ?>" />
<?php
  }
});

add_filter('plugin_action_links_' . plugin_basename(__FILE__), function ($links) {
  $settings_link = '<a href="' . home_url('wp-admin/admin.php?page=wc_ukr_shipping_options') . '">Настройки</a>';
  array_unshift($links, $settings_link);

  return $links;
});