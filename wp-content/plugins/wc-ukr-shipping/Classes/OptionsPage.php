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
    add_submenu_page(
      'woocommerce',
      'Настройки - WC Ukr Shipping',
      'WC Ukr Shipping',
      'manage_options',
      'wc_ukr_shipping_options',
      [ $this, 'html' ]
    );
  }

  public function html()
  {
    echo View::render('settings');
  }
}