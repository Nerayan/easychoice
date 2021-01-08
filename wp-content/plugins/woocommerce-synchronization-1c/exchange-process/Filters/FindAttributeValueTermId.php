<?php
namespace Itgalaxy\Wc\Exchange1c\ExchangeProcess\Filters;

use Itgalaxy\Wc\Exchange1c\Includes\Bootstrap;

class FindAttributeValueTermId
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
        \add_filter('itglx_wc1c_find_exists_product_attribute_value_term_id', [$this, 'findByName'], 10, 3);
    }

    public function findByName($termID, $element, $taxonomy)
    {
        if ((int) $termID) {
            return $termID;
        }

        if (empty($element->Значение)) {
            return $termID;
        }

        $settings = \get_option(Bootstrap::OPTIONS_KEY);

        if (empty($settings['find_exists_attribute_value_by_name'])) {
            return $termID;
        }

        $terms = \get_terms(
            [
                'taxonomy' => $taxonomy,
                'parent' => 0,
                'name' => trim(\wp_strip_all_tags((string) $element->Значение)),
                'hide_empty' => false,
                'orderby' => 'name',
                'fields' => 'ids',
                // find only terms without guid
                'meta_query' => [
                    [
                        'key' => '_id_1c',
                        'compare' => 'NOT EXISTS'
                    ]
                ]
            ]
        );

        if (!$terms) {
            return $termID;
        }

        return $terms[0];
    }
}
