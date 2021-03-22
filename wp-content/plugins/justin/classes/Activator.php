<?php

namespace morkva\JustinShip\classes;

if ( ! defined('ABSPATH')) {
  exit;
}

class Activator
{
  public function __construct()
  {
    register_activation_hook(JUSTIN_PLUGENTRY, [ $this, 'activate' ]);
  }

  public function activate()
  {
    global $wpdb;

    $collate = $wpdb->get_charset_collate();

    $wpdb->query("
      CREATE TABLE IF NOT EXISTS woo_justin_np_areas (
        ref VARCHAR(36) NOT NULL,
        description VARCHAR(255) NOT NULL
      ) $collate
    ");

    $wpdb->query("
      CREATE TABLE IF NOT EXISTS woo_justin_np_cities (
        ref VARCHAR(36) NOT NULL,
        description VARCHAR(255) NOT NULL,
        description_ru VARCHAR(255) NOT NULL,
        area_ref VARCHAR(36)
      ) $collate
    ");

    $wpdb->query("
      CREATE TABLE IF NOT EXISTS woo_justin_np_warehouses (
        ref VARCHAR(36) NOT NULL,
        description VARCHAR(255) NOT NULL,
        description_ru VARCHAR(255) NOT NULL,
        city_ref VARCHAR(36),
        number INT(10) NOT NULL DEFAULT 0
      ) $collate
    ");

    update_option('woo_justin_migration_version', JUSTIN_PLUGDBV);
  }
}
