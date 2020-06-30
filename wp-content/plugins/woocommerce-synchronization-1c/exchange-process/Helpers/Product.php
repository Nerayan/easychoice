<?php
namespace Itgalaxy\Wc\Exchange1c\ExchangeProcess\Helpers;

use Itgalaxy\Wc\Exchange1c\ExchangeProcess\DataResolvers\ProductAttributes;
use Itgalaxy\Wc\Exchange1c\ExchangeProcess\DataResolvers\ProductManufacturer;
use Itgalaxy\Wc\Exchange1c\ExchangeProcess\DataResolvers\ProductRequisites;
use Itgalaxy\Wc\Exchange1c\ExchangeProcess\DataResolvers\ProductUnit;
use Itgalaxy\Wc\Exchange1c\ExchangeProcess\DataResolvers\VariationImages;
use Itgalaxy\Wc\Exchange1c\Includes\Bootstrap;
use Itgalaxy\Wc\Exchange1c\Includes\Logger;

class Product
{
    public static function mainProductData($element, $productEntry, $name, $categoryIds, $productHash, $postAuthor)
    {
        $settings = get_option(Bootstrap::OPTIONS_KEY);

        $productMeta = [];
        $productMeta['_unit'] = ProductUnit::process($element);

        $productEntry['categoryID'] = [];

        if (
            empty($settings['skip_categories']) &&
            isset($element->Группы->Ид)
        ) {
            foreach ($element->Группы->Ид as $groupXmlId) {
                if (isset($categoryIds[(string) $groupXmlId])) {
                    $productEntry['categoryID'][] = $categoryIds[(string) $groupXmlId];
                }
            }

            $productEntry['categoryID'] = array_unique($productEntry['categoryID']);
        }

        $productMeta['_md5'] = $productHash;

        // resolve requisites
        $requisites = ProductRequisites::process($element);

        $productMeta['_all_product_requisites'] = $requisites['allRequisites'];

        // support the choice of where to get sku
        if (empty($settings['get_product_sku_from']) || $settings['get_product_sku_from'] === 'sku') {
            $productMeta['_sku'] = (string) $element->Артикул;
        } elseif ($settings['get_product_sku_from'] === 'requisite_code') {
            $productMeta['_sku'] = isset($requisites['allRequisites']['Код'])
                ? $requisites['allRequisites']['Код']
                : '';
        } elseif ($settings['get_product_sku_from'] === 'code') {
            $productMeta['_sku'] = isset($element->Код)
                ? (string) $element->Код
                : '';
        }

        if (!empty($requisites['fullName'])) {
            $productEntry['title'] = $requisites['fullName'];
        } else {
            $productEntry['title'] = wp_strip_all_tags(html_entity_decode(($name)));
        }

        // set weight
        if (
            empty($settings['skip_product_weight']) &&
            !empty($requisites['weight'])
        ) {
            $productMeta['_weight'] = $requisites['weight'];
        }

        if (empty($settings['skip_product_sizes'])) {
            // set length
            if (isset($requisites['length'])) {
                $productMeta['_length'] = $requisites['length'];
            }

            // set width
            if (isset($requisites['width'])) {
                $productMeta['_width'] = $requisites['width'];
            }

            // set height
            if (isset($requisites['height'])) {
                $productMeta['_height'] = $requisites['height'];
            }
        }

        if (empty($settings['skip_post_content_excerpt'])) {
            if (!empty($requisites['htmlPostContent'])) {
                $productEntry['post_content'] = $requisites['htmlPostContent'];
            }

            $description = html_entity_decode((string) $element->Описание);

            // if write the product description in excerpt
            if (!empty($settings['write_product_description_in_excerpt'])) {
                $productEntry['post_excerpt'] = $description;
            // else usual logic
            } elseif (!empty($description)) {
                if (empty($productEntry['post_content'])) {
                    $productEntry['post_content'] = $description;
                } else {
                    $productEntry['post_excerpt'] = $description;
                }
            }
        }

        $isNewProduct = true;

        // if exists product
        if (!empty($productEntry['ID'])) {
            $params = [
                'ID' => $productEntry['ID']
            ];

            $isNewProduct = false;

            if (isset($productEntry['post_content'])) {
                $params['post_content'] = $productEntry['post_content'];
            }

            if (isset($productEntry['post_excerpt'])) {
                $params['post_excerpt'] = $productEntry['post_excerpt'];
            }

            if (
                empty($settings['skip_post_title']) &&
                self::differenceTitle($productEntry['title'], $productEntry['ID'])
            ) {
                $params['post_title'] = $productEntry['title'];
            }

            wp_update_post($params);

            foreach ($productMeta as $key => $value) {
                update_post_meta($productEntry['ID'], $key, $value);
            }

            self::setCategory($productEntry['ID'], $productEntry['categoryID']);

            Logger::logChanges(
                '(product) Updated product, ID - ' . $productEntry['ID'],
                [get_post_meta($productEntry['ID'], '_id_1c', true)]
            );
        } else {
            $params = [
                'post_title' => $productEntry['title'],
                'post_author' => $postAuthor,
                'post_type' => 'product',
                'post_name' => self::uniquePostSlug($productEntry['title']),
                'post_status' => 'publish'
            ];

            if (isset($productEntry['post_content'])) {
                $params['post_content'] = $productEntry['post_content'];
            }

            if (isset($productEntry['post_excerpt'])) {
                $params['post_excerpt'] = $productEntry['post_excerpt'];
            }

            $productEntry['ID'] = wp_insert_post($params);

            $productMeta['_sale_price'] = '';
            $productMeta['_stock'] = 0;
            $productMeta['_manage_stock'] = get_option('woocommerce_manage_stock'); // yes or no

            // resolve xml id
            $xmlID = explode('#', (string) $element->Ид);
            $xmlID = $xmlID[0];

            $productMeta['_id_1c'] = $xmlID;

            foreach ($productMeta as $key => $value) {
                update_post_meta($productEntry['ID'], $key, $value);
            }

            Logger::logChanges(
                '(product) Added product, ID - ' . $productEntry['ID'],
                [$productMeta['_id_1c']]
            );

            self::setCategory($productEntry['ID'], $productEntry['categoryID']);
            self::hide($productEntry['ID'], true);
        }

        // is new or not disabled attribute data processing
        if ($isNewProduct || empty($settings['skip_post_attributes'])) {
            ProductAttributes::process($element, $productEntry['ID']);

            // resolve product manufacturer data to attribute
            ProductManufacturer::process($element, $productEntry['ID']);
        }

        // index/reindex relevanssi
        if (function_exists('relevanssi_insert_edit')) {
            relevanssi_insert_edit($productEntry['ID']);
        }

        return $productEntry;
    }

