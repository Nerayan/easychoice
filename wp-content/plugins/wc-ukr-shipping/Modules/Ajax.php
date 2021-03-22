<?php

namespace kirillbdev\WCUkrShipping\Modules;

use kirillbdev\WCUkrShipping\Api\NovaPoshtaApi;
use kirillbdev\WCUkrShipping\Contracts\ModuleInterface;
use kirillbdev\WCUkrShipping\DB\NovaPoshtaRepository;
use kirillbdev\WCUkrShipping\DB\OptionsRepository;
use kirillbdev\WCUkrShipping\Http\ResponseLegacy;
use kirillbdev\WCUkrShipping\Http\WpHttpClient;
use kirillbdev\WCUkrShipping\Model\CheckoutOrderData;
use kirillbdev\WCUkrShipping\Services\CalculationService;
use kirillbdev\WCUkrShipping\Services\TranslateService;

if ( ! defined('ABSPATH')) {
    exit;
}

class Ajax implements ModuleInterface
{
    private $api;
    private $novaPoshtaRepository;
    private $optionsRepository;

    /**
     * @var TranslateService
     */
    private $translator;

    public function __construct()
    {
        $this->api = new NovaPoshtaApi(new WpHttpClient());
        $this->novaPoshtaRepository = new NovaPoshtaRepository();
        $this->optionsRepository = new OptionsRepository();
        $this->translator = wcus_container_singleton('translate_service');
    }

    public function init()
    {
        if (wp_doing_ajax()) {
            $this->initRoutes();
            $this->initAdminRoutes();
        }
    }

    public function initRoutes()
    {
        /* === API V2 === */
        add_action('wp_ajax_wcus_api_v2_get_cities', [ $this, 'apiV2GetCities' ]);
        add_action('wp_ajax_nopriv_wcus_api_v2_get_cities', [ $this, 'apiV2GetCities' ]);

        add_action('wp_ajax_wcus_api_v2_get_warehouses', [ $this, 'apiV2GetWarehouses' ]);
        add_action('wp_ajax_nopriv_wcus_api_v2_get_warehouses', [ $this, 'apiV2GetWarehouses' ]);

        add_action('wp_ajax_wcus_api_v2_calculate_cost', [ $this, 'apiV2CalculateCost' ]);
        add_action('wp_ajax_nopriv_wcus_api_v2_calculate_cost', [ $this, 'apiV2CalculateCost' ]);
    }

    public function initAdminRoutes()
    {
        // Options Areas Load to DB
        add_action('wp_ajax_wc_ukr_shipping_load_areas', [$this, 'loadAreas']);

        // Options Cities load to DB
        add_action('wp_ajax_wc_ukr_shipping_load_cities', [$this, 'loadCities']);

        // Options Warehouses load to DB
        add_action('wp_ajax_wc_ukr_shipping_load_warehouses', [$this, 'loadWarehouses']);
    }

    public function apiV2GetCities()
    {
        $this->apiV2ValidateRequest();

        if (empty($_POST['value'])) {
            ResponseLegacy::makeAjax('error');
        }

        // todo: refactor
        $cities = $this->novaPoshtaRepository->getCities($_POST['value']);
        $result = [];

        if ($cities ) {
            foreach ($cities as $city) {
                $result[] = [
                    'name' => $this->translator->translateCity($city),
                    'value' => $city['ref']
                ];
            }
        }

        ResponseLegacy::makeAjax('success', [
            'items' => $result
        ]);
    }

    public function apiV2GetWarehouses()
    {
        $this->apiV2ValidateRequest();

        if (empty($_POST['value'])) {
            ResponseLegacy::makeAjax('error');
        }

        // todo: refactor
        $warehouses = $this->novaPoshtaRepository->getWarehouses($_POST['value']);
        $result = [];

        if ($warehouses ) {
            foreach ($warehouses as $warehouse) {
                $result[] = [
                    'name' => $this->translator->translateWarehouse($warehouse),
                    'value' => $warehouse['ref']
                ];
            }
        }

        ResponseLegacy::makeAjax('success', [
            'items' => $result
        ]);
    }

    public function apiV2CalculateCost()
    {
        $this->apiV2ValidateRequest();

        foreach ([ 'wcus_ajax', 'wcus_area_ref', 'wcus_city_ref', 'wcus_address_shipping', 'wcus_shipping_name' ] as $key) {
            if ( ! isset($_POST[ $key ])) {
                ResponseLegacy::makeAjax('error');
            }
        }

        $orderData = new CheckoutOrderData($_POST);
        $calculationService = new CalculationService();
        $cost = $calculationService->calculateCost($orderData);

        wc()->cart->set_total($orderData->getCalculatedTotal() + $cost);
        $shippingName = wp_unslash(esc_html($_POST['wcus_shipping_name']));

        wp_send_json([
            'success' => true,
            'data' => [
                'shipping' => (float)$cost
                    ? $shippingName . ': ' . wc_price($cost)
                    : $shippingName,
                'total' => wc()->cart->get_total()
            ]
        ]);
    }

    public function loadAreas()
    {
        $this->apiV2ValidateRequest();

        $response = $this->api->getAreas();

        if ($response->hasErrors()) {
            wp_send_json($response);
        }

        $this->novaPoshtaRepository->saveAreas($response);

        wp_send_json([
            'success' => true
        ]);
    }

    public function loadCities()
    {
        $this->apiV2ValidateRequest();

        $response = $this->api->getCities((int)$_POST['page']);

        if ($response->hasErrors()) {
            wp_send_json($response);
        }

        $this->novaPoshtaRepository->saveCities($response, (int)$_POST['page']);

        wp_send_json([
            'success' => true,
            'data' => [
                'loaded' => 0 === $response->getCount()
            ]
        ]);
    }

    public function loadWarehouses()
    {
        $this->apiV2ValidateRequest();

        $response = $this->api->getWarehouses((int)$_POST['page']);

        if ($response->hasErrors()) {
            wp_send_json($response);
        }

        $this->novaPoshtaRepository->saveWarehouses($response, (int)$_POST['page']);

        wp_send_json([
            'success' => true,
            'data' => [
                'loaded' => 0 === $response->getCount()
            ]
        ]);
    }

    private function apiV2ValidateRequest()
    {
        if (empty($_POST['_token']) || ! wp_verify_nonce($_POST['_token'], 'wc-ukr-shipping')) {
            ResponseLegacy::makeAjax('error');
        }
    }
}