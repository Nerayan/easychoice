<?php

\spl_autoload_register(function ($class) {
  if (stripos($class, 'kirillbdev\WCUkrShipping') !== 0) {
    return;
  }

  $classFile = str_replace('\\', '/', substr($class, strlen('kirillbdev\WCUkrShipping') + 1) . '.php');
  include_once __DIR__ . '/' . $classFile;
});