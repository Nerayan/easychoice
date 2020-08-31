<?php

namespace kirillbdev\WCUkrShipping\Classes;

use kirillbdev\WCUkrShipping\Services\StorageService;

if ( ! defined('ABSPATH')) {
  exit;
}

class OrderCreator
{
  private $db;

  public function __construct()
  {
    global $wpdb;

    $this->db = $wpdb;

    add_action('woocommerce_checkout_create_order', [ $this, 'createOrder' ]);
  }

  public function createOrder($order)
  {
    if ( ! $this->isNovaPoshtaShipping($order)) {
      return;
    }

    $type = $this->getShippingType();

    $this->saveArea($order, $type);
    $this->saveCity($order, $type);

    if ($this->maybeAddressShipping($type)) {
      $this->saveAddress($order, $type);
    }
    else {
      $this->saveWarehouse($order, $type);
    }
  }

  /**
   * @param \WC_Order $order
   *
   * @return bool
   */
  private function isNovaPoshtaShipping($order)
  {
    return $order->has_shipping_method(WC_UKR_SHIPPING_NP_SHIPPING_NAME);
  }

  private function maybeAddressShipping($type)
  {
    return isset($_POST['wcus_np_' . $type . '_custom_address_active'])
      && 1 === (int)$_POST['wcus_np_' . $type . '_custom_address_active'];
  }

  /**
   * @param \WC_Order $order
   * @param string $type
   */
  private function saveArea($order, $type)
  {
    $npArea = $this->db->get_row("
      SELECT description 
      FROM wc_ukr_shipping_np_areas 
      WHERE ref = '" . esc_attr($_POST['wcus_np_' . $type . '_area']) . "'
    ", ARRAY_A);

    if ($npArea) {
      $this->setOrderState($order, $npArea['description']);

      $order->update_meta_data('wc_ukr_shipping_np_area_ref', esc_attr($_POST['wcus_np_' . $type . '_area']));
    }
  }

  /**
   * @param \WC_Order $order
   */
  private function saveCity($order, $type)
  {
    $npCity = $this->db->get_row("
      SELECT description 
      FROM wc_ukr_shipping_np_cities 
      WHERE ref = '" . esc_attr($_POST['wcus_np_' . $type . '_city']) . "'
    ", ARRAY_A);

    if ($npCity) {
      $this->setOrderCity($order, $npCity['description']);

      $order->update_meta_data('wc_ukr_shipping_np_city_ref', esc_attr($_POST['wcus_np_' . $type . '_city']));
    }
  }

  /**
   * @param \WC_Order $order
   */
  private function saveWarehouse($order, $type)
  {
    $npWarehouse = $this->db->get_row("
      SELECT description 
      FROM wc_ukr_shipping_np_warehouses 
      WHERE ref = '" . esc_attr($_POST['wcus_np_' . $type . '_warehouse']) . "'
    ", ARRAY_A);

    if ($npWarehouse) {
      $this->setOrderAddress($order, $npWarehouse['description']);

      $order->update_meta_data('wc_ukr_shipping_np_warehouse_ref', esc_attr($_POST['wcus_np_' . $type . '_warehouse']));

      StorageService::setValue('wc_ukr_shipping_np_selected_warehouse', esc_attr($_POST['wcus_np_' . $type . '_warehouse']));
    }
  }

  /**
   * @param \WC_Order $order
   */
  private function saveAddress($order, $type)
  {
    $this->setOrderAddress($order, $_POST['wcus_np_' . $type . '_custom_address']);
  }

  /**
   * @param \WC_Order $order
   * @param string $state
   */
  private function setOrderState($order, $state)
  {
    if ('billing' === $this->getShippingType()) {
      $order->set_billing_state($state);
    }
    else {
      $order->set_shipping_state($state);
    }
  }

  /**
   * @param \WC_Order $order
   * @param string $city
   */
  private function setOrderCity($order, $city) {
    if ('billing' === $this->getShippingType()) {
      $order->set_billing_city($city);
    }
    else {
      $order->set_shipping_city($city);
    }
  }

  /**
   * @param \WC_Order $order
   * @param string $address
   */
  private function setOrderAddress($order, $address) {
    if ('billing' === $this->getShippingType()) {
      $order->set_billing_address_1($address);
    }
    else {
      $order->set_shipping_address_1($address);
    }
  }

  /**
   * @return string
   */
  private function getShippingType()
  {
    if (isset($_POST['ship_to_different_address']) && 1 === (int)$_POST['ship_to_different_address']) {
      return 'shipping';
    }

    return 'billing';
  }
}