<?php

namespace morkva\JustinShip\classes;

use morkva\JustinShip\Api\JustinApi;
use morkva\JustinShip\DB\JustinRepository;

if ( ! defined('ABSPATH')) {
  exit;
}

class JustinFrontendInjector
{
  /**
   * @var NPTranslator
   */
  private $translator;

  public function __construct()
  {
    $this->translator = new NPTranslator();

    add_action('wp_head', [ $this, 'injectGlobals' ]);
    add_action('wp_enqueue_scripts', [ $this, 'injectScripts' ]);
    add_action($this->getInjectActionName(), [ $this, 'injectShippingFields' ]);

    // Prevent default WooCommerce rate caching
    add_filter('woocommerce_shipping_rate_label', function ($label, $rate) {
      if ($rate->get_method_id() === 'justin_shipping_method') {
        $label = $this->translator->getTranslates()['method_title'];
      }

      return $label;
    }, 10, 2);
  }

  public function injectGlobals()
  {
    if ( ! is_checkout()) {
      return;
    }

    ?>
    <style>
      .wc-ukr-shipping-np-fields {
        padding: 1px 0;
      }

      .wcus-state-loading:after {
        border-color: <?= get_option('woo_justin_spinner_color', '#dddddd'); ?>;
        border-left-color: #fff;
      }
    </style>
  <?php
  }

  public function injectScripts()
  {
	  if ( ! is_checkout()) {
		  return;
	  }

    wp_enqueue_style(
      'woo_justin_css',
      JUSTIN_PLUGURL . 'assets/css/style.min.css'
    );

    wp_enqueue_script(
      'woo_justin_checkout',
      JUSTIN_PLUGURL . 'assets/js/checkoutj.js',
      [ 'jquery' ],
      filemtime(JUSTIN_PLUGFOLDER . 'assets/js/checkoutj.js'),
      true
    );
  }

  public function injectShippingFields()
  {
	  if ( ! is_checkout()) {
		  return;
	  }

	  $translates = $this->translator->getTranslates();
	  $areaAttributes = $this->getAreaSelectAttributes($translates['placeholder_area']);
	  $cityAttributes = $this->getCitySelectAttributes($translates['placeholder_city']);
	  $warehouseAttributes = $this->getWarehouseSelectAttributes($translates['placeholder_warehouse']);

    ?>
      <div id="<?= JUSTIN_METHOD_NAME; ?>_fields" class="wc-ukr-shipping-np-fields">
        <div id="nova-poshta-shipping-info">
          <?php
          //City
          woocommerce_form_field(JUSTIN_METHOD_NAME . '_city', [
            'type' => 'select',
            'required'=>true,
            'options' => $cityAttributes['options'],
            'input_class' => [
              'justin-select'
            ],
            'label' => 'Місто',
            'default' => $cityAttributes['default']
          ]);

          //Warehouse
          woocommerce_form_field(JUSTIN_METHOD_NAME . '_warehouse', [
            'type' => 'select',
            'required'=>true,
            'options' => $warehouseAttributes['options'],
            'input_class' => [
              'justin-select'
            ],
            'label' => 'Justin Відділення',
            'default' => $warehouseAttributes['default']
          ]);

          ?>
        </div>


      </div>
    <?php
  }

  private function getAreaSelectAttributes($placeholder)
  {
    $options = [
      '' => $placeholder
    ];


    return [
      'options' => $options,
      'default' => ""
    ];
  }

  private function getCitySelectAttributes($placeholder)
  {

    $str = file_get_contents(JUSTIN_PLUGFOLDER.'classes/json/localities.json');
    $json = json_decode($str, true); 
    $options = [""=>""];

    if (true) {
      $repository = new JustinRepository();
      $cities = $json['result'];

      foreach ($cities as $city) {
        $options[$city['title_ua']] =  $city['title_ua'];
      }
    }


    return [
      'options' => $options,
      'default' => ''
    ];
  }

  private function getWarehouseSelectAttributes($placeholder)
  {
    $options = [
      '' => ''
    ];

    return [
      'options' => $options,
      'default' => ''
    ];
  }

  private function getInjectActionName()
  {
    return 'additional' === woo_justin_get_option('woo_justin_np_block_pos')
      ? 'woocommerce_before_order_notes'
      : 'woocommerce_after_checkout_billing_form';
  }
}
