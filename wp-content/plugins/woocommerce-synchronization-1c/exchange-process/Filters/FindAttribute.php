<?php
namespace Itgalaxy\Wc\Exchange1c\ExchangeProcess\Filters;

use Itgalaxy\Wc\Exchange1c\ExchangeProcess\Helpers\ProductAttributeHelper;
use Itgalaxy\Wc\Exchange1c\Includes\Bootstrap;

class FindAttribute
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
        \add_filter('itglx_wc1c_find_exists_product_attribute', [$this, 'findByName'], 10, 2);
    }

    /**
     * @param object|null $attribute
     * @param \SimpleXMLElement $element
     *
     * @return object|null
     */
    public function findByName($attribute, $element)
    {
        if ($attribute) {
            return $attribute;
        }

        if (empty($element->Ид) || empty($element->Наименование)) {
            return $attribute;
        }

        $settings = \get_option(Bootstrap::OPTIONS_KEY, []);

        if (empty($settings['find_exists_attribute_by_name'])) {
            return $attribute;
        }

        return ProductAttributeHelper::getByLabelWithOut1cGuid((string) $element->Наименование);
    }
}
