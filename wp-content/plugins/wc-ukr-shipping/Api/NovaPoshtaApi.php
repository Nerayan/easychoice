<?php

namespace kirillbdev\WCUkrShipping\Api;

if ( ! defined('ABSPATH')) {
	exit;
}

class NovaPoshtaApi
{
	public function __construct()
	{
		$this->apiKey = get_option('wc_ukr_shipping_np_api_key', '');
	}

	public function getAreas()
	{
		$data['modelName'] = 'Address';
		$data['calledMethod'] = 'getAreas';
		$data['apiKey'] = $this->apiKey;

		return $this->sendRequest($data);
	}

	public function getCities($page)
	{
		$data['modelName'] = 'Address';
		$data['calledMethod'] = 'getCities';
		$data['apiKey'] = $this->apiKey;
    $data['methodProperties'] = [
      'Page' => $page,
      'Limit' => 300
    ];

		return $this->sendRequest($data);
	}

	public function getWarehouses($page) {
		$data['modelName'] = 'AddressGeneral';
		$data['calledMethod'] = 'getWarehouses';
		$data['apiKey'] = $this->apiKey;
    $data['methodProperties'] = [
      'Page' => $page,
      'Limit' => 300
    ];

		return $this->sendRequest($data);
	}

	private function sendRequest($data)
	{
		$result = wp_remote_post('https://api.novaposhta.ua/v2.0/json/', [
			'headers' => ['Content-Type' => 'application/json'],
			'timeout' => 30,
			'body' => json_encode($data)
		]);

    return json_decode($result['body'], true);
	}
}