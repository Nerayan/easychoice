<?php
/**
 * Plugin Name: Justin for Woocommerce
 * Plugin URI: https://morkva.co.ua/plugins/wc-ukr-shipping/?ref=repository
 * Description: Плагін доставки Justin для WooCommerce
 * Version: 1.3
 * Author: Morkva
*/

if ( ! defined('ABSPATH')) {
  exit;
}

include_once 'autoload.php';

define('JUSTIN_PLUGURL', plugin_dir_url(__FILE__));
define('JUSTIN_PLUGENTRY', __FILE__);
define('JUSTIN_PLUGFOLDER', plugin_dir_path(__FILE__));
define('JUSTIN_PLUGDBV', '1.1');

require_once ABSPATH . 'wp-admin/includes/plugin.php';
$plugData = get_plugin_data(__FILE__);
define( 'MJS_PLUGIN_VERSION', $plugData['Version'] );

\morkva\JustinShip\classes\JustinShip::instance();

if ( ! function_exists('woo_justin')) {

  function woo_justin()
  {
    return \morkva\JustinShip\classes\JustinShip::instance();
  }

}

if ( ! function_exists('woo_justin_import_svg')) {

  function woo_justin_import_svg($image)
  {
    return file_get_contents(JUSTIN_PLUGFOLDER . '/image/' . $image);
  }

}

if ( ! function_exists('woo_justin_get_option')) {

  function woo_justin_get_option($key)
  {
    return \morkva\JustinShip\DB\OptionsRepository::getOption($key);
  }

}

define('JUSTIN_METHOD_NAME', 'justin_shipping_method');
define('JUSTIN_METHOD_TITLE', 'Justin доставка');

/*
clear shipping rates cache because woocommerce caching these values
*/
add_filter('woocommerce_checkout_update_order_review', 'justin_clear_wc_shipping_rates_cache');

function justin_clear_wc_shipping_rates_cache()
{
    $packages = WC()->cart->get_shipping_packages();

    foreach ($packages as $key => $value)
    {
        $shipping_session = "shipping_for_package_$key";
        unset(WC()->session->$shipping_session);
    }
}

function justin_adjust_shipping_rate($rates)
{
    global $woocommerce;
    $index = 0;
    foreach ($rates as $rate)
    {
        if ($rate->get_method_id() == 'justin_shipping_method')
        {
            $cost = $rate->cost;
            $rate->cost = intval(get_option('justin_default_price'));
        }
    }
    return $rates;
}
add_filter('woocommerce_package_rates', 'justin_adjust_shipping_rate', 50, 1);


add_filter('woocommerce_shipping_methods', 'woo_justin_add_np_shipping_method');
function woo_justin_add_np_shipping_method($methods)
{
  include_once 'classes/WC_Justin_Shipping_Method.php';

  $methods[JUSTIN_METHOD_NAME] = 'WC_Justin_Shipping_Method';

  return $methods;
}

new \morkva\JustinShip\classes\JustinFrontendInjector();
new \morkva\JustinShip\classes\CheckoutValidator();
new \morkva\JustinShip\classes\OrderCreator();

add_action('woocommerce_admin_order_data_after_shipping_address', function ($order) {
  $shippingMethod = $order->get_shipping_methods();
  $shippingMethod = reset($shippingMethod);

  if ($shippingMethod && $shippingMethod->get_method_id() === JUSTIN_METHOD_NAME) {
?>
    <input type="hidden" name="_shipping_state" value="<?= esc_attr($order->get_shipping_state()); ?>" />
<?php
  }
});

add_filter('plugin_action_links_' . plugin_basename(__FILE__), function ($links) {
  $settings_link = '<a href="' . home_url('wp-admin/admin.php?page=morkvajustin_plugin') . '">Налаштування</a>';
  array_unshift($links, $settings_link);

  return $links;
});



/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-morkvajustin-plugin-activator.php
 */
function activate_morkvajustin_plugin() {
    require_once plugin_dir_path( __FILE__ ) . 'includes/class-morkvajustin-plugin-activator.php';
    MJS_Plugin_Activator::activate();
}
/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-morkvajustin-plugin-deactivator.php
 */
function deactivate_morkvajustin_plugin() {
    require_once plugin_dir_path( __FILE__ ) . 'includes/class-morkvajustin-plugin-deactivator.php';
    MJS_Plugin_Deactivator::deactivate();
}
register_activation_hook( __FILE__, 'activate_morkvajustin_plugin' );
register_deactivation_hook( __FILE__, 'deactivate_morkvajustin_plugin' );
/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-morkvajustin-plugin.php';
/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_morkvajustin_plugin() {
    $plugin = new MJS_Plugin();
    $plugin->run();
}
run_morkvajustin_plugin();
