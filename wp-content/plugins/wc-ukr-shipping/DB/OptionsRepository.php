<?php

namespace kirillbdev\WCUkrShipping\DB;

class OptionsRepository
{
  public function save($data)
  {
    foreach ($data['wc_ukr_shipping'] as $key => $value) {
      update_option('wc_ukr_shipping_' . $key, sanitize_text_field($value));
    }

    if ( ! isset($data['wc_ukr_shipping']['address_shipping'])) {
      update_option('wc_ukr_shipping_address_shipping', 0);
    }

    if ( ! isset($data['wc_ukr_shipping']['send_statistic'])) {
      update_option('wc_ukr_shipping_send_statistic', 0);
    }

    // Flush WooCommerce Shipping Cache
    delete_option('_transient_shipping-transient-version');
  }

  public function deleteAll()
  {
	  delete_option('_transient_shipping-transient-version');
  }
}