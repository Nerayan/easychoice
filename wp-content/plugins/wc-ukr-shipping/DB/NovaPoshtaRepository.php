<?php

namespace kirillbdev\WCUkrShipping\DB;

if ( ! defined('ABSPATH')) {
    exit;
}

class NovaPoshtaRepository
{
    private $db;

    public function __construct()
    {
        global $wpdb;

        $this->db = $wpdb;
    }

    public function getAreas()
    {
        global $wpdb;

        return $wpdb->get_results("SELECT * FROM wc_ukr_shipping_np_areas", ARRAY_A);
    }

    public function getCities($areaRef)
    {
        global $wpdb;

        return $wpdb->get_results("
      SELECT * 
      FROM wc_ukr_shipping_np_cities 
      WHERE area_ref='" . esc_attr($areaRef) . "' 
      ORDER BY description", ARRAY_A
        );
    }

    public function getWarehouses($cityRef)
    {
        global $wpdb;

        return $wpdb->get_results("
      SELECT * 
      FROM wc_ukr_shipping_np_warehouses 
      WHERE city_ref='" . esc_attr($cityRef) . "' 
      ORDER BY number ASC
      ", ARRAY_A
        );
    }

    public function getAreaByRef($ref)
    {
        return $this->db->get_row("
          SELECT * 
          FROM wc_ukr_shipping_np_areas 
          WHERE ref = '" . esc_attr($ref) . "'
        ", ARRAY_A);
    }

    public function getCityByRef($ref)
    {
        return $this->db->get_row("
          SELECT * 
          FROM wc_ukr_shipping_np_cities 
          WHERE ref = '" . esc_attr($ref) . "'
        ", ARRAY_A);
    }

    public function getWarehouseByRef($ref)
    {
        return $this->db->get_row("
          SELECT * 
          FROM wc_ukr_shipping_np_warehouses 
          WHERE ref = '" . esc_attr($ref) . "'
        ", ARRAY_A);
    }

    public function saveAreas($areas)
    {
        global $wpdb;

        $wpdb->query("TRUNCATE wc_ukr_shipping_np_areas");

        foreach ($areas as $area) {
            $wpdb->query("
        INSERT INTO wc_ukr_shipping_np_areas (ref, description)
        VALUES ('{$area['Ref']}', '" . esc_attr($area['Description']) . "')
      ");
        }
    }

    public function saveCities($cities, $page)
    {
        global $wpdb;

        if ($page === 1) {
            $wpdb->query("TRUNCATE wc_ukr_shipping_np_cities");
        }

        foreach ($cities as $city) {
            $wpdb->query("
        INSERT INTO wc_ukr_shipping_np_cities (ref, description, description_ru, area_ref)
        VALUES ('{$city['Ref']}', '" . esc_attr($city['Description']) . "', '" . esc_attr($city['DescriptionRu']) . "', '{$city['Area']}')
      ");
        }
    }

    public function saveWarehouses($warehouses, $page)
    {
        global $wpdb;

        if ($page === 1) {
            $wpdb->query("TRUNCATE wc_ukr_shipping_np_warehouses");
        }

        foreach ($warehouses as $warehouse) {
            $wpdb->query("
        INSERT INTO wc_ukr_shipping_np_warehouses (ref, description, description_ru, city_ref, number)
        VALUES ('{$warehouse['Ref']}', '" . esc_attr($warehouse['Description']) . "', '" . esc_attr($warehouse['DescriptionRu']) . "', '{$warehouse['CityRef']}', '" . (int)$warehouse['Number'] . "')
      ");
        }
    }

    public function dropTables()
    {
        global $wpdb;

        $wpdb->query("DROP TABLE IF EXISTS wc_ukr_shipping_np_areas");
        $wpdb->query("DROP TABLE IF EXISTS wc_ukr_shipping_np_cities");
        $wpdb->query("DROP TABLE IF EXISTS wc_ukr_shipping_np_warehouses");
    }
}