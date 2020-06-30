<?php
namespace Itgalaxy\Wc\Exchange1c\Admin;

class ProductAttributesPage1cIdInfo
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
        if (
            is_admin() &&
            !empty($_GET['page']) &&
            $_GET['page'] == 'product_attributes'
        ) {
            // https://docs.woocommerce.com/wc-apidocs/hook-docs.html
            add_filter('woocommerce_attribute_taxonomies', [$this, 'add1cValueInfo']);
        }
    }

    public function add1cValueInfo($taxonomies)
    {
        foreach ($taxonomies as $taxonomy) {
            if (isset($taxonomy->id_1c) && $taxonomy->id_1c) {
                $taxonomy->attribute_label .= ' | '
                    . $taxonomy->id_1c;
            }
        }

        return $taxonomies;
    }
}
