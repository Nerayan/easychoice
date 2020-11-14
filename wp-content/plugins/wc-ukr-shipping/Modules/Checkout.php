<?php

namespace kirillbdev\WCUkrShipping\Modules;

if (!defined('ABSPATH')) {
    exit;
}

use kirillbdev\WCUkrShipping\Contracts\ModuleInterface;
use kirillbdev\WCUkrShipping\DB\NovaPoshtaRepository;
use kirillbdev\WCUkrShipping\Helpers\HtmlHelper;
use kirillbdev\WCUkrShipping\Services\TranslateService;

class Checkout implements ModuleInterface
{
    /**
     * @var TranslateService
     */
    private $translator;

    /**
     * @var NovaPoshtaRepository
     */
    private $repository;

    /**
     * Cache translates of shipping block.
     *
     * @var array
     */
    private $translates;

    /**
     * Cache area select attributes of shipping block.
     *
     * @var array
     */
    private $areaAttributes;

    /**
     * Cache city select attributes of shipping block.
     *
     * @var array
     */
    private $cityAttributes;

    /**
     * Cache warehouse select attributes of shipping block.
     *
     * @var array
     */
    private $warehouseAttributes;

    public function __construct()
    {
        $this->translator = wcus_container_singleton('translate_service');
        $this->repository = new NovaPoshtaRepository();
    }

    /**
     * Boot function
     *
     * @return void
     */
    public function init()
    {
        add_action($this->getInjectActionName(), [$this, 'injectBillingFields']);
        add_action('woocommerce_after_checkout_shipping_form', [$this, 'injectShippingFields']);
        add_filter('woocommerce_cart_shipping_method_full_label', [$this, 'wrapShippingCost'], 10, 2);
        add_filter('woocommerce_cart_totals_order_total_html', [$this, 'wrapOrderTotal']);
        add_action( 'woocommerce_after_shipping_rate', [ $this, 'injectShippingName' ], 10, 2);
    }

    public function injectBillingFields()
    {
        $this->injectFields('billing');
    }

    public function injectShippingFields()
    {
        $this->injectFields('shipping');
    }

    public function wrapShippingCost($label, $method)
    {
        if ($method->get_method_id() === WC_UKR_SHIPPING_NP_SHIPPING_NAME) {
            return '<span id="wcus-shipping-cost">' . $label . '</span>';
        }

        return $label;
    }

    public function wrapOrderTotal($value)
    {
        return '<span id="wcus-order-total">' . $value . '</span>';
    }

    public function injectShippingName($method, $index)
    {
        if ($method->get_method_id() === WC_UKR_SHIPPING_NP_SHIPPING_NAME) {
            echo '<input id="wcus-shipping-name" type="hidden" value="' . $method->get_label() . '">';
        }
    }

    private function injectFields($type)
    {
        if (!wc_ukr_shipping_is_checkout()) {
            return;
        }

        $this->renderCheckoutFields($type);
    }

    private function renderCheckoutFields($type)
    {
        $this->initShippingBlockAttributes();

        ?>
      <div id="wcus_np_<?= $type; ?>_fields" class="wc-ukr-shipping-np-fields">
        <h3><?= $this->translates['block_title']; ?></h3>
          <?php
          $this->renderAreaField($type);
          $this->renderCityField($type);
          ?>
        <div class="j-wcus-warehouse-block">
            <?php $this->renderWarehouseField($type); ?>
        </div>

          <?php if ((int)get_option('wc_ukr_shipping_address_shipping', 1) === 1) { ?>
            <div class="wc-urk-shipping-form-group" style="padding: 10px 5px;">
              <label class="wc-ukr-shipping-checkbox">
                <input id="wcus_np_<?= $type; ?>_custom_address_active"
                       type="checkbox"
                       name="wcus_np_<?= $type; ?>_custom_address_active"
                       class="j-wcus-np-custom-address"
                       data-relation-select="<?= 'billing' === $type ? 'wcus_np_shipping_custom_address_active' : 'wcus_np_billing_custom_address_active'; ?>"
                       value="1">
                  <?= $this->translates['address_title']; ?>
              </label>
            </div>
            <div class="j-wcus-np-custom-address-block" style="display: none;">
                <?php
                woocommerce_form_field('wcus_np_' . $type . '_custom_address', [
                    'type' => 'text',
                    'input_class' => [
                        'input-text'
                    ],
                    'label' => '',
                    'placeholder' => $this->translates['address_placeholder'],
                    'default' => ''
                ]);
                ?>
            </div>
          <?php } ?>
      </div>
        <?php
    }

