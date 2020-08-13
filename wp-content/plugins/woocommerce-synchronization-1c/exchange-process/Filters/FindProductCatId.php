<?php
namespace Itgalaxy\Wc\Exchange1c\ExchangeProcess\Filters;

use Itgalaxy\Wc\Exchange1c\Includes\Bootstrap;

class FindProductCatId
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
        \add_filter('itglx_wc1c_find_product_cat_term_id', [$this, 'findByName'], 10, 4);
    }

    public function findByName($termID, $element, $taxonomy, $parentID)
    {
        if ((int) $termID) {
            return $termID;
        }

        if (empty($element->Наименование)) {
            return $termID;
        }

        $settings = \get_option(Bootstrap::OPTIONS_KEY);

        if (empty($settings['find_product_cat_term_by_name'])) {
            return $termID;
        }

        $terms = \get_terms(
            [
                'taxonomy' => $taxonomy,
                'parent' => $parentID,
                'name' => trim(\wp_strip_all_tags((string) $element->Наименование)),
                'hide_empty' => false,
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

        // ignore if results more one
        if (count($terms) > 1) {
            return $termID;
        }

        return $terms[0];
    }
}