    public static function mainVariationData($element, $productEntry, $postAuthor)
    {
        global $wpdb;

        if (!isset($_SESSION['IMPORT_1C']['setTerms'])) {
            $_SESSION['IMPORT_1C']['setTerms'] = [];
        }

        if (!isset($_SESSION['IMPORT_1C']['productVariations'])) {
            $_SESSION['IMPORT_1C']['productVariations'] = [];
        }

        if (!get_post_meta($productEntry['post_parent'], '_is_set_variable', true)) {
            Term::setObjectTerms(
                $productEntry['post_parent'],
                'variable',
                'product_type'
            );

            update_post_meta($productEntry['post_parent'], '_manage_stock', 'no');
            update_post_meta($productEntry['post_parent'], '_is_set_variable', true);
        }

        // create variation
        if (!empty($productEntry['ID'])) {
            $wpdb->update(
                $wpdb->posts,
                [
                    'post_title' => (string) $element->Наименование,
                    'post_name' => sanitize_title((string) $element->Наименование),
                    'post_parent' => $productEntry['post_parent']
                ],
                ['ID' => $productEntry['ID']]
            );

            Logger::logChanges(
                '(variation) Updated, ID - '
                . $productEntry['ID']
                . ', parent ID - '
                . $productEntry['post_parent'],
                [(string) $element->Ид]
            );
        } else {
            $productEntry['ID'] =
                wp_insert_post(
                    [
                        'post_title' => (string) $element->Наименование,
                        'post_type' => 'product_variation',
                        'post_name' => sanitize_title((string) $element->Наименование),
                        'post_author' => $postAuthor,
                        'post_parent' => $productEntry['post_parent'],
                        // enabled or disabled by default based on the setting WooCommerce
                        'post_status' => get_option('woocommerce_manage_stock') === 'yes'
                            ? 'private'
                            : 'publish'
                    ]
                );

            update_post_meta($productEntry['ID'], '_id_1c', (string) $element->Ид);

            Logger::logChanges(
                '(variation) Added, ID - '
                . $productEntry['ID']
                . ', parent ID - '
                . $productEntry['post_parent'],
                [(string) $element->Ид]
            );
        }

        $_SESSION['IMPORT_1C']['productVariations'][$productEntry['post_parent']][] = $productEntry['ID'];

        if (
            isset($element->ЗначенияСвойств) &&
            isset($element->ЗначенияСвойств->ЗначенияСвойства)
        ) {
            self::resolveVariationOptionsWithId($element, $productEntry);
            // simple variant without ids
        } elseif (
            isset($element->ХарактеристикиТовара) &&
            isset($element->ХарактеристикиТовара->ХарактеристикаТовара)
        ) {
            self::resolveVariationOptionsWithoutId($element, $productEntry);
        }

        // variation image processing
        VariationImages::process($element, $productEntry['ID'], $postAuthor);

        return $productEntry;
    }

