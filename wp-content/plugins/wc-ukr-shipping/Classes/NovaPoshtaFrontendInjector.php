<?php

namespace kirillbdev\WCUkrShipping\Classes;

if ( ! defined('ABSPATH')) {
  exit;
}

class NovaPoshtaFrontendInjector
{
  public function __construct()
  {
    add_action('wp_head', [ $this, 'injectGlobals' ]);
    add_action('wp_enqueue_scripts', [ $this, 'injectScripts' ]);
    add_action('woocommerce_after_checkout_billing_form', [ $this, 'injectShippingFields' ]);
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
        border-color: <?= get_option('wc_ukr_shipping_spinner_color', '#5867dd'); ?>;
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
      'wc_ukr_shipping_css',
      WC_UKR_SHIPPING_PLUGIN_URL . 'assets/css/style.min.css'
    );

    wp_enqueue_script(
      'wc_ukr_shipping_nova_poshta_checkout',
      WC_UKR_SHIPPING_PLUGIN_URL . 'assets/js/nova-poshta-checkout.js',
      [ 'jquery' ],
      filemtime(WC_UKR_SHIPPING_PLUGIN_DIR . 'assets/js/nova-poshta-checkout.js'),
      true
    );
  }

  public function injectShippingFields()
  {
	  if ( ! is_checkout()) {
		  return;
	  }

    ?>
      <div id="<?= WC_UKR_SHIPPING_NP_SHIPPING_NAME; ?>_fields" class="wc-ukr-shipping-np-fields">
        <h3><?= __('Укажите адрес доставки', 'wc-ukr-shipping'); ?></h3>
        <div id="nova-poshta-shipping-info">
          <?php
          //Region
          woocommerce_form_field(WC_UKR_SHIPPING_NP_SHIPPING_NAME . '_area', [
            'type' => 'select',
            'options' => [
              '' => __('Выберите область', 'wc-ukr-shipping')
            ],
            'input_class' => [
              'wc-ukr-shipping-select'
            ],
            'label' => ''
          ]);

          //City
          woocommerce_form_field(WC_UKR_SHIPPING_NP_SHIPPING_NAME . '_city', [
            'type' => 'select',
            'options' => [
              '' => __('Выберите город', 'wc-ukr-shipping')
            ],
            'input_class' => [
              'wc-ukr-shipping-select'
            ],
            'label' => ''
          ]);

          //Warehouse
          woocommerce_form_field(WC_UKR_SHIPPING_NP_SHIPPING_NAME . '_warehouse', [
            'type' => 'select',
            'options' => [
              '' => __('Выберите отделение', 'wc-ukr-shipping')
            ],
            'input_class' => [
              'wc-ukr-shipping-select'
            ],
            'label' => ''
          ]);

          ?>
        </div>

        <?php if ((int)get_option('wc_ukr_shipping_address_shipping', 1) === 1) { ?>
          <div class="wc-urk-shipping-form-group" style="padding: 10px 5px;">
            <label class="wc-ukr-shipping-checkbox">
              <input id="np_custom_address" type="checkbox" name="np_custom_address" value="1">
              <?= get_option('wc_ukr_shipping_np_address_title', __('Указать произвольный адрес', 'wc-ukr-shipping')); ?>
            </label>
          </div>

          <div id="np_custom_address_block">
            <?php

            // Custom address field
            woocommerce_form_field(WC_UKR_SHIPPING_NP_SHIPPING_NAME . '_custom_address', [
              'type' => 'text',
              'input_class' => [
                'input-text'
              ],
              'label' => '',
              'placeholder' => __('Укажите адрес', 'wc-ukr-shipping')
            ]);
            ?>
          </div>
        <?php } ?>
      </div>
    <?php
  }
}