<?php

namespace kirillbdev\WCUkrShipping\Contracts;

if ( ! defined('ABSPATH')) {
    exit;
}

interface ModuleInterface
{
    /**
     * Boot function
     *
     * @return void
     */
    public function init();
}