    public static function resolveVariationOptionsWithoutId($element, $productEntry)
    {
        $productOptions = get_option('all_product_options');

        foreach ($element->ХарактеристикиТовара->ХарактеристикаТовара as $property) {
            if (
                !empty($property->Значение) &&
                !empty($property->Наименование)
            ) {
                $label = (string) $property->Наименование;
                $taxByLabel = trim(strtolower($label));
                $taxByLabel = hash('crc32', $taxByLabel);

                $attributeName = 'simple_' . $taxByLabel;

                if (empty($productOptions[$attributeName])) {
                    continue;
                }

                $attribute = $productOptions[$attributeName];

                $optionTermID = false;

                $optionTermSlug =
                    md5(
                        $attribute['taxName']
                        . (string) $property->Значение
                    );
                $term = get_term_by('slug', $optionTermSlug, $attribute['taxName']);

                if ($term) {
                    $optionTermID = $term->term_id;
                } else {
                    $term =
                        wp_insert_term(
                            (string) $property->Значение,
                            $attribute['taxName'],
                            [
                                'slug' => $optionTermSlug,
                                'description' => '',
                                'parent' => 0
                            ]
                        );

                    if (!is_wp_error($term)) {
                        $optionTermID = $term['term_id'];

                        // default meta value by ordering
                        update_term_meta($optionTermID, 'order_' . $attribute['taxName'], 0);
                    }
                }

                if ($optionTermID) {
                    update_post_meta(
                        $productEntry['ID'],
                        'attribute_' . $attribute['taxName'],
                        get_term_by('id', $optionTermID, $attribute['taxName'])->slug
                    );

                    $_SESSION['IMPORT_1C']['setTerms'][$productEntry['post_parent']][$attribute['taxName']][] =
                        $optionTermID;
                }
            }
        }
    }

