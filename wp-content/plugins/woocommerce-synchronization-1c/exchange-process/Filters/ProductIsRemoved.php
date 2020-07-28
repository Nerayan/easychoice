<?php
namespace Itgalaxy\Wc\Exchange1c\ExchangeProcess\Filters;

class ProductIsRemoved
{
    private static $instance = false;

    public static function getInstance()
    {
        if (!self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    private function __construct()
    {
        add_filter('itglx_wc1c_product_is_removed', [$this, 'process'], 10, 2);
    }

    public function process($isRemoved, $element)
    {
        if (
            (string) $element->ПометкаУдаления &&
            (string) $element->ПометкаУдаления === 'true'
        ) {
            return true;
        }

        return $isRemoved;
    }
}
