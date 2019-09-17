<?php

namespace kirillbdev\WCUkrShipping\Classes;

class Migrator
{
  private $migrateCallback = [
    'legacy'  => 'migrateFromLegacy',
    '1.0'     => 'migrateFrom10'
  ];

  public function needMigration()
  {
    $version = get_option('wc_ukr_shipping_migration_version');

    return $version !== WC_UKR_SHIPPING_MIGRATION_VERSION;
  }

  public function getOldVersion()
  {
    return get_option('wc_ukr_shipping_migration_version', 'legacy');
  }

  public function migrate()
  {
    $dbVersion = get_option('wc_ukr_shipping_migration_version', 'legacy');

    if ($dbVersion !== WC_UKR_SHIPPING_MIGRATION_VERSION) {

      if (is_callable([ $this, $this->migrateCallback[ $dbVersion ]])) {
        call_user_func([ $this, $this->migrateCallback[ $dbVersion ] ]);
      }

      update_option('wc_ukr_shipping_migration_version', WC_UKR_SHIPPING_MIGRATION_VERSION);
    }
  }

  private function updateTablesLegacy()
  {
    global $wpdb;

    $wpdb->query("
      ALTER TABLE wc_ukr_shipping_np_cities
      ADD COLUMN description_ru VARCHAR(255) NOT NULL
      AFTER description
    ");

    $wpdb->query("
      ALTER TABLE wc_ukr_shipping_np_warehouses
      ADD COLUMN description_ru VARCHAR(255) NOT NULL
        AFTER description,
      ADD COLUMN number INT(10) NOT NULL DEFAULT 0
        AFTER city_ref
    ");
  }

  private function migrateFromLegacy()
  {
    $this->updateTablesLegacy();

    delete_option('wc_ukr_shipping_state');
    delete_option('wc_ukr_shipping_db_version');

    global $wpdb;

    $wpdb->query("DELETE FROM " . $wpdb->prefix . "options WHERE option_name LIKE 'woocommerce_nova_poshta_shipping_%_settings'");
  }

  private function migrateFrom10()
  {
    global $wpdb;

    $wpdb->query("
      ALTER TABLE wc_ukr_shipping_np_warehouses
      ADD COLUMN number INT(10) NOT NULL DEFAULT 0
        AFTER city_ref
    ");
  }
}