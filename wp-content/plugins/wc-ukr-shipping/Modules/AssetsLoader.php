<?php

namespace kirillbdev\WCUkrShipping\Modules;

use kirillbdev\WCUkrShipping\Classes\WCUkrShipping;
use kirillbdev\WCUkrShipping\Contracts\ModuleInterface;

if ( ! defined('ABSPATH')) {
    exit;
}

class AssetsLoader implements ModuleInterface
{
    public function init()
    {
        add_action('wp_head', [ $this, 'loadCheckoutStyles' ]);
        add_action('wp_enqueue_scripts', [ $this, 'loadFrontendAssets' ]);
        add_action('admin_enqueue_scripts', [ $this, 'loadAdminAssets' ]);
    }

    public function loadFrontendAssets()
    {
        if ( ! wc_ukr_shipping_is_checkout()) {
            return;
        }

        wp_enqueue_style(
            'wc_ukr_shipping_css',
            WC_UKR_SHIPPING_PLUGIN_URL . 'assets/css/style.min.css'
        );

        wp_enqueue_script(
            'wcus_checkout_js',
            WC_UKR_SHIPPING_PLUGIN_URL . 'assets/js/checkout.min.js',
            [ 'jquery' ],
            filemtime(WC_UKR_SHIPPING_PLUGIN_DIR . 'assets/js/checkout.min.js'),
            true
        );

        $this->injectGlobals('wcus_checkout_js');
    }

    public function loadAdminAssets()
    {
        wp_enqueue_script('wp-color-picker');
        wp_enqueue_style('wp-color-picker');

        wp_enqueue_style(
            'wc_ukr_shipping_admin_css',
            WC_UKR_SHIPPING_PLUGIN_URL . 'assets/css/admin.min.css',
            [],
            filemtime(WC_UKR_SHIPPING_PLUGIN_DIR . 'assets/css/admin.min.css')
        );

        wp_enqueue_script(
            'wc_ukr_shipping_tabs_js',
            WC_UKR_SHIPPING_PLUGIN_URL . 'assets/js/tabs.js',
            [],
            filemtime(WC_UKR_SHIPPING_PLUGIN_DIR . 'assets/js/tabs.js')
        );

        wp_enqueue_script(
            'wcus_settings_js',
            WC_UKR_SHIPPING_PLUGIN_URL . 'assets/js/settings.min.js',
            [],
            filemtime(WC_UKR_SHIPPING_PLUGIN_DIR . 'assets/js/settings.min.js'),
            true
        );

        $this->injectGlobals('wcus_settings_js');
    }

    public function loadCheckoutStyles()
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

    private function injectGlobals($scriptId)
    {
        $translator = WCUkrShipping::instance()->singleton('translate_service');
        $translates = $translator->getTranslates();

        $data = [
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'homeUrl' => home_url(),
            'lang' => $translator->getCurrentLanguage(),
            'nonce' => wp_create_nonce('wc-ukr-shipping'),
            'disableDefaultBillingFields' => apply_filters('wc_ukr_shipping_prevent_disable_default_fields', false) === false ?
                'true' :
                'false',
            'i10n' => [
                'placeholder_area' => $translates['placeholder_area'],
                'placeholder_city' => $translates['placeholder_city'],
                'placeholder_warehouse' => $translates['placeholder_warehouse'],
                'not_found' => $translates['not_found']
            ]
        ];

        wp_localize_script($scriptId, 'wc_ukr_shipping_globals', $data);
    }
}