<?php

namespace morkva\JustinShip\classes;

if ( ! defined('ABSPATH')) {
	exit;
}

class JustinApi
{
	public function __construct()
	{
		$this->apiKey = get_option('woo_justin_api_key', '');
	}

}
