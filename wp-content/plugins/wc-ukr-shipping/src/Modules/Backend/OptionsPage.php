<?php

namespace kirillbdev\WCUkrShipping\Modules\Backend;

use kirillbdev\WCUkrShipping\Http\Controllers\OptionsController;
use kirillbdev\WCUSCore\Contracts\ModuleInterface;
use kirillbdev\WCUSCore\Foundation\View;
use kirillbdev\WCUSCore\Http\Routing\Route;

if ( ! defined('ABSPATH')) {
    exit;
}

class OptionsPage implements ModuleInterface
{
    public function init()
    {
        add_action('admin_menu', [$this, 'registerOptionsPage'], 99);
    }

    public function routes()
    {
        return [
            new Route('wcus_save_options', OptionsController::class, 'save')
        ];
    }

    public function registerOptionsPage()
    {
        add_menu_page(
            __('options_page_title', WCUS_TRANSLATE_DOMAIN),
            'WC Ukr Shipping',
            'manage_options',
            'wc_ukr_shipping_options',
            [$this, 'html'],
            WC_UKR_SHIPPING_PLUGIN_URL . 'image/menu-icon.png',
            '56.15'
        );

        add_submenu_page(
            'wc_ukr_shipping_options',
            __('nav_item_premium', WCUS_TRANSLATE_DOMAIN),
            wc_ukr_shipping_import_svg('star.svg') . __('nav_item_premium', WCUS_TRANSLATE_DOMAIN),
            'manage_options',
            'wcus_submenu_premium',
            [$this, 'premiumHtml']
        );
    }

    public function html()
    {
        echo View::render('settings');
    }

    public function premiumHtml()
    {
        wp_redirect('https://kirillbdev.pro/wc-ukr-shipping-pro/?ref=plugin_menu', 301);
        exit;
    }
}