    public static function resolveVariationOptionsWithId($element, $productEntry)
    {
        $productOptions = get_option('all_product_options');

        foreach ($element->ЗначенияСвойств->ЗначенияСвойства as $property) {
            if (
                !empty($property->Значение) &&
                !empty($productOptions[(string) $property->Ид])
            ) {
                $attribute =
                    $productOptions[(string) $property->Ид];

                $optionTermID = false;

                if ($attribute['type'] === 'Справочник') {
                    $optionTermID =
                        $attribute['values'][(string) $property->Значение];
                } else {
                    $optionTermSlug =
                        md5(
                            $attribute['taxName']
                            . (string) $property->Значение
                        );
                    $term = get_term_by('slug', $optionTermSlug, $attribute['taxName']);

                    if ($term) {
                        $optionTermID = $term->term_id;
                    } else {
                        $term =
                            wp_insert_term(
                                (string) $property->Значение,
                                $attribute['taxName'],
                                [
                                    'slug' => $optionTermSlug,
                                    'description' => '',
                                    'parent' => 0
                                ]
                            );

                        if (!is_wp_error($term)) {
                            $optionTermID = $term['term_id'];

                            // default meta value by ordering
                            update_term_meta($optionTermID, 'order_' . $attribute['taxName'], 0);
                        }
                    }
                }

                if ($optionTermID) {
                    update_post_meta(
                        $productEntry['ID'],
                        'attribute_' . $attribute['taxName'],
                        get_term_by('id', $optionTermID, $attribute['taxName'])->slug
                    );

                    $_SESSION['IMPORT_1C']['setTerms'][$productEntry['post_parent']][$attribute['taxName']][] =
                        $optionTermID;
                }
            }
        }
    }

    public static function setCategory($productID, $categoryIds)
    {
        if (empty($categoryIds) || !is_array($categoryIds)) {
            Logger::logChanges(
                '(product) Empty product cat list, ID - ' . $productID,
                [get_post_meta($productID, '_id_1c', true)]
            );

            return;
        }

        if (empty($_SESSION['IMPORT_1C']['product_cat_list'])) {
            $_SESSION['IMPORT_1C']['product_cat_list'] = Term::getProductCatIDs(false);
        }

        $currentProductCats = wp_get_object_terms($productID, 'product_cat', ['fields' => 'ids']);

        //add only categories not from 1C to the main set
        if (!empty($currentProductCats)) {
            foreach ($currentProductCats as $termID) {
                if (!in_array($termID, $_SESSION['IMPORT_1C']['product_cat_list'])) {
                    $categoryIds[] = $termID;
                }
            }
        }

        Logger::logChanges(
            '(product) Set product cat list, ID - ' . $productID,
            [get_post_meta($productID, '_id_1c', true), array_map('intval', $categoryIds)]
        );

        Term::setObjectTerms(
            $productID,
            array_map('intval', $categoryIds),
            'product_cat'
        );
    }

    public static function show($productID, $withSetStatus = false)
    {
        if ($withSetStatus) {
            update_post_meta($productID, '_stock_status', 'instock');
            self::updateLookupTable((int) $productID, 'instock');
        }

        $setTerms = [];

        if (has_term('featured', 'product_visibility', $productID)) {
            $setTerms[] = 'featured';
        }

        Term::setObjectTerms(
            $productID,
            $setTerms,
            'product_visibility'
        );
    }

    public static function hide($productID, $withSetStatus = false)
    {
        if ($withSetStatus) {
            \update_post_meta($productID, '_stock_status', 'outofstock');
            self::updateLookupTable((int) $productID, 'outofstock');
        }

        $setTerms = [
            'outofstock'
        ];

        $settings = get_option(Bootstrap::OPTIONS_KEY);
        $products1cStockNull = isset($settings['products_stock_null_rule'])
            ? $settings['products_stock_null_rule']
            : '0';

        if ($products1cStockNull !== '2') {
            $setTerms[] = 'exclude-from-catalog';
            $setTerms[] = 'exclude-from-search';
        }

        if (has_term('featured', 'product_visibility', $productID)) {
            $setTerms[] = 'featured';
        }

        Term::setObjectTerms(
            $productID,
            $setTerms,
            'product_visibility'
        );
    }

