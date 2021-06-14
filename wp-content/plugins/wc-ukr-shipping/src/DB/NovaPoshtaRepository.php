<?php

namespace kirillbdev\WCUkrShipping\DB;

use kirillbdev\WCUSCore\Facades\DB;

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
        $areas = DB::table('wc_ukr_shipping_np_areas')->get();
        $mapped = [];

        foreach ($areas as $area) {
            $mapped[ $area['ref'] ] = $area;
        }

        return array_values(apply_filters('wcus_get_areas', $mapped));
    }

    public function getCities($areaRef)
    {
        return DB::table('wc_ukr_shipping_np_cities')
            ->where('area_ref', $areaRef)
            ->orderBy('description')
            ->get();
    }

    public function getWarehouses($cityRef)
    {
        $warehouses = DB::table('wc_ukr_shipping_np_warehouses')
            ->where('city_ref', $cityRef)
            ->orderBy('`number`')
            ->get();

        if (0 === (int)get_option('wcus_show_poshtomats', 1)) {
            return array_filter($warehouses, function ($warehouse) {
                return false === strpos($warehouse['description'], 'Поштомат');
            });
        }

        return $warehouses;
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
        DB::table('wc_ukr_shipping_np_areas')
            ->truncate();

        foreach ($areas as $area) {
            DB::table('wc_ukr_shipping_np_areas')
                ->insert([
                    'ref' => $area['Ref'],
                    'description' => $area['Description']
                ]);
        }
    }

    public function saveCities($cities, $page)
    {
        if ($page === 1) {
            DB::table('wc_ukr_shipping_np_cities')
                ->truncate();
        }

        foreach ($cities as $city) {
            DB::table('wc_ukr_shipping_np_cities')
                ->insert([
                    'ref' => $city['Ref'],
                    'description' => $city['Description'],
                    'description_ru' => $city['DescriptionRu'],
                    'area_ref' => $city['Area']
                ]);
        }
    }

    public function saveWarehouses($warehouses, $page)
    {
        if ($page === 1) {
            DB::table('wc_ukr_shipping_np_warehouses')
                ->truncate();
        }

        foreach ($warehouses as $warehouse) {
            DB::table('wc_ukr_shipping_np_warehouses')
                ->insert([
                    'ref' => $warehouse['Ref'],
                    'description' => $warehouse['Description'],
                    'description_ru' => $warehouse['DescriptionRu'],
                    'city_ref' => $warehouse['CityRef'],
                    'number' => $warehouse['Number']
                ], [
                    'number' => '%d'
                ]);
        }
    }

    public function dropTables()
    {
        global $wpdb;

        $wpdb->query("DROP TABLE IF EXISTS wc_ukr_shipping_np_areas");
        $wpdb->query("DROP TABLE IF EXISTS wc_ukr_shipping_np_cities");
        $wpdb->query("DROP TABLE IF EXISTS wc_ukr_shipping_np_warehouses");

        delete_option('wcus_migrations_history');
    }
}