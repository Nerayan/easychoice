<?php

namespace kirillbdev\WCUkrShipping\Classes;

if ( ! defined('ABSPATH')) {
	exit;
}

class NovaPoshtaApiLoader
{
	private $api;

	public function __construct($api)
	{
		$this->api = $api;
	}

	public function loadAreas()
	{
		$result = $this->api->getAreas();

		if ($result['success']) {
			global $wpdb;

			foreach ($result['data'] as $area) {
				$wpdb->query("
					INSERT INTO wc_ukr_shipping_np_areas 
					VALUES('" . $area['Ref'] . "', '" . esc_attr($area['Description']) . "')
				");
			}

			return true;
		}

		return false;
	}

	public function loadCities()
	{
		$result = $this->api->getCities();

		if ($result['success']) {
			global $wpdb;

			foreach ($result['data'] as $city) {
				$wpdb->query("
					INSERT INTO wc_ukr_shipping_np_cities 
					VALUES('" . $city['Ref'] ."', '" . esc_attr($city['Description']) ."', '" . $city['Area'] . "')
				");
			}

			return true;
		}

		return false;
	}

  public function loadWarehouses()
	{
		$result = $this->api->getWarehouses();

		if ($result['success']) {
      global $wpdb;

			foreach ($result['data'] as $warehouse) {
				$wpdb->query("
					INSERT INTO wc_ukr_shipping_np_warehouses 
					VALUES('" . $warehouse['Ref'] ."', '" . esc_attr($warehouse['Description']) ."', '" . $warehouse['CityRef'] . "')
				");
			}

			return true;
		}

		return false;
	}
}