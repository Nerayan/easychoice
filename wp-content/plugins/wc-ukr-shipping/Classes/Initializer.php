<?php

namespace kirillbdev\WCUkrShipping\Classes;

if ( ! defined('ABSPATH')) {
  exit;
}

class Initializer
{
  public function __construct()
  {
    if (defined('WC_UKR_SHIPPING_ERROR_MEMORY')) {
      return;
    }

    if ( ! Activator::isActualDb()) {
      Activator::setPluginState('updating');
    }

    add_action('admin_init', function () {
      $this->checkDBVersion();
    });

    add_action('init', [$this, 'routeActivationPage']);

    new NovaPoshtaAjaxHandler();
    new NovaPoshtaRest();
  }

  public function routeActivationPage()
  {
    if ( ! current_user_can('administrator') || Activator::isPluginActivated()) {
      return;
    }

    if ($_SERVER['REQUEST_URI'] === '/wc-ukr-shipping/activation') {
      wp_enqueue_style('wc-ukr-shipping-css', plugin_dir_url(__DIR__) . 'assets/css/style.min.css');
      wp_enqueue_script('jquery');

      $data['ajax_url'] = admin_url('admin-ajax.php');

      echo View::render('activation', $data);
      exit;
    }
  }

  private function checkDBVersion()
  {
    if ( ! Activator::isActualDb()) {
      Activator::setPluginState('updating');
      $this->updateDB();

      wp_redirect(home_url('wc-ukr-shipping/activation'), 301);
      exit;
    }
  }

  private function updateDB()
  {
    global $wpdb;

    $wpdb->query("DROP TABLE IF EXISTS wc_ukr_shipping_np_areas");
    $wpdb->query("DROP TABLE IF EXISTS wc_ukr_shipping_np_cities");
    $wpdb->query("DROP TABLE IF EXISTS wc_ukr_shipping_np_warehouses");

    $collate = $wpdb->get_charset_collate();

    $wpdb->query("
      CREATE TABLE wc_ukr_shipping_np_areas (
        ref varchar(36) NOT NULL,
        description varchar(255) NOT NULL
      ) $collate
    ");

    $wpdb->query("
      CREATE TABLE wc_ukr_shipping_np_cities (
        ref varchar(36) NOT NULL,
        description varchar(255) NOT NULL,
        area_ref varchar(36)
      ) $collate
    ");

    $wpdb->query("
      CREATE TABLE wc_ukr_shipping_np_warehouses (
        ref varchar(36) NOT NULL,
        description varchar(255) NOT NULL,
        city_ref varchar(36)
      ) $collate
    ");

    update_option('wc_ukr_shipping_db_version', WC_UKR_SHIPPING_DB_VERSION);
  }
}