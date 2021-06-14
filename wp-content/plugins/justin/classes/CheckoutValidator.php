<?php

namespace morkva\JustinShip\classes;

if ( ! defined('ABSPATH')) {
  exit;
}

class CheckoutValidator
{
  public function __construct()
  {
    add_action('woocommerce_checkout_process', [ $this, 'validateFields' ]);
    add_filter('woocommerce_checkout_fields', [ $this, 'removeDefaultFieldsFromValidation' ], 99);
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
  //  return true;
    if (isset($_POST['shipping_method'])) {
      if (preg_match('/^' . JUSTIN_METHOD_NAME . '.*/i', $_POST['shipping_method'][0])) {

        if ( ! $_POST[JUSTIN_METHOD_NAME . '_city'] || ! $_POST[JUSTIN_METHOD_NAME . '_warehouse'] )  {
          wc_add_notice('Justin доставка потребує детальнішого заповнення форми', 'error');
        }
      }
    }
  }

  public function processCheckoutPostedData($data)
  {
	  if (isset($data['shipping_method'])) {
		  if (
		  	preg_match('/^' . JUSTIN_METHOD_NAME . '.*/i', $data['shipping_method'][0]) &&
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
    // return isset($_POST['shipping_method']) &&
    //   preg_match('/^' . JUSTIN_METHOD_NAME . '.*/i', $_POST['shipping_method'][0]) &&
    //   apply_filters('woo_justin_prevent_disable_default_fields', false) === false;
    return apply_filters('woo_justin_prevent_disable_default_fields', false) === false;
  }
}
