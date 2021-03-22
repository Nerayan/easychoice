<?php

namespace morkva\JustinShip\classes;

use morkva\JustinShip\Api\JustinApi;

if ( ! defined('ABSPATH')) {
	exit;
}

class JustinAjaxHandler
{
  private $apiLoader;

	public function __construct()
	{
	  $this->apiLoader = new JustinApiLoader(new JustinApi(''));

		// Activation
    add_action('wp_ajax_woo_justin_np_load_areas', [ $this, 'apiLoadAreas' ]);
    add_action('wp_ajax_nopriv_woo_justin_np_load_areas', [ $this, 'apiLoadAreas' ]);

    add_action('wp_ajax_woo_justin_np_load_cities', [ $this, 'apiLoadCities' ]);
    add_action('wp_ajax_nopriv_woo_justin_np_load_cities', [ $this, 'apiLoadCities' ]);

    add_action('wp_ajax_woo_justin_np_load_warehouses', [ $this, 'apiLoadWarehouses' ]);
    add_action('wp_ajax_nopriv_woo_justin_np_load_warehouses', [ $this, 'apiLoadWarehouses' ]);
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
