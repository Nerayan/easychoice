<?php

namespace morkva\JustinShip\classes;

class NPTranslator
{
  private $areaTranslates = ['' ];

  /**
   * @return array
   */
  public function getTranslates()
  {
    return apply_filters('woo_justin_get_nova_poshta_translates', [
      'method_title' => __(woo_justin_get_option('woo_justin_np_method_title'), 'wc-ukr-shipping'),
      'block_title' => __(woo_justin_get_option('woo_justin_np_block_title'), 'wc-ukr-shipping'),
      'placeholder_area' => __(woo_justin_get_option('woo_justin_np_placeholder_area'), 'wc-ukr-shipping'),
      'placeholder_city' => __(woo_justin_get_option('woo_justin_np_placeholder_city'), 'wc-ukr-shipping'),
      'placeholder_warehouse' => __(woo_justin_get_option('woo_justin_np_placeholder_warehouse'), 'wc-ukr-shipping'),
      'address_title' => __(woo_justin_get_option('woo_justin_np_address_title'), 'wc-ukr-shipping'),
      'address_placeholder' => __(woo_justin_get_option('woo_justin_np_address_placeholder'), 'wc-ukr-shipping')
    ]);
  }

  public function translateAreas($areas)
  {
    if (apply_filters('woo_justin_language', get_option('woo_justin_np_lang', 'ru')) === 'ru') {
      foreach ($areas as &$area) {
        if (isset($this->areaTranslates[ $area['ref'] ])) {
          $area['description'] = $this->areaTranslates[ $area['ref'] ];
        }
      }
    }

    return $areas;
  }
}
