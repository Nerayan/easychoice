<?php

namespace kirillbdev\WCUkrShipping\Classes;

use kirillbdev\WCUkrShipping\Contracts\ModuleInterface;
use kirillbdev\WCUkrShipping\Modules\Activator;
use kirillbdev\WCUkrShipping\Modules\Ajax;
use kirillbdev\WCUkrShipping\Modules\AssetsLoader;
use kirillbdev\WCUkrShipping\Modules\Backend\PluginInfo;
use kirillbdev\WCUkrShipping\Modules\Cart;
use kirillbdev\WCUkrShipping\Modules\Checkout;
use kirillbdev\WCUkrShipping\Modules\OptionsPage;
use kirillbdev\WCUkrShipping\Modules\ShippingItemDrawer;
use kirillbdev\WCUkrShipping\Modules\ShippingMethod;
use kirillbdev\WCUkrShipping\Modules\CheckoutValidator;
use kirillbdev\WCUkrShipping\Services\TranslateService;
use kirillbdev\WCUkrShipping\Modules\OrderCreator;

if ( ! defined('ABSPATH')) {
    exit;
}

final class WCUkrShipping
{
    /**
     * @var WCUkrShipping
     */
    private static $instance = null;

    /**
     * @var ModuleInterface[]
     */
    private $modules = [];

    /**
     * @var Router
     */
    private $router;

    private function __construct()
    {
        $this->instantiateContainer();
    }

    public static function instance()
    {
        if (!self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public function __get($name)
    {
        return $this->$name;
    }

    public function init()
    {
        $this->router = new Router();

        $this->initModule(Activator::class);
        $this->initModule(AssetsLoader::class);
        $this->initModule(OptionsPage::class);
        $this->initModule(Ajax::class);
        $this->initModule(Checkout::class);
        $this->initModule(ShippingMethod::class);
        $this->initModule(CheckoutValidator::class);
        $this->initModule(OrderCreator::class);
        $this->initModule(ShippingItemDrawer::class);
        $this->initModule(Cart::class);
        $this->initModule(PluginInfo::class);

        add_action('plugins_loaded', function () {
            load_plugin_textdomain(WCUS_TRANSLATE_DOMAIN, false, 'wc-ukr-shipping/lang');
        });
    }

    public function singleton($abstract)
    {
        return $this->container->singleton($abstract);
    }

    public function make($abstract)
    {
        return $this->container->get($abstract);
    }

    private function initModule($module)
    {
        /* @var ModuleInterface $instance */
        $instance = new $module();
        $instance->init();

        if (wp_doing_ajax() && method_exists($instance, 'routes')) {
            $routes = $instance->routes();

            foreach ($routes as $route) {
                $this->router->addRoute($route);
            }
        }

        $this->modules[$module] = $instance;
    }

    private function instantiateContainer()
    {
        $this->container = new Container();

        $this->container->singleton('translate_service', TranslateService::class);
    }
}