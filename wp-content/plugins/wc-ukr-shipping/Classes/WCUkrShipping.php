<?php

namespace kirillbdev\WCUkrShipping\Classes;

use kirillbdev\WCUkrShipping\Http\NovaPoshtaAjax;
use kirillbdev\WCUkrShipping\Http\NovaPoshtaRest;

if ( ! defined('ABSPATH')) {
  exit;
}

final class WCUkrShipping
{
  private static $instance = null;

  private $activator;
  private $assetsLoader;
  private $optionsPage;
  private $rest;
  private $ajax;
  private $reporter;

  private function __construct()
  {
    $this->activator = new Activator();
    $this->assetsLoader = new AssetsLoader();
    $this->optionsPage = new OptionsPage();
    $this->rest = new NovaPoshtaRest();

    add_action('admin_init', function () {
      if ($this->maybeRESTDisabled()) {
        $this->ajax = new NovaPoshtaAjax();
      }
    });

    $this->reporter = new Reporter();
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

  private function maybeRESTDisabled()
  {
    if ( ! is_plugin_active('wc-ukr-shipping/wc-ukr-shipping.php')) {
      return false;
    }

    if (get_transient('wc_ukr_shipping_request_handler') === false) {
      set_transient('wc_ukr_shipping_request_handler', 'ajax', 3600 * 24);

      wp_remote_get(home_url('wp-json/wc-ukr-shipping/v1/test'), [
        'timeout' => 30,
        'sslverify' => false
      ]);
    }

    if (get_transient('wc_ukr_shipping_request_handler') === 'ajax') {
      return true;
    }

    return false;
  }
}