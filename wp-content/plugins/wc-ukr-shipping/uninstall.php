<?php

if ( ! defined('WP_UNINSTALL_PLUGIN')) {
	exit;
}

include_once 'autoload.php';

$wcUkrShippingReporter = new \kirillbdev\WCUkrShipping\Classes\Reporter();
$wcUkrShippingReporter->reportForUninstall();

$wcUkrShippingNPRepository = new \kirillbdev\WCUkrShipping\DB\NovaPoshtaRepository();
$wcUkrShippingNPRepository->dropTables();

$wcUkrShippingOptionsRepository = new \kirillbdev\WCUkrShipping\DB\OptionsRepository();
$wcUkrShippingOptionsRepository->deleteAll();