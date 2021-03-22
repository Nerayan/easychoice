<?php

namespace morkva\JustinShip\DB;

class OptionsRepository
{
  /**
   * @param string $key
   * @return mixed|null
   */
  public static function getOption($key)
  {
    $defaults = [
      'woo_justin_np_method_title'           => 'Justin',
      'woo_justin_np_block_title'            => 'Доставка',
      'woo_justin_np_placeholder_area'       => 'область',
      'woo_justin_np_placeholder_city'       => 'Оберіть місто',
      'woo_justin_np_placeholder_warehouse'  => 'Оберіть відправлення',
      'woo_justin_np_address_title'          => 'доставка',
      'woo_justin_np_address_placeholder'    => 'адреса',
      'woo_justin_np_block_pos'              => 'billing'
    ];

    return get_option($key, isset($defaults[ $key ]) ? $defaults[ $key ] : null);
  }

  public function save($data)
  {
    foreach ($data['woo_justin'] as $key => $value) {
      update_option('woo_justin_' . $key, sanitize_text_field($value));
    }

    if ( ! isset($data['woo_justin']['address_shipping'])) {
      update_option('woo_justin_address_shipping', 0);
    }

    if ( ! isset($data['woo_justin']['send_statistic'])) {
      update_option('woo_justin_send_statistic', 0);
    }

    // Flush WooCommerce Shipping Cache
    delete_option('_transient_shipping-transient-version');
  }

  public function deleteAll()
  {
	  delete_option('_transient_shipping-transient-version');
  }
}
