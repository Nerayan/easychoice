<?php
namespace Itgalaxy\Wc\Exchange1c\ExchangeProcess\Filters;

use Itgalaxy\Wc\Exchange1c\ExchangeProcess\Helpers\Product;
use Itgalaxy\Wc\Exchange1c\Includes\Bootstrap;

class FindProductId
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
        add_filter('itglx_wc1c_find_product_id', [$this, 'findBySku'], 10, 2);
    }

    public function findBySku($productId, $element)
    {
        if ((int) $productId) {
            return $productId;
        }

        if (empty($element->Артикул)) {
            return $productId;
        }

        $settings = get_option(Bootstrap::OPTIONS_KEY);

        if (empty($settings['find_product_by_sku'])) {
            return $productId;
        }

        $productId = Product::getProductIdByMeta(
            (string) $element->Артикул,
            '_sku'
        );

        return $productId;
    }
}