    public static function getProductIdByMeta($value, $metaKey = '_id_1c')
    {
        global $wpdb;

        $product = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT `post_id` FROM `{$wpdb->postmeta}` WHERE `meta_value` = %s AND `meta_key` = %s",
                (string) $value,
                (string) $metaKey
            )
        );

        if ($product) {
            return $product;
        }

        return null;
    }

    public static function removeProductThumbnail($productID)
    {
        Logger::logChanges(
            '(image) Removed thumbnail for ID - ' . $productID,
            [get_post_meta($productID, '_id_1c', true)]
        );

        wp_delete_attachment(get_post_thumbnail_id($productID), true);
        delete_post_thumbnail($productID);

        $images = get_post_meta($productID, '_product_image_gallery', true);

        if (!empty($images)) {
            $images = explode(',', $images);

            foreach ($images as $image) {
                wp_delete_attachment($image, true);
            }

            update_post_meta($productID, '_product_image_gallery', '');

            Logger::logChanges(
                '(image) Removed gallery for ID - ' . $productID,
                [get_post_meta($productID, '_id_1c', true)]
            );
        }
    }

    public static function removeVariations($productId)
    {
        $variationIds = wp_parse_id_list(
            get_posts(
                [
                    'post_parent' => $productId,
                    'post_type' => 'product_variation',
                    'fields' => 'ids',
                    'post_status' => ['any', 'trash', 'auto-draft'],
                    'numberposts' => -1
                ]
            )
        );

        if (!empty($variationIds)) {
            foreach ($variationIds as $variationId) {
                wp_delete_post($variationId, true);
            }
        }

        delete_transient('wc_product_children_' . $productId);
    }

    public static function removeProduct($productId)
    {
        $postType = get_post_type($productId);

        if ($postType !== 'product') {
            return;
        }

        // https://developer.wordpress.org/reference/functions/has_post_thumbnail/
        if (has_post_thumbnail($productId)) {
            self::removeProductThumbnail($productId);
        }

        $variable = get_post_meta($productId, '_is_set_variable', true);

        if ($variable) {
            self::removeVariations($productId);
        }

        Logger::logChanges(
            '(product) Removed product, ID - ' . $productId,
            [get_post_meta($productId, '_id_1c', true)]
        );

        // https://developer.wordpress.org/reference/functions/wp_delete_post/
        wp_delete_post($productId, true);
    }

    public static function differenceTitle($name, $productId)
    {
        global $wpdb;

        $productTitle = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT `post_title` FROM `{$wpdb->posts}` WHERE `ID` = %d",
                $productId
            )
        );

        if (
            $productTitle &&
            $name !== $productTitle
        ) {
            return true;
        }

        return false;
    }

    public static function uniquePostSlug($slug, $post_ID = null)
    {
        global $wpdb;

        $slug = sanitize_title($slug);

        if ($post_ID) {
            $check_sql = "SELECT `post_name` FROM `{$wpdb->posts}` WHERE `post_name` = '%s' AND `post_type` = 'product' AND `ID` != '%d' LIMIT 1";
            $post_name_check = $wpdb->get_var($wpdb->prepare($check_sql, $slug, $post_ID));

            if ($post_name_check) {
                $suffix = 2;
                do {
                    $alt_post_name = _truncate_post_slug($slug, 200 - (strlen($suffix) + 1)) . "-$suffix";
                    $post_name_check = $wpdb->get_var($wpdb->prepare($check_sql, $alt_post_name, $post_ID));
                    $suffix++;
                } while ($post_name_check);
                $slug = $alt_post_name;
            }
        } else {
            $check_sql = "SELECT `post_name` FROM `{$wpdb->posts}` WHERE `post_name` = '%s' AND `post_type` = 'product' LIMIT 1";
            $post_name_check = $wpdb->get_var($wpdb->prepare($check_sql, $slug));

            if ($post_name_check) {
                $suffix = 2;
                do {
                    $alt_post_name = _truncate_post_slug($slug, 200 - (strlen($suffix) + 1)) . "-$suffix";
                    $post_name_check = $wpdb->get_var($wpdb->prepare($check_sql, $alt_post_name));
                    $suffix++;
                } while ($post_name_check);
                $slug = $alt_post_name;
            }
        }

        return $slug;
    }

    private static function updateLookupTable($id, $stockStatus)
    {
        global $wpdb;

        if (!function_exists('wc_update_product_lookup_tables_column')) {
            return;
        }

        $wpdb->replace(
            $wpdb->wc_product_meta_lookup,
            [
                'product_id' => $id,
                'stock_status' => $stockStatus
            ]
        );
    }
}
