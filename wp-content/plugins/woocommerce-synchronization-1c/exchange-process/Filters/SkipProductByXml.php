<?php
namespace Itgalaxy\Wc\Exchange1c\ExchangeProcess\Filters;

use Itgalaxy\Wc\Exchange1c\Includes\Bootstrap;

class SkipProductByXml
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
        add_filter('itglx_wc1c_skip_product_by_xml', [$this, 'process'], 10, 2);
    }

    public function process($skip, $element)
    {
        if ($skip) {
            return $skip;
        }

        if (
            !isset($element->Ид) ||
            !isset($element->Наименование)
        ) {
            return true;
        }

        $settings = get_option(Bootstrap::OPTIONS_KEY);

        // skip products without image
        if (
            !empty($settings['skip_products_without_photo']) &&
            (!isset($element->Картинка) || empty((string) $element->Картинка))
        ) {
            return true;
        }

        $name = trim(wp_strip_all_tags((string) $element->Наименование));

        if (empty($name)) {
            return true;
        }

        return $skip;
    }
}
