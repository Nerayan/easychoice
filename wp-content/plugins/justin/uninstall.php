<?php

if ( ! defined('WP_UNINSTALL_PLUGIN')) {
	exit;
}

include_once 'autoload.php';

$wcUkrShippingNPRepository = new \morkva\JustinShip\DB\JustinRepository();
$wcUkrShippingNPRepository->dropTables();

$wcUkrShippingOptionsRepository = new \morkva\JustinShip\DB\OptionsRepository();
$wcUkrShippingOptionsRepository->deleteAll();