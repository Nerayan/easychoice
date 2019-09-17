<?php

namespace kirillbdev\WCUkrShipping\Classes;

class NovaPoshtaRest
{
  public function __construct()
  {
    add_action('rest_api_init', [ $this, 'initRoutes' ]);
  }

  public function initRoutes()
  {
    register_rest_route( 'wc_ukr_shipping/v1', 'novaposhta/area', [
      'callback' => [ $this, 'getAreas' ]
    ]);

    register_rest_route( 'wc_ukr_shipping/v1', 'novaposhta/cities/(?P<ref>[^\/]*)', [
      'callback' => [ $this, 'getCities' ]
    ]);

    register_rest_route( 'wc_ukr_shipping/v1', 'novaposhta/warehouses/(?P<ref>[^\/]*)', [
      'callback' => [ $this, 'getWarehouses' ]
    ]);
  }

  public function getAreas(\WP_REST_Request $request)
  {
    try {
      global $wpdb;

      $npAreaTranslator = new NPAreaTranslator();
      $areas = $wpdb->get_results("SELECT * FROM wc_ukr_shipping_np_areas", ARRAY_A);

      return [
        'result' => true,
        'data' => $npAreaTranslator->translateAreas($areas)
      ];
    }
    catch (\Error $e) {
      return [
        'result' => false,
        'data' => $e->getMessage()
      ];
    }
  }

  public function getCities(\WP_REST_Request $request)
  {
    try {
      global $wpdb;

      $ref = $request['ref'];
      $cities = $wpdb->get_results("SELECT * FROM wc_ukr_shipping_np_cities WHERE area_ref='" . esc_attr($ref) . "' ORDER BY description", ARRAY_A);

      return [
        'result' => true,
        'data' => $cities
      ];
    }
    catch (\Error $e) {
      return [
        'result' => false,
        'data' => $e->getMessage()
      ];
    }
  }

  public function getWarehouses(\WP_REST_Request $request)
  {
    try {
      global $wpdb;

      $ref = $request['ref'];

      $warehouses = $wpdb->get_results("
        SELECT * 
        FROM wc_ukr_shipping_np_warehouses 
        WHERE city_ref='" . esc_attr($ref) . "' 
        ORDER BY number ASC
      ", ARRAY_A);

      return [
        'result' => true,
        'data' => $warehouses
      ];
    }
    catch (\Error $e) {
      return [
        'result' => false,
        'data' => $e->getMessage()
      ];
    }
  }
}