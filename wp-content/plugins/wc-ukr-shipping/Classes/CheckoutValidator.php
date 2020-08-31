<?php

namespace kirillbdev\WCUkrShipping\Classes;

if ( ! defined('ABSPATH')) {
  exit;
}

class CheckoutValidator
{
  public function __construct()
  {
    add_action('woocommerce_checkout_process', [ $this, 'validateFields' ]);
    add_filter('woocommerce_checkout_fields', [ $this, 'removeDefaultFieldsFromValidation' ]);
  }

  /**
   * @param array $fields
   * @return array
   */
  public function removeDefaultFieldsFromValidation($fields)
  {
    if ($this->isNovaPoshtaSelected()) {
      if ($this->maybeDisableDefaultFields()) {
        foreach ([ 'billing', 'shipping' ] as $type) {
          unset($fields[$type][$type . '_address_1']);
          unset($fields[$type][$type . '_address_2']);
          unset($fields[$type][$type . '_city']);
          unset($fields[$type][$type . '_state']);
          unset($fields[$type][$type . '_postcode']);
        }
      }
    }

    return $fields;
  }

  public function validateFields()
  {
    if ($this->isNovaPoshtaSelected()) {
      $type = $this->getTypeToValidate();

      if ($this->maybeAddressShippingSelected($type)) {
        $this->validateAddressShipping($type);

        return;
      }

      $this->validateWarehouseShipping($type);
    }
  }

  /**
   * @return bool
   */
  private function maybeDisableDefaultFields()
  {
    return isset($_POST['shipping_method']) &&
      preg_match('/^' . WC_UKR_SHIPPING_NP_SHIPPING_NAME . '.*/i', $_POST['shipping_method'][0]) &&
      apply_filters('wc_ukr_shipping_prevent_disable_default_fields', false) === false;
  }

  /**
   * @param string $type
   *
   * @return bool
   */
  private function maybeAddressShippingSelected($type)
  {
    return isset($_POST['wcus_np_' . $type . '_custom_address_active'])
      && 1 === (int)$_POST['wcus_np_' . $type . '_custom_address_active'];
  }

  /**
   * @param string $type
   */
  private function validateAddressShipping($type)
  {
    if (
      empty($_POST['wcus_np_' . $type . '_area'])
      || empty($_POST['wcus_np_' . $type . '_city'])
      || empty($_POST['wcus_np_' . $type . '_custom_address'])
    ) {
      $this->addErrorNotice();
    }
  }

  /**
   * @param string $type
   */
  private function validateWarehouseShipping($type)
  {
    if (
      empty($_POST['wcus_np_' . $type . '_area'])
      || empty($_POST['wcus_np_' . $type . '_city'])
      || empty($_POST['wcus_np_' . $type . '_warehouse'])
    ) {
      $this->addErrorNotice();
    }
  }

  private function addErrorNotice()
  {
    wc_add_notice('Укажите адрес <strong>Новой Почты</strong>', 'error');
  }

  /**
   * @return string
   */
  private function getTypeToValidate()
  {
    if (isset($_POST['ship_to_different_address']) && 1 === (int)$_POST['ship_to_different_address']) {
      return 'shipping';
    }

    return 'billing';
  }

  /**
   * @return bool
   */
  private function isNovaPoshtaSelected()
  {
    if (isset($_POST['shipping_method']) && preg_match('/^' . WC_UKR_SHIPPING_NP_SHIPPING_NAME . '.*/i', $_POST['shipping_method'][0])) {
      return true;
    }

    return false;
  }
}