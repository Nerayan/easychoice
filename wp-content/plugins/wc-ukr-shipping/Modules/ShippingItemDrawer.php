<?php

namespace kirillbdev\WCUkrShipping\Modules;

use kirillbdev\WCUkrShipping\Contracts\ModuleInterface;
use kirillbdev\WCUkrShipping\DB\NovaPoshtaRepository;

if ( ! defined('ABSPATH')) {
    exit;
}

class ShippingItemDrawer implements ModuleInterface
{
    /**
     * @var NovaPoshtaRepository
     */
    private $repository;

    public function __construct()
    {
        $this->repository = new NovaPoshtaRepository();
    }

    /**
     * Boot function
     */
    public function init()
    {
        add_filter('woocommerce_order_item_display_meta_key', [ $this, 'getMetaLabel' ], 10, 2);
        add_filter('woocommerce_order_item_display_meta_value', [ $this, 'getMetaValue' ], 10, 2);
    }

    public function getMetaLabel($key, $meta)
    {
        switch ($key) {
            case 'wcus_area_ref':
                return 'Область';
            case 'wcus_city_ref':
                return 'Город';
            case 'wcus_warehouse_ref':
                return 'Отделение';
            case 'wcus_address':
                return 'Адрес';
            default:
                return $key;
        }
    }

    public function getMetaValue($value, $meta)
    {
        switch ($meta->key) {
            case 'wcus_area_ref':
                return $this->getArea($value);
            case 'wcus_city_ref':
                return $this->getCity($value);
            case 'wcus_warehouse_ref':
                return $this->getWarehouse($value);
            default:
                return $value;
        }
    }

    private function getArea($ref)
    {
        $area = $this->repository->getAreaByRef($ref);

        return $area
            ? $area['description']
            : '';
    }

    private function getCity($ref)
    {
        $city = $this->repository->getCityByRef($ref);

        return $city
            ? $city['description']
            : '';
    }

    private function getWarehouse($ref)
    {
        $warehouse = $this->repository->getWarehouseByRef($ref);

        return $warehouse
            ? $warehouse['description']
            : '';
    }
}