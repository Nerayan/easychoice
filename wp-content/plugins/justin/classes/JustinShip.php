<?php

namespace morkva\JustinShip\classes;

use morkva\JustinShip\Http\JustinAjax;

if ( ! defined('ABSPATH')) {
  exit;
}

final class JustinShip
{
  private static $instance = null;

  private $activator;
  private $assetsLoader;
  private $optionsPage;
  private $ajax;

  private function __construct()
  {
    $this->activator = new Activator();
    $this->assetsLoader = new AssetsLoader();
    $this->ajax = new JustinAjax();
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
}
