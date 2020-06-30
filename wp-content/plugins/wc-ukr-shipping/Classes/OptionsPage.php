<?php

namespace kirillbdev\WCUkrShipping\Classes;

if ( ! defined('ABSPATH')) {
  exit;
}

class OptionsPage
{
  public function __construct()
  {
    add_action('admin_menu', [ $this, 'registerOptionsPage' ], 99);
  }

  public function registerOptionsPage()
  {
    add_menu_page(
      'Настройки - WC Ukr Shipping',
      'WC Ukr Shipping',
      'manage_options',
      'wc_ukr_shipping_options',
      [ $this, 'html' ],
      WC_UKR_SHIPPING_PLUGIN_URL . 'image/menu-icon.png',
      '56.15'
    );

    add_submenu_page(
      'wc_ukr_shipping_options',
      'Premium версия',
      wc_ukr_shipping_import_svg('star.svg') . 'Premium версия',
      'manage_options',
      'wcus_submenu_premium',
      [ $this, 'premiumHtml' ]
    );
  }

  public function html()
  {
    echo View::render('settings');
  }

  public function premiumHtml()
  {
    wp_redirect('https://kirillbdev.pro/wc-ukr-shipping-pro/?ref=plugin_menu', 301);
    exit;
  }
}