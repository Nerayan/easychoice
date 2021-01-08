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

            $params = (array) apply_filters('itglx_wc1c_update_post_product_params', $params, $element);

            wp_update_post($params);

            foreach ($productMeta as $key => $value) {
                self::saveMetaValue($productEntry['ID'], $key, $value);
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
                'post_status' => 'publish'
            ];

            if (isset($productEntry['post_content'])) {
                $params['post_content'] = $productEntry['post_content'];
            }

            if (isset($productEntry['post_excerpt'])) {
                $params['post_excerpt'] = $productEntry['post_excerpt'];
            }

            $params = (array) apply_filters('itglx_wc1c_insert_post_new_product_params', $params, $element);

            // https://developer.wordpress.org/reference/functions/wp_insert_post/
            $productEntry['ID'] = wp_insert_post($params);

            if (is_wp_error($productEntry['ID'])) {
                Logger::logChanges('(product) Error adding product', [(string) $element->Ид, $productEntry['ID']]);

                return [];
            }

            $productMeta['_sale_price'] = '';
            $productMeta['_stock'] = 0;
            $productMeta['_manage_stock'] = get_option('woocommerce_manage_stock'); // yes or no

            // resolve xml id
            $xmlID = explode('#', (string) $element->Ид);
            $xmlID = $xmlID[0];

            $productMeta['_id_1c'] = $xmlID;

            foreach ($productMeta as $key => $value) {
                self::saveMetaValue($productEntry['ID'], $key, $value);
            }

            Logger::logChanges(
                '(product) Added product, ID - ' . $productEntry['ID'],
                [$productMeta['_id_1c']]
            );

            self::setCategory($productEntry['ID'], $productEntry['categoryID']);
            self::hide($productEntry['ID'], true);
        }


        /*
         * Example xml structure
         * position - Товар -> Метки
         *
        <Метки>
            <Ид>f108c911-3bca-11eb-841f-ade4b337caca</Ид>
            <Ид>f108c912-3bca-11eb-841f-ade4b337caca</Ид>
            <Ид>f108c913-3bca-11eb-841f-ade4b337caca</Ид>
        </Метки>
        */
        if (isset($element->Метки->Ид) && !empty($_SESSION['IMPORT_1C']['productTags'])) {
            $tagIds = [];

            foreach ($element->Метки->Ид as $tagXmlId) {
                if (isset($_SESSION['IMPORT_1C']['productTags'][(string) $tagXmlId])) {
                    $tagIds[] = $_SESSION['IMPORT_1C']['productTags'][(string) $tagXmlId];
                }
            }

            \wp_set_object_terms($productEntry['ID'], array_map('intval', $tagIds), 'product_tag');
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

    public static function mainVariationData($element, $productEntry, $postAuthor, $onlyCharacteristics = false)
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

            self::saveMetaValue($productEntry['post_parent'], '_manage_stock', 'no');
            self::saveMetaValue($productEntry['post_parent'], '_is_set_variable', true);
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
            // https://developer.wordpress.org/reference/functions/wp_insert_post/
            $productEntry['ID'] = wp_insert_post(
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

            self::saveMetaValue($productEntry['ID'], '_id_1c', (string) $element->Ид, $productEntry['post_parent']);

            Logger::logChanges(
                '(variation) Added, ID - '
                . $productEntry['ID']
                . ', parent ID - '
                . $productEntry['post_parent'],
                [(string) $element->Ид]
            );
        }

        // processing and recording the sku for variable offers.
        if (isset($element->Артикул)) {
            $parentSku = get_post_meta($productEntry['post_parent'], '_sku', true);
            $offerSku = trim((string) $element->Артикул);

            if ($offerSku !== $parentSku) {
                self::saveMetaValue($productEntry['ID'], '_sku', $offerSku, $productEntry['post_parent']);
            }
        }

        $_SESSION['IMPORT_1C']['productVariations'][$productEntry['post_parent']][] = $productEntry['ID'];

        if (
            !$onlyCharacteristics &&
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
                empty($property->Значение) ||
                empty($property->Наименование)
            ) {
                continue;
            }

            $label = (string) $property->Наименование;
            $taxByLabel = trim(strtolower($label));
            $taxByLabel = hash('crc32', $taxByLabel);

            $attributeName = 'simple_' . $taxByLabel;

            if (empty($productOptions[$attributeName])) {
                continue;
            }

            $attribute = $productOptions[$attributeName];
            $uniqId1c = md5($attribute['createdTaxName'] . (string) $property->Значение);
            $optionTermID = Term::getTermIdByMeta($uniqId1c);

            if (!$optionTermID) {
                $optionTermID = get_term_by('name', (string) $property->Значение, $attribute['taxName']);

                if ($optionTermID) {
                    $optionTermID = $optionTermID->term_id;

                    Term::update1cId($optionTermID, $uniqId1c);
                }
            }

            if (!$optionTermID) {
                $term = ProductAttributeHelper::insertValue(
                    (string) $property->Значение,
                    $attribute['taxName'],
                    $uniqId1c
                );

                $optionTermID = $term['term_id'];

                // default meta value by ordering
                update_term_meta($optionTermID, 'order_' . $attribute['taxName'], 0);

                Term::update1cId($optionTermID, $uniqId1c);
            }

            if ($optionTermID) {
                self::saveMetaValue(
                    $productEntry['ID'],
                    'attribute_' . $attribute['taxName'],
                    get_term_by('id', $optionTermID, $attribute['taxName'])->slug,
                    $productEntry['post_parent']
                );

                $_SESSION['IMPORT_1C']['setTerms'][$productEntry['post_parent']][$attribute['taxName']][] =
                    $optionTermID;
            }
        }
    }

    public static function resolveVariationOptionsWithId($element, $productEntry)
    {
        $productOptions = get_option('all_product_options');
        $ignoreAttributeProcessing = apply_filters('itglx_wc1c_attribute_ignore_guid_array', []);

        foreach ($element->ЗначенияСвойств->ЗначенияСвойства as $property) {
            if (
                empty($property->Значение) ||
                empty($productOptions[(string) $property->Ид])
            ) {
                continue;
            }

            if (in_array((string) $property->Ид, $ignoreAttributeProcessing, true)) {
                continue;
            }

            $attribute = $productOptions[(string) $property->Ид];

            if ($attribute['type'] === 'Справочник') {
                $optionTermID = isset($attribute['values'][(string) $property->Значение])
                    ? $attribute['values'][(string) $property->Значение]
                    : false;
            } else {
                $uniqId1c = md5($attribute['createdTaxName'] . (string) $property->Значение);
                $optionTermID = Term::getTermIdByMeta($uniqId1c);

                if (!$optionTermID) {
                    $optionTermID = get_term_by('name', (string) $property->Значение, $attribute['taxName']);

                    if ($optionTermID) {
                        $optionTermID = $optionTermID->term_id;

                        Term::update1cId($optionTermID, $uniqId1c);
                    }
                }

                if (!$optionTermID) {
                    $term = ProductAttributeHelper::insertValue(
                        (string) $property->Значение,
                        $attribute['taxName'],
                        $uniqId1c
                    );

                    $optionTermID = $term['term_id'];

                    // default meta value by ordering
                    update_term_meta($optionTermID, 'order_' . $attribute['taxName'], 0);

                    Term::update1cId($optionTermID, $uniqId1c);
                }
            }

            if ($optionTermID) {
                self::saveMetaValue(
                    $productEntry['ID'],
                    'attribute_' . $attribute['taxName'],
                    get_term_by('id', $optionTermID, $attribute['taxName'])->slug,
                    $productEntry['post_parent']
                );

                $_SESSION['IMPORT_1C']['setTerms'][$productEntry['post_parent']][$attribute['taxName']][] =
                    $optionTermID;
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

        // https://developer.wordpress.org/reference/functions/wp_get_object_terms/
        $currentProductCats = wp_get_object_terms($productID, 'product_cat', ['fields' => 'ids']);

        //add only categories not from 1C to the main set
        if (!empty($currentProductCats)) {
            foreach ($currentProductCats as $termID) {
                if (!in_array($termID, $_SESSION['IMPORT_1C']['product_cat_list'])) {
                    $categoryIds[] = $termID;
                }
            }
        }

        $categoryIds = array_map('intval', $categoryIds);

        Logger::logChanges(
            '(product) Set product cat list, ID - ' . $productID,
            [get_post_meta($productID, '_id_1c', true), $categoryIds]
        );

        Term::setObjectTerms($productID, $categoryIds, 'product_cat');
    }

    public static function show($productID, $withSetStatus = false, $statusValue = 'instock')
    {
        if ($withSetStatus) {
            update_post_meta($productID, '_stock_status', $statusValue);
            self::updateLookupTable((int) $productID, $statusValue);
        }

        $setTerms = [];

        if (has_term('featured', 'product_visibility', $productID)) {
            $setTerms[] = 'featured';
        }

        Term::setObjectTerms($productID, $setTerms, 'product_visibility');
    }

    public static function hide($productID, $withSetStatus = false)
    {
        if (apply_filters('itglx_wc1c_stop_hide_product_method', false, $productID)) {
            return;
        }

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

    public static function saveMetaValue($productID, $metaKey, $metaValue, $parentProductID = null)
    {
        if (!$parentProductID) {
            $filter = 'itglx_wc1c_product_meta_' . $metaKey . '_value';
        } else {
            $filter = 'itglx_wc1c_variation_meta_' . $metaKey . '_value';
        }

        update_post_meta(
            $productID,
            $metaKey,
            apply_filters($filter, $metaValue, $productID, $parentProductID)
        );
    }

    public static function getProductIdByMeta($value, $metaKey = '_id_1c', $isVariation = false)
    {
        global $wpdb;

        $product = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT `meta`.`post_id` as `post_id`, `posts`.`post_type` as `post_type` FROM `{$wpdb->postmeta}` as `meta`
                INNER JOIN `{$wpdb->posts}` as `posts` ON (`meta`.`post_id` = `posts`.`ID`)
                WHERE `meta`.`meta_value` = %s AND `meta`.`meta_key` = %s",
                (string) $value,
                (string) $metaKey
            )
        );

        if (!isset($product->post_type)) {
            return null;
        }

        if ($isVariation) {
            return $product->post_type === 'product_variation' ? $product->post_id : null;
        }

        return $product->post_type === 'product' ? $product->post_id : null;
    }

    public static function removeProductImages($productID)
    {
        // https://developer.wordpress.org/reference/functions/get_post_thumbnail_id/
        $thumbnailID = get_post_thumbnail_id($productID);

        if ($thumbnailID) {
            // https://developer.wordpress.org/reference/functions/wp_delete_attachment/
            wp_delete_attachment($thumbnailID, true);

            // https://developer.wordpress.org/reference/functions/delete_post_thumbnail/
            delete_post_thumbnail($productID);

            Logger::logChanges(
                '(image) Removed thumbnail ID - ' . $thumbnailID . ', for product ID - ' . $productID,
                [get_post_meta($productID, '_id_1c', true)]
            );
        }

        $images = get_post_meta($productID, '_product_image_gallery', true);

        // if product gallery is empty
        if (empty($images)) {
            return;
        }

        $images = explode(',', $images);

        foreach ($images as $image) {
            // https://developer.wordpress.org/reference/functions/wp_delete_attachment/
            wp_delete_attachment($image, true);

            Logger::logChanges(
                '(image) Removed gallery image ID - ' . $image . ', for product ID - ' . $productID,
                [get_post_meta($productID, '_id_1c', true)]
            );
        }

        self::saveMetaValue($productID, '_product_image_gallery', '');

        Logger::logChanges(
            '(image) Removed gallery for ID - ' . $productID,
            [get_post_meta($productID, '_id_1c', true)]
        );
    }

    public static function removeVariations($productId)
    {
        // https://developer.wordpress.org/reference/functions/wp_parse_id_list/
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
                self::removeVariation($variationId, $productId);
            }
        }

        Logger::logChanges(
            '(product) Removed product variations, ID - ' . $productId,
            [get_post_meta($productId, '_id_1c', true)]
        );
    }

    public static function removeVariation($variationId, $productId = 0)
    {
        // https://developer.wordpress.org/reference/functions/has_post_thumbnail/
        if (has_post_thumbnail($variationId)) {
            // https://developer.wordpress.org/reference/functions/wp_delete_attachment/
            // https://developer.wordpress.org/reference/functions/get_post_thumbnail_id/
            wp_delete_attachment(get_post_thumbnail_id($variationId), true);

            // https://developer.wordpress.org/reference/functions/delete_post_thumbnail/
            delete_post_thumbnail($variationId);

            Logger::logChanges(
                '(image) Removed thumbnail for variation ID - ' . $variationId,
                [get_post_meta($variationId, '_id_1c', true)]
            );
        }

        // https://developer.wordpress.org/reference/functions/wp_delete_post/
        wp_delete_post($variationId, true);

        if ($productId) {
            delete_transient('wc_product_children_' . $productId);
        }
    }

    public static function removeProduct($productId)
    {
        if (get_post_type($productId) !== 'product') {
            return;
        }

        // https://developer.wordpress.org/reference/functions/has_post_thumbnail/
        if (has_post_thumbnail($productId)) {
            self::removeProductImages($productId);
        }

        if (get_post_meta($productId, '_is_set_variable', true)) {
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
