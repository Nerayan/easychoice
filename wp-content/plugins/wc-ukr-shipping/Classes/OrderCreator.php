<?php

namespace kirillbdev\WCUkrShipping\Classes;

if ( ! defined('ABSPATH')) {
  exit;
}

class OrderCreator
{
  public function __construct()
  {
    add_action('woocommerce_checkout_create_order', [ $this, 'createOrder' ]);
  }

  public function createOrder($order)
  {
    if ($_POST[WC_UKR_SHIPPING_NP_SHIPPING_NAME . '_custom_address']) {
      $order->set_shipping_address_1(sanitize_text_field($_POST[WC_UKR_SHIPPING_NP_SHIPPING_NAME . '_custom_address']));

      return;
    }

    global $wpdb;

    $npArea = $wpdb->get_row("SELECT description FROM wc_ukr_shipping_np_areas WHERE ref = '" . esc_attr($_POST[WC_UKR_SHIPPING_NP_SHIPPING_NAME . '_area']) . "'", ARRAY_A);

    if ($npArea) {
      $order->set_shipping_state($npArea['description']);
    }

    $npCity = $wpdb->get_row("SELECT description FROM wc_ukr_shipping_np_cities WHERE ref = '" . esc_attr($_POST[WC_UKR_SHIPPING_NP_SHIPPING_NAME . '_city']) . "'", ARRAY_A);

    if ($npCity) {
      $order->set_shipping_city($npCity['description']);
    }

    $npWarehouse = $wpdb->get_row("SELECT description FROM wc_ukr_shipping_np_warehouses WHERE ref = '" . esc_attr($_POST[WC_UKR_SHIPPING_NP_SHIPPING_NAME . '_warehouse']) . "'", ARRAY_A);

    if ($npWarehouse) {
      $order->set_shipping_address_1($npWarehouse['description']);
    }
  }
}