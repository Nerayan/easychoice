<?php

namespace kirillbdev\WCUkrShipping\Classes;

use kirillbdev\WCUkrShipping\Http\NovaPoshtaAjax;
use kirillbdev\WCUkrShipping\Services\CheckoutService;
use kirillbdev\WCUkrShipping\Services\TranslateService;

if ( ! defined('ABSPATH')) {
  exit;
}

final class WCUkrShipping
{
  private static $instance = null;

  private $activator;
  private $assetsLoader;
  private $optionsPage;
  private $ajax;

  private function __construct()
  {
    $this->instantiateContainer();
  }

  private function __clone() { }
  private function __wakeup() { }

  public static function instance()
  {
    if ( ! self::$instance) {
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
    $this->activator = new Activator();
    $this->assetsLoader = new AssetsLoader();
    $this->optionsPage = new OptionsPage();
    $this->ajax = new NovaPoshtaAjax();

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

  private function instantiateContainer()
  {
    $this->container = new Container();

    $this->container->singleton('translate_service', TranslateService::class);
    $this->container->singleton('checkout_service', CheckoutService::class);
  }
}