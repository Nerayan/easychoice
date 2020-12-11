<?php
namespace Itgalaxy\Wc\Exchange1c\ExchangeProcess\Helpers;

use Itgalaxy\Wc\Exchange1c\Includes\Bootstrap;

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
            //$params['slug'] = self::uniqueTermSlug($categoryEntry['name'], \get_term($categoryEntry['term_id'], 'product_cat'));
        }

        \wp_update_term($categoryEntry['term_id'], 'product_cat', $params);
    }

    public static function insertProductCat($categoryEntry)
    {
        $result = \wp_insert_term(
            $categoryEntry['name'],
            'product_cat',
            [
                'slug' => self::uniqueTermSlug($categoryEntry['name']),
                'parent' => ($categoryEntry['parent'] == '' ? 0 : $categoryEntry['parent'])
            ]
        );

        if (is_wp_error($result)) {
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

    public static function uniqueTermSlug($slug, $term = null)
    {
        global $wpdb;

        $slug = \sanitize_title($slug);

        if (!\term_exists($slug)) {
            return $slug;
        }

        if ($term) {
            if (\is_taxonomy_hierarchical($term->taxonomy) && !empty($term->parent)) {
                $the_parent = $term->parent;

                while (!empty($the_parent)) {
                    $parent_term = \get_term($the_parent, $term->taxonomy);

                    if (\is_wp_error($parent_term) || empty($parent_term)) {
                        break;
                    }

                    $slug .= '-' . $parent_term->slug;

                    if (!\term_exists($slug)) {
                        return $slug;
                    }

                    if (empty($parent_term->parent)) {
                        break;
                    }

                    $the_parent = $parent_term->parent;
                }
            }

            // If we didn't get a unique slug, try appending a number to make it unique.
            if (!empty($term->term_id)) {
                $query = $wpdb->prepare("SELECT `slug` FROM $wpdb->terms WHERE `slug` = '%s' AND `term_id` != %d", $slug, $term->term_id);
            } else {
                $query = $wpdb->prepare("SELECT `slug` FROM $wpdb->terms WHERE `slug` = '%s'", $slug);
            }

            if ($wpdb->get_var($query)) {
                $num = 2;

                do {
                    $alt_slug = $slug . "-$num";
                    $num++;
                    $slug_check = $wpdb->get_var($wpdb->prepare("SELECT `slug` FROM `{$wpdb->terms}` WHERE `slug` = '%s'", $alt_slug));
                } while ($slug_check);

                $slug = $alt_slug;
            }

        } else {
            $check_sql = "SELECT `slug` FROM `{$wpdb->terms}` WHERE `slug` = '%s' LIMIT 1";
            $slug_check = $wpdb->get_var($wpdb->prepare($check_sql, $slug));

            if ($slug_check) {
                $num = 2;

                do {
                    $alt_slug = $slug . "-$num";
                    $num++;
                    $slug_check = $wpdb->get_var($wpdb->prepare("SELECT `slug` FROM `{$wpdb->terms}` WHERE `slug` = '%s'", $alt_slug));
                } while ($slug_check);

                $slug = $alt_slug;
            }
        }

        return $slug;
    }
}
