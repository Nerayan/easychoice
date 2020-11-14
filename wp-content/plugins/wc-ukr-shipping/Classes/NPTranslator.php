<?php

namespace kirillbdev\WCUkrShipping\Classes;

class NPTranslator
{
  private $areaTranslates = [
    '71508128-9b87-11de-822f-000c2965ae0e' => 'АРК',
    '71508129-9b87-11de-822f-000c2965ae0e' => 'Винницкая',
    '7150812a-9b87-11de-822f-000c2965ae0e' => 'Волынская',
    '7150812b-9b87-11de-822f-000c2965ae0e' => 'Днепропетровская',
    '7150812c-9b87-11de-822f-000c2965ae0e' => 'Донецкая',
    '7150812d-9b87-11de-822f-000c2965ae0e' => 'Житомирская',
    '7150812e-9b87-11de-822f-000c2965ae0e' => 'Закарпатская',
    '7150812f-9b87-11de-822f-000c2965ae0e' => 'Запорожская',
    '71508130-9b87-11de-822f-000c2965ae0e' => 'Ивано-Франковская',
    '71508131-9b87-11de-822f-000c2965ae0e' => 'Киевская',
    '71508132-9b87-11de-822f-000c2965ae0e' => 'Кировоградская',
    '71508133-9b87-11de-822f-000c2965ae0e' => 'Луганская',
    '71508134-9b87-11de-822f-000c2965ae0e' => 'Львовская',
    '71508135-9b87-11de-822f-000c2965ae0e' => 'Николаевская',
    '71508136-9b87-11de-822f-000c2965ae0e' => 'Одесская',
    '71508137-9b87-11de-822f-000c2965ae0e' => 'Полтавская',
    '71508138-9b87-11de-822f-000c2965ae0e' => 'Ровенская',
    '71508139-9b87-11de-822f-000c2965ae0e' => 'Сумская',
    '7150813a-9b87-11de-822f-000c2965ae0e' => 'Тернопольская',
    '7150813b-9b87-11de-822f-000c2965ae0e' => 'Харьковская',
    '7150813c-9b87-11de-822f-000c2965ae0e' => 'Херсонская',
    '7150813d-9b87-11de-822f-000c2965ae0e' => 'Хмельницкая',
    '7150813e-9b87-11de-822f-000c2965ae0e' => 'Черкасская',
    '7150813f-9b87-11de-822f-000c2965ae0e' => 'Черновицкая',
    '71508140-9b87-11de-822f-000c2965ae0e' => 'Черниговская'
  ];

  /**
   * @return array
   */
  public function getTranslates()
  {
    return apply_filters('wc_ukr_shipping_get_nova_poshta_translates', [
      'method_title' => __(wc_ukr_shipping_get_option('wc_ukr_shipping_np_method_title'), 'wc-ukr-shipping'),
      'block_title' => __(wc_ukr_shipping_get_option('wc_ukr_shipping_np_block_title'), 'wc-ukr-shipping'),
      'placeholder_area' => __(wc_ukr_shipping_get_option('wc_ukr_shipping_np_placeholder_area'), 'wc-ukr-shipping'),
      'placeholder_city' => __(wc_ukr_shipping_get_option('wc_ukr_shipping_np_placeholder_city'), 'wc-ukr-shipping'),
      'placeholder_warehouse' => __(wc_ukr_shipping_get_option('wc_ukr_shipping_np_placeholder_warehouse'), 'wc-ukr-shipping'),
      'address_title' => __(wc_ukr_shipping_get_option('wc_ukr_shipping_np_address_title'), 'wc-ukr-shipping'),
      'address_placeholder' => __(wc_ukr_shipping_get_option('wc_ukr_shipping_np_address_placeholder'), 'wc-ukr-shipping')
    ]);
  }

  public function translateAreas($areas)
  {
    if (apply_filters('wc_ukr_shipping_language', get_option('wc_ukr_shipping_np_lang', 'ru')) === 'ru') {
      foreach ($areas as &$area) {
        if (isset($this->areaTranslates[ $area['ref'] ])) {
          $area['description'] = $this->areaTranslates[ $area['ref'] ];
        }
      }
    }

    return $areas;
  }
}