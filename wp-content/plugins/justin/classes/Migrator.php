<?php

namespace morkva\JustinShip\classes;

class Migrator
{
  private $migrateCallback = [
    'legacy'  => 'migrateFromLegacy',
    '1.0'     => 'migrateFrom10'
  ];

  public function needMigration()
  {
    $version = get_option('woo_justin_migration_version');

    return $version !== JUSTIN_PLUGDBV;
  }

  public function getOldVersion()
  {
    return get_option('woo_justin_migration_version', 'legacy');
  }

  public function migrate()
  {
    $dbVersion = get_option('woo_justin_migration_version', 'legacy');

    if ($dbVersion !== JUSTIN_PLUGDBV) {

      if (is_callable([ $this, $this->migrateCallback[ $dbVersion ]])) {
        call_user_func([ $this, $this->migrateCallback[ $dbVersion ] ]);
      }

      update_option('woo_justin_migration_version', JUSTIN_PLUGDBV);
    }
  }

  private function updateTablesLegacy()
  {
    global $wpdb;

    $wpdb->query("
      ALTER TABLE woo_justin_np_cities
      ADD COLUMN description_ru VARCHAR(255) NOT NULL
      AFTER description
    ");

    $wpdb->query("
      ALTER TABLE woo_justin_np_warehouses
      ADD COLUMN description_ru VARCHAR(255) NOT NULL
        AFTER description,
      ADD COLUMN number INT(10) NOT NULL DEFAULT 0
        AFTER city_ref
    ");
  }

  private function migrateFromLegacy()
  {
    $this->updateTablesLegacy();

    delete_option('woo_justin_state');
    delete_option('woo_justin_db_version');

    global $wpdb;

    $wpdb->query("DELETE FROM " . $wpdb->prefix . "options WHERE option_name LIKE 'woocommerce_justin_shipping_method_%_settings'");
  }

  private function migrateFrom10()
  {
    global $wpdb;

    $wpdb->query("
      ALTER TABLE woo_justin_np_warehouses
      ADD COLUMN number INT(10) NOT NULL DEFAULT 0
        AFTER city_ref
    ");
  }
}
