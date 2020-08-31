<?php

namespace kirillbdev\WCUkrShipping\Classes;

use kirillbdev\WCUkrShipping\DB\NovaPoshtaRepository;
use kirillbdev\WCUkrShipping\Services\CheckoutService;
use kirillbdev\WCUkrShipping\Services\StorageService;
use kirillbdev\WCUkrShipping\Services\TranslateService;

if ( ! defined('ABSPATH')) {
  exit;
}

class NovaPoshtaFrontendInjector
{
  /**
   * @var TranslateService
   */
  private $translator;

  /**
   * @var CheckoutService
   */
  private $checkoutService;

  public function __construct()
  {
    $this->translator = WCUkrShipping::instance()->singleton('translate_service');
    $this->checkoutService = WCUkrShipping::instance()->singleton('checkout_service');

    add_action('wp_head', [ $this, 'injectGlobals' ]);
    add_action('wp_enqueue_scripts', [ $this, 'injectScripts' ]);
    add_action($this->getInjectActionName(), [ $this, 'injectBillingFields' ]);
    add_action('woocommerce_after_checkout_shipping_form', [ $this, 'injectShippingFields' ]);

    // Prevent default WooCommerce rate caching
    add_filter('woocommerce_shipping_rate_label', function ($label, $rate) {
      if ($rate->get_method_id() === 'nova_poshta_shipping') {
        $label = $this->translator->getTranslates()['method_title'];
      }

      return $label;
    }, 10, 2);
  }

  public function injectGlobals()
  {
    if ( ! wc_ukr_shipping_is_checkout()) {
      return;
    }

    ?>
    <style>
      .wc-ukr-shipping-np-fields {
        padding: 1px 0;
      }

      .wcus-state-loading:after {
        border-color: <?= get_option('wc_ukr_shipping_spinner_color', '#dddddd'); ?>;
        border-left-color: #fff;
      }
    </style>
  <?php
  }

  public function injectScripts()
  {
	  if ( ! wc_ukr_shipping_is_checkout()) {
		  return;
	  }

    wp_enqueue_style(
      'wc_ukr_shipping_css',
      WC_UKR_SHIPPING_PLUGIN_URL . 'assets/css/style.min.css'
    );

    wp_enqueue_script(
      'wc_ukr_shipping_nova_poshta_checkout',
      WC_UKR_SHIPPING_PLUGIN_URL . 'assets/js/checkout.min.js',
      [ 'jquery' ],
      filemtime(WC_UKR_SHIPPING_PLUGIN_DIR . 'assets/js/checkout.min.js'),
      true
    );
  }

  public function injectBillingFields()
  {
    $this->injectFields('billing');
  }

  public function injectShippingFields()
  {
    $this->injectFields('shipping');
  }

  private function injectFields($type)
  {
    if ( ! wc_ukr_shipping_is_checkout()) {
      return;
    }

    $this->checkoutService->renderCheckoutFields($type);
  }

  private function getAreaSelectAttributes($placeholder)
  {
    $options = [
      '' => $placeholder
    ];

    $repository = new NovaPoshtaRepository();
    $areas = $this->translator->translateAreas($repository->getAreas());

    foreach ($areas as $area) {
      $options[$area['ref']] = $area['description'];
    }

    return [
      'options' => $options,
      'default' => StorageService::getValue('wc_ukr_shipping_np_selected_area', '')
    ];
  }

  private function getCitySelectAttributes($placeholder)
  {
    $options = [
      '' => $placeholder
    ];

    if (StorageService::getValue('wc_ukr_shipping_np_selected_area')) {
      $repository = new NovaPoshtaRepository();
      $cities = $repository->getCities(StorageService::getValue('wc_ukr_shipping_np_selected_area'));

      foreach ($cities as $city) {
        $options[$city['ref']] = get_option('wc_ukr_shipping_np_lang', 'uk') === 'uk' ?
          $city['description'] :
          $city['description_ru'];
      }
    }

    return [
      'options' => $options,
      'default' => StorageService::getValue('wc_ukr_shipping_np_selected_city', '')
    ];
  }

  private function getWarehouseSelectAttributes($placeholder)
  {
    $options = [
      '' => $placeholder
    ];

    if (StorageService::getValue('wc_ukr_shipping_np_selected_city')) {
      $repository = new NovaPoshtaRepository();
      $warehouses = $repository->getWarehouses(StorageService::getValue('wc_ukr_shipping_np_selected_city'));

      foreach ($warehouses as $warehouse) {
        $options[$warehouse['ref']] = get_option('wc_ukr_shipping_np_lang', 'uk') === 'uk' ?
          $warehouse['description'] :
          $warehouse['description_ru'];
      }
    }

    return [
      'options' => $options,
      'default' => StorageService::getValue('wc_ukr_shipping_np_selected_warehouse', '')
    ];
  }

  private function getInjectActionName()
  {
    return 'additional' === wc_ukr_shipping_get_option('wc_ukr_shipping_np_block_pos')
      ? 'woocommerce_before_order_notes'
      : 'woocommerce_after_checkout_billing_form';
  }
}