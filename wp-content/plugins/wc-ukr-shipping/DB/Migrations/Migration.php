<?php

namespace kirillbdev\WCUkrShipping\DB\Migrations;

if ( ! defined('ABSPATH')) {
    exit;
}

abstract class Migration
{
    /**
     * @return string
     */
    abstract public function name();

    /**
     * @param mixed $db
     *
     * @return void
     */
    abstract public function up($db);
}