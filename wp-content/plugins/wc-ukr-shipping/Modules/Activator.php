<?php

namespace kirillbdev\WCUkrShipping\Modules;

use kirillbdev\WCUkrShipping\Contracts\ModuleInterface;

if ( ! defined('ABSPATH')) {
  exit;
}

class Activator implements ModuleInterface
{
  public function init()
  {
      register_activation_hook(WC_UKR_SHIPPING_PLUGIN_ENTRY, [ $this, 'activate' ]);
  }

  public function activate()
  {
    global $wpdb;

    $collate = $wpdb->get_charset_collate();

    $wpdb->query("
      CREATE TABLE IF NOT EXISTS wc_ukr_shipping_np_areas (
        ref VARCHAR(36) NOT NULL,
        description VARCHAR(255) NOT NULL
      ) $collate
    ");

    $wpdb->query("
      CREATE TABLE IF NOT EXISTS wc_ukr_shipping_np_cities (
        ref VARCHAR(36) NOT NULL,
        description VARCHAR(255) NOT NULL,
        description_ru VARCHAR(255) NOT NULL,
        area_ref VARCHAR(36)
      ) $collate
    ");

    $wpdb->query("
      CREATE TABLE IF NOT EXISTS wc_ukr_shipping_np_warehouses (
        ref VARCHAR(36) NOT NULL,
        description VARCHAR(255) NOT NULL,
        description_ru VARCHAR(255) NOT NULL,
        city_ref VARCHAR(36),
        number INT(10) NOT NULL DEFAULT 0
      ) $collate
    ");

    update_option('wc_ukr_shipping_migration_version', WC_UKR_SHIPPING_MIGRATION_VERSION);
  }
}