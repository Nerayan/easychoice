<?php

namespace kirillbdev\WCUkrShipping\Classes;

use kirillbdev\WCUkrShipping\Api\NovaPoshtaApi;

if ( ! defined('ABSPATH')) {
	exit;
}

class NovaPoshtaAjaxHandler
{
  private $apiLoader;

	public function __construct()
	{
	  $this->apiLoader = new NovaPoshtaApiLoader(new NovaPoshtaApi(''));

		// Activation
    add_action('wp_ajax_wc_ukr_shipping_np_load_areas', [ $this, 'apiLoadAreas' ]);
    add_action('wp_ajax_nopriv_wc_ukr_shipping_np_load_areas', [ $this, 'apiLoadAreas' ]);

    add_action('wp_ajax_wc_ukr_shipping_np_load_cities', [ $this, 'apiLoadCities' ]);
    add_action('wp_ajax_nopriv_wc_ukr_shipping_np_load_cities', [ $this, 'apiLoadCities' ]);

    add_action('wp_ajax_wc_ukr_shipping_np_load_warehouses', [ $this, 'apiLoadWarehouses' ]);
    add_action('wp_ajax_nopriv_wc_ukr_shipping_np_load_warehouses', [ $this, 'apiLoadWarehouses' ]);
    // End Activation
	}

	public function apiLoadAreas()
  {
  	$result = $this->apiLoader->loadAreas();

    echo json_encode([
      'result' => $result
    ]);

    wp_die();
  }

  public function apiLoadCities()
  {
  	$result = $this->apiLoader->loadCities();

    echo json_encode([
      'result' => $result
    ]);

    wp_die();
  }

  public function apiLoadWarehouses()
  {
  	$result = $this->apiLoader->loadWarehouses();

    echo json_encode([
      'result' => $result
    ]);

    Activator::setPluginState('activated');

    wp_die();
  }
}