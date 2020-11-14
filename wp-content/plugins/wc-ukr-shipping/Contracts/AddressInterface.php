<?php

namespace kirillbdev\WCUkrShipping\Contracts;

if ( ! defined('ABSPATH')) {
    exit;
}

interface AddressInterface
{
    /**
     * @return string
     */
    public function getAreaRef();

    /**
     * @return string
     */
    public function getCityRef();

    /**
     * @return string
     */
    public function getWarehouseRef();

    /**
     * @return string
     */
    public function getCustomAddress();

    /**
     * @return bool
     */
    public function isAddressShipping();
}