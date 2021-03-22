<?php
namespace Itgalaxy\Wc\Exchange1c\ExchangeProcess\Helpers;

use Itgalaxy\Wc\Exchange1c\Includes\Bootstrap;
use Itgalaxy\Wc\Exchange1c\Includes\Logger;

class Term
{
    public static function getTermIdByMeta($value, $metaKey = '_id_1c')
    {
        global $wpdb;

        $term = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT `meta`.`term_id` FROM `{$wpdb->termmeta}` as `meta`
                INNER JOIN `{$wpdb->terms}` as `terms` ON (`meta`.`term_id` = `terms`.`term_id`)
                WHERE `meta`.`meta_value` = %s AND `meta`.`meta_key` = %s",
                (string) $value,
                (string) $metaKey
            )
        );

        if ($term) {
            return $term;
        }

        return null;
    }

    public static function getProductCatIDs($withKey1cId = true)
    {
        global $wpdb;

        $categoryIds = [];

        $categories = $wpdb->get_results(
            "SELECT `meta_value`, `term_id` FROM `{$wpdb->termmeta}` WHERE `meta_key` = '_id_1c'  GROUP BY `term_id`"
        );

        if ($withKey1cId) {
            foreach ($categories as $category) {
                $categoryIds[$category->meta_value] = $category->term_id;
            }
        } else {
            foreach ($categories as $category) {
                $categoryIds[] = $category->term_id;
            }
        }

        unset($categories);

        return $categoryIds;
    }

    public static function update1cId($termID, $ID1c)
    {
        \update_term_meta($termID, '_id_1c', $ID1c);
    }

    public static function updateProductCat($categoryEntry)
    {
        $settings = get_option(Bootstrap::OPTIONS_KEY);

        $params = [
            'parent' => ($categoryEntry['parent'] == '' ? 0 : $categoryEntry['parent'])
        ];

        // category name update not disabled and category has different name
        if (
            empty($settings['skip_product_cat_name']) &&
            self::differenceName($categoryEntry['name'], $categoryEntry['term_id'])
        ) {
            $params['name'] = $categoryEntry['name'];
        }

        \wp_update_term($categoryEntry['term_id'], 'product_cat', $params);
    }

    public static function insertProductCat($categoryEntry)
    {
        $result = \wp_insert_term(
            $categoryEntry['name'],
            'product_cat',
            [
                'slug' => \wp_unique_term_slug(
                    \sanitize_title($categoryEntry['name']),
                    (object) [
                        'taxonomy' => 'product_cat',
                        'parent' => 0
                    ]
                ),
                'parent' => ($categoryEntry['parent'] == '' ? 0 : $categoryEntry['parent'])
            ]
        );

        if (is_wp_error($result)) {
            Logger::logChanges(
                '(product_cat) Add error - ' . $result->get_error_message(),
                $categoryEntry
            );

            return false;
        }

        // default meta value by ordering
        update_term_meta($result['term_id'], 'order', 0);

        return $result['term_id'];
    }

    public static function getObjectTerms($objectIDs, $taxonomies, $args = [])
    {
        // https://developer.wordpress.org/reference/functions/wp_get_object_terms/
        return \wp_get_object_terms($objectIDs, $taxonomies, $args);
    }

    public static function setObjectTerms($objectID, $terms, $taxonomy, $append = false)
    {
        // https://developer.wordpress.org/reference/functions/wp_set_object_terms/
        return \wp_set_object_terms($objectID, $terms, $taxonomy, $append);
    }

    public static function differenceName($name, $termId)
    {
        global $wpdb;

        $termName = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT `name` FROM `{$wpdb->terms}` WHERE `term_id` = %d",
                $termId
            )
        );

        if ($termName && $name !== $termName) {
            return true;
        }

        return false;
    }
}
