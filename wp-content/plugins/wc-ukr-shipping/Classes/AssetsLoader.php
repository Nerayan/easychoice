<?php

namespace kirillbdev\WCUkrShipping\Classes;

if ( ! defined('ABSPATH')) {
  exit;
}

class AssetsLoader
{
  public function __construct()
  {
    add_action('admin_enqueue_scripts', [ $this, 'loadAdminAssets' ]);
    add_action('admin_enqueue_scripts', [ $this, 'injectGlobals' ]);
    add_action('wp_enqueue_scripts', [ $this, 'injectGlobals' ]);
  }

  public function loadAdminAssets()
  {
    wp_enqueue_script( 'wp-color-picker' );
    wp_enqueue_style( 'wp-color-picker' );

    wp_enqueue_style(
      'wc_ukr_shipping_admin_css',
      WC_UKR_SHIPPING_PLUGIN_URL . 'assets/css/admin.min.css',
      [],
      filemtime(WC_UKR_SHIPPING_PLUGIN_DIR . 'assets/css/admin.min.css')
    );

    wp_enqueue_script(
      'wc_ukr_shipping_tabs_js',
      WC_UKR_SHIPPING_PLUGIN_URL . 'assets/js/tabs.js',
      [],
      filemtime(WC_UKR_SHIPPING_PLUGIN_DIR . 'assets/js/tabs.js')
    );
  }

  public function injectGlobals()
  {
    $translator = WCUkrShipping::instance()->singleton('translate_service');
    $translates = $translator->getTranslates();

    $routerScript = 'assets/js/ajax-router.js';

    wp_enqueue_script(
      'wc_ukr_shipping_router_js',
      WC_UKR_SHIPPING_PLUGIN_URL . $routerScript,
      [ 'jquery' ],
      filemtime(WC_UKR_SHIPPING_PLUGIN_DIR . $routerScript),
      true
    );

    wp_localize_script('wc_ukr_shipping_router_js', 'wc_ukr_shipping_globals', [
      'ajaxUrl'                     => admin_url('admin-ajax.php'),
      'homeUrl'                     => home_url(),
      'lang'                        => $translator->getCurrentLanguage(),
      'nonce'                       => wp_create_nonce('wc-ukr-shipping'),
      'disableDefaultBillingFields' => apply_filters('wc_ukr_shipping_prevent_disable_default_fields', false) === false ?
        'true' :
        'false',
      'i10n' => [
        'placeholder_area' => $translates['placeholder_area'],
        'placeholder_city' => $translates['placeholder_city'],
        'placeholder_warehouse' => $translates['placeholder_warehouse'],
        'not_found' => $translates['not_found']
      ]
    ]);
  }
}