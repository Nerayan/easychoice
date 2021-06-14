<?php
namespace Itgalaxy\Wc\Exchange1c\ExchangeProcess\Helpers;

use Itgalaxy\Wc\Exchange1c\ExchangeProcess\DataResolvers\ProductAttributes;
use Itgalaxy\Wc\Exchange1c\ExchangeProcess\DataResolvers\ProductManufacturer;
use Itgalaxy\Wc\Exchange1c\ExchangeProcess\DataResolvers\ProductRequisites;
use Itgalaxy\Wc\Exchange1c\ExchangeProcess\DataResolvers\ProductUnit;
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
                '(product) Updated product, ID - ' . $productEntry['ID'] . ', status - ' . self::getStatus($productEntry['ID']),
                [get_post_meta($productEntry['ID'], '_id_1c', true)]
            );
        } else {
            Logger::logChanges('(product) insert start', [(string) $element->Ид]);

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
                '(product) added product, ID - ' . $productEntry['ID'],
                [$productMeta['_id_1c']]
            );

            self::setCategory($productEntry['ID'], $productEntry['categoryID']);
            self::hide($productEntry['ID'], true);

            Logger::logChanges('(product) insert end', [$productMeta['_id_1c']]);
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
            $oldStatus = get_post_meta($productID, '_stock_status', true);

            if ($oldStatus !== $statusValue) {
                \update_post_meta($productID, '_stock_status', $statusValue);
                self::triggerWooCommerceChangeStockStatusHook($productID, $oldStatus, $statusValue);
            }
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
            $oldStatus = get_post_meta($productID, '_stock_status', true);

            if ($oldStatus !== 'outofstock') {
                \update_post_meta($productID, '_stock_status', 'outofstock');
                self::triggerWooCommerceChangeStockStatusHook($productID, $oldStatus, 'outofstock');
            }
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
        if ($parentProductID) {
            ProductVariation::saveMetaValue($productID, $metaKey, $metaValue, $parentProductID);

            return;
        }

        update_post_meta(
            $productID,
            $metaKey,
            apply_filters('itglx_wc1c_product_meta_' . $metaKey . '_value', $metaValue, $productID, $parentProductID)
        );
    }

    public static function getProductIdByMeta($value, $metaKey = '_id_1c')
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
                ProductVariation::remove($variationId, $productId);
            }
        }

        Logger::logChanges(
            '(product) Removed product variations, ID - ' . $productId,
            [get_post_meta($productId, '_id_1c', true)]
        );
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

    /**
     * Calling standard hooks when changing the stock status of a product / variation.
     *
     * @param int $productID Product or Variation ID.
     * @param string $oldStatus Current status of the stock.
     * @param string $newStatus New status of the stock.
     *
     * @return void
     */
    private static function triggerWooCommerceChangeStockStatusHook($productID, $oldStatus, $newStatus)
    {
        $product = \wc_get_product($productID);

        if (
            !$product ||
            is_wp_error($product) ||
            !method_exists($product, 'is_type')
        ) {
            return;
        }

        if ($product->is_type('variation') ) {
            do_action('woocommerce_variation_set_stock_status', $productID, $newStatus, $product);

            return;
        }

        do_action('woocommerce_product_set_stock_status', $productID, $newStatus, $product);
    }

    /**
     * Getting status value by ID.
     *
     * @param int $productID
     *
     * @return string|null
     */
    private static function getStatus($productID)
    {
        global $wpdb;

        return $wpdb->get_var(
            $wpdb->prepare(
                "SELECT `post_status` FROM `{$wpdb->posts}` WHERE `ID` = %d",
                $productID
            )
        );
    }
}
