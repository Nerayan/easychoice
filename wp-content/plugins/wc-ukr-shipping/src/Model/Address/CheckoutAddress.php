<?php

namespace kirillbdev\WCUkrShipping\Model\Address;

use kirillbdev\WCUkrShipping\Contracts\AddressInterface;

if ( ! defined('ABSPATH')) {
    exit;
}

class CheckoutAddress implements AddressInterface
{
    /**
     * @var array
     */
    private $data;

    /**
     * @var string
     */
    private $type;

    /**
     * @param array $data
     * @param string $shippingType
     */
    public function __construct($data, $shippingType)
    {
        $this->data = $data;
        $this->type = $shippingType;
    }

    /**
     * @return string
     */
    public function getAreaRef()
    {
        if (isset($this->data['wcus_ajax']) && 1 === (int)$this->data['wcus_ajax']) {
            return $this->data['wcus_area_ref'];
        }

        return $this->data['wcus_np_' . $this->type . '_area'];
    }

    /**
     * @return string
     */
    public function getCityRef()
    {
        if (isset($this->data['wcus_ajax']) && 1 === (int)$this->data['wcus_ajax']) {
            return $this->data['wcus_city_ref'];
        }

        return $this->data['wcus_np_' . $this->type . '_city'];
    }

    /**
     * @return string
     */
    public function getWarehouseRef()
    {
        return $this->data['wcus_np_' . $this->type . '_warehouse'];
    }

    /**
     * @return string
     */
    public function getCustomAddress()
    {
        return $this->data['wcus_np_' . $this->type . '_custom_address'];
    }

    /**
     * @return bool
     */
    public function isAddressShipping()
    {
        if (isset($this->data['wcus_ajax']) && 1 === (int)$this->data['wcus_ajax']) {
            return (int)$this->data['wcus_address_shipping'];
        }

        $key = 'wcus_np_' . $this->type . '_custom_address_active';

        if (isset($this->data[ $key ]) && (int)$this->data[ $key ]) {
            return true;
        }

        return false;
    }
}