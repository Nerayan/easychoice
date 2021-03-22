<?php

namespace morkva\JustinShip\classes;
 

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
    if ($_POST[JUSTIN_METHOD_NAME . '_custom_address']) {
      $order->set_shipping_address_1(sanitize_text_field($_POST[JUSTIN_METHOD_NAME . '_custom_address']));
      $order->set_billing_address_1(sanitize_text_field($_POST[JUSTIN_METHOD_NAME . '_custom_address']));

      return;
    }






    if (true) {
      $order->set_shipping_city(esc_attr($_POST[JUSTIN_METHOD_NAME . '_city']));
      $order->set_billing_city(esc_attr($_POST[JUSTIN_METHOD_NAME . '_city']));
    }
    if (true) {
      $order->set_shipping_address_1(esc_attr($_POST[JUSTIN_METHOD_NAME . '_warehouse']));
      $order->set_billing_address_1(esc_attr($_POST[JUSTIN_METHOD_NAME . '_warehouse']));
    }
  }
}