    private function initShippingBlockAttributes()
    {
        if ($this->translates) {
            return;
        }

        $this->translates = $this->translator->getTranslates();
        $this->areaAttributes = $this->getAreaSelectAttributes($this->translates['placeholder_area']);
        $this->cityAttributes = $this->getCitySelectAttributes($this->translates['placeholder_city']);
        $this->warehouseAttributes = $this->getWarehouseSelectAttributes($this->translates['placeholder_warehouse']);
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
            'default' => ''
        ];
    }

    private function getCitySelectAttributes($placeholder)
    {
        $options = [
            '' => $placeholder
        ];

        return [
            'options' => $options,
            'default' => ''
        ];
    }

    private function getWarehouseSelectAttributes($placeholder)
    {
        $options = [
            '' => $placeholder
        ];

        return [
            'options' => $options,
            'default' => ''
        ];
    }

    private function renderAreaField($type)
    {
        if ((int)wc_ukr_shipping_get_option('wc_ukr_shipping_np_new_ui', 0)) {
            $this->renderNativeAreaField($type);
        } else {
            woocommerce_form_field('wcus_np_' . $type . '_area', [
                'type' => 'select',
                'options' => $this->areaAttributes['options'],
                'input_class' => [
                    'wc-ukr-shipping-select',
                    'j-wcus-np-area-select'
                ],
                'label' => '',
                'default' => $this->areaAttributes['default'],
                'custom_attributes' => [
                    'data-mirror' => 'billing' === $type
                        ? 'wcus_np_shipping_area'
                        : 'wcus_np_billing_area'
                ]
            ]);
        }
    }

    private function renderCityField($type)
    {
        if ((int)wc_ukr_shipping_get_option('wc_ukr_shipping_np_new_ui')) {
            $this->renderNativeCityField($type);
        } else {
            woocommerce_form_field('wcus_np_' . $type . '_city', [
                'type' => 'select',
                'options' => $this->cityAttributes['options'],
                'input_class' => [
                    'wc-ukr-shipping-select',
                    'j-wcus-np-city-select'
                ],
                'label' => '',
                'default' => $this->cityAttributes['default'],
                'custom_attributes' => [
                    'data-mirror' => 'billing' === $type
                        ? 'wcus_np_shipping_city'
                        : 'wcus_np_billing_city'
                ]
            ]);
        }
    }

    private function renderWarehouseField($type)
    {
        if ((int)wc_ukr_shipping_get_option('wc_ukr_shipping_np_new_ui', 0)) {
            $this->renderNativeWarehouseField($type);
        } else {
            woocommerce_form_field('wcus_np_' . $type . '_warehouse', [
                'type' => 'select',
                'options' => $this->warehouseAttributes['options'],
                'input_class' => [
                    'wc-ukr-shipping-select',
                    'j-wcus-np-warehouse-select'
                ],
                'label' => '',
                'default' => $this->warehouseAttributes['default'],
                'custom_attributes' => [
                    'data-mirror' => 'billing' === $type
                        ? 'wcus_np_shipping_warehouse'
                        : 'wcus_np_billing_warehouse'
                ]
            ]);
        }
    }

    private function renderNativeAreaField($type)
    {
        ?>
      <p class="form-row" id="wcus_np_<?= $type; ?>_area_field">
        <span class="woocommerce-input-wrapper">
          <?php
          HtmlHelper::selectField('wcus_np_' . $type . '_area', [
              'options' => $this->areaAttributes['options'],
              'class' => [
                  'select',
                  'wc-ukr-shipping-select',
                  'j-wcus-select-2'
              ],
              'attributes' => [
                  'data-mirror' => 'billing' === $type ? 'wcus_np_shipping_area' : 'wcus_np_billing_area'
              ],
              'value' => $this->areaAttributes['default']
          ]);
          ?>
        </span>
      </p>
        <?php
    }

    private function renderNativeCityField($type)
    {
        ?>
      <p class="form-row" id="wcus_np_<?= $type; ?>_city_field">
        <span class="woocommerce-input-wrapper">
          <?php
          HtmlHelper::selectField('wcus_np_' . $type . '_city', [
              'options' => $this->cityAttributes['options'],
              'class' => [
                  'select',
                  'wc-ukr-shipping-select',
                  'j-wcus-select-2'
              ],
              'attributes' => [
                  'data-mirror' => 'billing' === $type ? 'wcus_np_shipping_city' : 'wcus_np_billing_city'
              ],
              'value' => $this->cityAttributes['default']
          ]);
          ?>
        </span>
      </p>
        <?php
    }

    private function renderNativeWarehouseField($type)
    {
        ?>
      <p class="form-row" id="wcus_np_<?= $type; ?>_warehouse_field">
        <span class="woocommerce-input-wrapper">
          <?php
          HtmlHelper::selectField('wcus_np_' . $type . '_warehouse', [
              'options' => $this->warehouseAttributes['options'],
              'class' => [
                  'select',
                  'wc-ukr-shipping-select',
                  'j-wcus-select-2'
              ],
              'attributes' => [
                  'data-mirror' => 'billing' === $type ? 'wcus_np_shipping_warehouse' : 'wcus_np_billing_warehouse'
              ],
              'value' => $this->warehouseAttributes['default']
          ]);
          ?>
        </span>
      </p>
        <?php
    }

    private function getInjectActionName()
    {
        return 'additional' === wc_ukr_shipping_get_option('wc_ukr_shipping_np_block_pos')
            ? 'woocommerce_before_order_notes'
            : 'woocommerce_after_checkout_billing_form';
    }
}