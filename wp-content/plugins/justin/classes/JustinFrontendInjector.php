<?php

namespace morkva\JustinShip\classes;

// use morkva\JustinShip\classes\JustinApi;
// use morkva\JustinShip\classes\JustinApiNew;
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
  private $justinApiNew;

  public function __construct()
  {
    $this->translator = new NPTranslator();
    $this->justinApiNew = new JustinApiNew();

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
      .justin_shipping_method_fields {
        padding: 1px 0;
      }

      .justin-state-loading:after {
        border-color: <?php echo get_option('woo_justin_spinner_color', '#dddddd'); ?>;
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
      JUSTIN_PLUGURL . 'assets/css/admin.min.css'
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
      <div id="<?php echo JUSTIN_METHOD_NAME; ?>_fields" class="justin_shipping_method_fields">
        <div id="justin-shipping-info">
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
      $cities = array();
      $options = array( '' => '' );

      $apiCitiesJson = $this->justinApiNew->getCity();
      $apiCities = json_decode($apiCitiesJson, true );
      $cities = isset( $apiCities['data'] ) ? $apiCities['data'] : false;
      if ( ! $cities ) {
          wc_add_notice( '<b>Помилка API Justin (Cities):</b><i> Не можливо отримати дані про населені пункти.</i>', 'error' );
      }
      if ( null !== $apiCities['response']['message'] && ! empty( $apiCities['response']['message'] ) && 'ОК' != $apiCities['response']['message'] ) {
          wc_add_notice( '<b>Помилка API Justin (Cities):</b><i> ' . $apiCities['response']['message'] . '</i>', 'error' );
      }

      if ( is_array( $cities ) && ! empty( $cities ) ) {
          foreach ($cities as $city) {
            $options[$city['fields']['descr']] = $city['fields']['descr'];
          }
      }

      return array(
              'options' => $options,
              'default' => ''
             );
  }

  private function getWarehouseSelectAttributes($placeholder)
  {

    $options = array( '' => '' );

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
