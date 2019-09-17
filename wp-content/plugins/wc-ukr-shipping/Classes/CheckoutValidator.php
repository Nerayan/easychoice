<?php

namespace kirillbdev\WCUkrShipping\Classes;

if ( ! defined('ABSPATH')) {
  exit;
}

class CheckoutValidator
{
  public function __construct()
  {
    add_action('woocommerce_after_checkout_validation', [ $this, 'afterCheckoutValidation' ], 10, 2);
    add_action('woocommerce_checkout_process', [ $this, 'validateFields' ]);
    add_filter('woocommerce_checkout_fields', [ $this, 'removeDefaultFieldsFromValidation' ]);
    add_filter('woocommerce_checkout_posted_data', [ $this, 'processCheckoutPostedData' ]);
  }

  public function removeDefaultFieldsFromValidation($fields)
  {
    if ($this->maybeDisableDefaultFields()) {
      unset($fields['billing']['billing_address_1']);
      unset($fields['billing']['billing_address_2']);
      unset($fields['billing']['billing_city']);
      unset($fields['billing']['billing_state']);
      unset($fields['billing']['billing_postcode']);
    }

    return $fields;
  }

  public function validateFields()
  {
    if (isset($_POST['shipping_method'])) {
      if (preg_match('/^' . WC_UKR_SHIPPING_NP_SHIPPING_NAME . '.*/i', $_POST['shipping_method'][0])) {

        if ( ! $_POST[WC_UKR_SHIPPING_NP_SHIPPING_NAME . '_custom_address'] && (
            ! $_POST[WC_UKR_SHIPPING_NP_SHIPPING_NAME . '_area'] ||
            ! $_POST[WC_UKR_SHIPPING_NP_SHIPPING_NAME . '_city'] ||
            ! $_POST[WC_UKR_SHIPPING_NP_SHIPPING_NAME . '_warehouse']
          )) {
          wc_add_notice('Укажите отделение Новой Почты', 'error');
        }
      }
    }
  }

  public function processCheckoutPostedData($data)
  {
	  if (isset($data['shipping_method'])) {
		  if (
		  	preg_match('/^' . WC_UKR_SHIPPING_NP_SHIPPING_NAME . '.*/i', $data['shipping_method'][0]) &&
			  isset($data['ship_to_different_address'])
		  ) {
		  	unset($data['ship_to_different_address']);
		  	unset($data['shipping_first_name']);
			  unset($data['shipping_last_name']);
			  unset($data['shipping_company']);
			  unset($data['shipping_country']);
			  unset($data['shipping_address_1']);
			  unset($data['shipping_address_2']);
			  unset($data['shipping_city']);
			  unset($data['shipping_state']);
			  unset($data['shipping_postcode']);
		  }
	  }

  	return $data;
  }

  private function maybeDisableDefaultFields()
  {
    return isset($_POST['shipping_method']) &&
      preg_match('/^' . WC_UKR_SHIPPING_NP_SHIPPING_NAME . '.*/i', $_POST['shipping_method'][0]) &&
      apply_filters('wc_ukr_shipping_prevent_disable_default_fields', false) === false;
  }
}