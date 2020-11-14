<?php

namespace kirillbdev\WCUkrShipping\Model;

use kirillbdev\WCUkrShipping\Contracts\AddressInterface;
use kirillbdev\WCUkrShipping\Contracts\OrderDataInterface;
use kirillbdev\WCUkrShipping\Services\CalculationService;

if ( ! defined('ABSPATH')) {
    exit;
}

class OrderShipping
{
    /**
     * @var \WC_Order_Item_Shipping
     */
    private $item;

    /**
     * @param \WC_Order_Item_Shipping $item
     */
    public function __construct($item)
    {
        $this->item = $item;
    }

    /**
     * @param OrderDataInterface $data
     */
    public function save($data)
    {
        $address = $data->getShippingAddress();

        if ($data->isAddressShipping()) {
            $this->saveAddressShipping($address);
        }
        else {
            $this->saveWarehouseShipping($address);
        }

        $calculationService = new CalculationService();
        $cost = $calculationService->calculateCost($data);

        $this->item->set_total($cost);
    }

    /**
     * @param string $key
     * @param mixed $value
     */
    public function updateMeta($key, $value)
    {
        $this->item->update_meta_data($key, $value);
    }

    /**
     * @param AddressInterface $address
     */
    private function saveAddressShipping($address)
    {
        $this->updateMeta('wcus_area_ref', $address->getAreaRef());
        $this->updateMeta('wcus_city_ref', $address->getCityRef());
        $this->updateMeta('wcus_address', $address->getCustomAddress());
    }

    /**
     * @param AddressInterface $address
     */
    private function saveWarehouseShipping($address)
    {
        $this->updateMeta('wcus_area_ref', $address->getAreaRef());
        $this->updateMeta('wcus_city_ref', $address->getCityRef());
        $this->updateMeta('wcus_warehouse_ref', $address->getWarehouseRef());
    }
}