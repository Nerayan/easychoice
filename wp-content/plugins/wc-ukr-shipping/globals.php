<?php

if ( ! defined('ABSPATH')) {
    exit;
}

if ( ! function_exists('wc_ukr_shipping')) {

    function wc_ukr_shipping()
    {
        return \kirillbdev\WCUkrShipping\Classes\WCUkrShipping::instance();
    }

}

if ( ! function_exists('wc_ukr_shipping_import_svg')) {

    function wc_ukr_shipping_import_svg($image)
    {
        return file_get_contents(WC_UKR_SHIPPING_PLUGIN_DIR . '/image/' . $image);
    }

}

if ( ! function_exists('wc_ukr_shipping_get_option')) {

    function wc_ukr_shipping_get_option($key)
    {
        return \kirillbdev\WCUkrShipping\DB\OptionsRepository::getOption($key);
    }

}

if ( ! function_exists('wc_ukr_shipping_is_checkout')) {

    function wc_ukr_shipping_is_checkout()
    {
        return function_exists('is_checkout') && is_checkout();
    }

}

if ( ! function_exists('wcus_container_singleton')) {

    function wcus_container_singleton($abstract)
    {
        return \kirillbdev\WCUkrShipping\Classes\WCUkrShipping::instance()->singleton($abstract);
    }

}