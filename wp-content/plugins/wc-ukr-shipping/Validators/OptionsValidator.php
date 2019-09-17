<?php

namespace kirillbdev\WCUkrShipping\Validators;

class OptionsValidator
{
  public function validateRequest($data)
  {
    $errors = [];

    if ( ! isset($data['wc_ukr_shipping']['np_method_title']) || strlen($data['wc_ukr_shipping']['np_method_title']) === 0) {
      $errors['wc_ukr_shipping_np_method_title'] = 'Заполните поле';
    }

    if ( ! isset($data['wc_ukr_shipping']['np_address_title']) || strlen($data['wc_ukr_shipping']['np_address_title']) === 0) {
      $errors['wc_ukr_shipping_np_address_title'] = 'Заполните поле';
    }

    return $errors ? $errors : true;
  }
}