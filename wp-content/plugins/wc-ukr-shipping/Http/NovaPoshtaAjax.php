<?php

namespace kirillbdev\WCUkrShipping\Http;

use kirillbdev\WCUkrShipping\Api\NovaPoshtaApi;
use kirillbdev\WCUkrShipping\Classes\NPAreaTranslator;
use kirillbdev\WCUkrShipping\DB\NovaPoshtaRepository;
use kirillbdev\WCUkrShipping\DB\OptionsRepository;
use kirillbdev\WCUkrShipping\Validators\OptionsValidator;

class NovaPoshtaAjax
{
  private $api;
  private $novaPoshtaRepository;
  private $optionsRepository;

  public function __construct()
  {
    $this->api = new NovaPoshtaApi();
    $this->novaPoshtaRepository = new NovaPoshtaRepository();
    $this->optionsRepository = new OptionsRepository();

    if (wp_doing_ajax()) {
      $this->initRoutes();
    }
  }

  public function initRoutes()
  {
    // Options Save
    add_action('wp_ajax_wc_ukr_shipping_save_settings', [ $this, 'saveSettings' ]);

    // Options Areas Load to DB
    add_action('wp_ajax_wc_ukr_shipping_load_areas', [ $this, 'loadAreas' ]);

    // Options Cities load to DB
    add_action('wp_ajax_wc_ukr_shipping_load_cities', [ $this, 'loadCities' ]);

    // Options Warehouses load to DB
    add_action('wp_ajax_wc_ukr_shipping_load_warehouses', [ $this, 'loadWarehouses' ]);

    // Frontend Areas
    add_action('wp_ajax_wc_ukr_shipping_get_areas', [ $this, 'getAreas' ]);
    add_action('wp_ajax_nopriv_wc_ukr_shipping_get_areas', [ $this, 'getAreas' ]);

    // Frontend Cities
    add_action('wp_ajax_wc_ukr_shipping_get_cities', [ $this, 'getCities' ]);
    add_action('wp_ajax_nopriv_wc_ukr_shipping_get_cities', [ $this, 'getCities' ]);

    // Frontend Warehouses
    add_action('wp_ajax_wc_ukr_shipping_get_warehouses', [ $this, 'getWarehouses' ]);
    add_action('wp_ajax_nopriv_wc_ukr_shipping_get_warehouses', [ $this, 'getWarehouses' ]);
  }

  public function saveSettings()
  {
    parse_str($_POST['body'], $body);

    $validator = new OptionsValidator();
    $result = $validator->validateRequest($body);

    if ($result !== true) {
      Response::makeAjax('error', [
        'errors' => $result
      ]);
    }

    $this->optionsRepository->save($body);

    Response::makeAjax('success', [
      'api_key' => get_option('wc_ukr_shipping_np_api_key', ''),
      'message' => 'Настройки успешно сохранены'
    ]);
  }

  public function getAreas()
  {
    try {
      $areas = $this->novaPoshtaRepository->getAreas();
      $npAreaTranslator = new NPAreaTranslator();

      Response::makeAjax('success', $npAreaTranslator->translateAreas($areas));
    }
    catch (\Error $e) {
      Response::makeAjax('error', $e->getMessage());
    }
  }

  public function getCities()
  {
    try {
      $cities = $this->novaPoshtaRepository->getCities($_POST['body']['ref']);

      Response::makeAjax('success', $cities);
    }
    catch (\Error $e) {
      Response::makeAjax('error', $e->getMessage());
    }
  }

  public function getWarehouses()
  {
    try {
      $warehouses = $this->novaPoshtaRepository->getWarehouses($_POST['body']['ref']);

      Response::makeAjax('success', $warehouses);
    }
    catch (\Error $e) {
      Response::makeAjax('error', $e->getMessage());
    }
  }

  public function loadAreas()
  {
    $result = $this->api->getAreas();

    if ($result['success']) {
      $this->novaPoshtaRepository->saveAreas($result['data']);

      Response::makeAjax('success');
    }

    Response::makeAjax('error', [
      'errors' => $result['errors']
    ]);
  }

  public function loadCities()
  {
    $result = $this->api->getCities((int)$_POST['body']['page']);

    if ($result['success']) {
      $this->novaPoshtaRepository->saveCities($result['data'], (int)$_POST['body']['page']);

      Response::makeAjax('success', [
        'loaded' => count($result['data']) === 0
      ]);
    }

    Response::makeAjax('error', [
      'errors' => $result['errors']
    ]);
  }

  public function loadWarehouses()
  {
    $result = $this->api->getWarehouses((int)$_POST['body']['page']);

    if ($result['success']) {
      $this->novaPoshtaRepository->saveWarehouses($result['data'], (int)$_POST['body']['page']);

      Response::makeAjax('success', [
        'loaded' => count($result['data']) === 0
      ]);
    }

    Response::makeAjax('error', [
      'errors' => $result['errors']
    ]);
  }
}