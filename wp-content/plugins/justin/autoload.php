<?php

\spl_autoload_register(function ($class) {
  if (stripos($class, 'morkva\JustinShip') !== 0) {
    return;
  }

  $classFile = str_replace('\\', '/', substr($class, strlen('morkva\JustinShip') + 1) . '.php');
  include_once __DIR__ . '/' . $classFile;
});