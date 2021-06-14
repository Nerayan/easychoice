<?php
namespace Itgalaxy\Wc\Exchange1c\ExchangeProcess\Helpers;

use Itgalaxy\Wc\Exchange1c\ExchangeProcess\DataResolvers\ProductVariationAttributes;
use Itgalaxy\Wc\Exchange1c\ExchangeProcess\DataResolvers\VariationCharacteristicsToGlobalProductAttributes;
use Itgalaxy\Wc\Exchange1c\ExchangeProcess\DataResolvers\VariationImages;
use Itgalaxy\Wc\Exchange1c\Includes\Bootstrap;
use Itgalaxy\Wc\Exchange1c\Includes\Logger;

class ProductVariation
{
    /**
     * @param \SimpleXMLElement $element
     * @param array $variationEntry
     * @param int $postAuthor
     * @param bool $onlyCharacteristics
     * @param bool $ignoreImage
     *
     * @return array
     * @throws \Exception
     */
    public static function mainData($element, $variationEntry, $postAuthor, $onlyCharacteristics = false, $ignoreImage = false)
    {
        $settings = get_option(Bootstrap::OPTIONS_KEY, []);
        $isNewVariation = empty($variationEntry['ID']);
        $offerHash = md5(json_encode((array) $element));

        if (!isset($_SESSION['IMPORT_1C']['setTerms'])) {
            $_SESSION['IMPORT_1C']['setTerms'] = [];
        }

        if (!isset($_SESSION['IMPORT_1C']['productVariations'])) {
            $_SESSION['IMPORT_1C']['productVariations'] = [];
        }

        /**
         * Don't overwrite data if there is no change.
         */
        if (
            !$isNewVariation &&
            empty($settings['force_update_product']) &&
            $offerHash == get_post_meta($variationEntry['ID'], '_md5_offer', true)
        ) {
            /**
             * @see ProductVariationAttributes::processCharacteristics()
             * @see ProductVariationAttributes::processOptions()
             */
            $currentAttributeValues = get_post_meta($variationEntry['ID'], '_itglx_wc1c_attributes_state', true);

            foreach ($currentAttributeValues as $attributeTax => $optionTermID) {
                $_SESSION['IMPORT_1C']['setTerms'][$variationEntry['post_parent']][$attributeTax][] = $optionTermID;
            }

            $_SESSION['IMPORT_1C']['productVariations'][$variationEntry['post_parent']][] = $variationEntry['ID'];

            Logger::logChanges(
                '(variation) not changed - skip, ID - '
                . $variationEntry['ID']
                . ', parent ID - '
                . $variationEntry['post_parent'],
                [(string) $element->Ид]
            );

            return $variationEntry;
        }

        // prepare of the main product and indication of its type
        if (!get_post_meta($variationEntry['post_parent'], '_is_set_variable', true)) {
            Term::setObjectTerms($variationEntry['post_parent'], 'variable', 'product_type');
            Product::saveMetaValue($variationEntry['post_parent'], '_manage_stock', 'no');
            Product::saveMetaValue($variationEntry['post_parent'], '_is_set_variable', true);
        }

        $variationEntry = self::createUpdate($element, $variationEntry, $postAuthor, $isNewVariation);

        $_SESSION['IMPORT_1C']['productVariations'][$variationEntry['post_parent']][] = $variationEntry['ID'];

        if (!$onlyCharacteristics && ProductVariationAttributes::hasOptions($element)) {
            ProductVariationAttributes::processOptions($element, $variationEntry);
        }
        // simple variant without ids
        elseif (ProductVariationAttributes::hasCharacteristics($element)) {
            VariationCharacteristicsToGlobalProductAttributes::process($element);
            ProductVariationAttributes::processCharacteristics($element, $variationEntry);
        }

        self::saveMetaValue($variationEntry['ID'], '_md5_offer', $offerHash, $variationEntry['post_parent']);

        // variation image processing
        if (
            !$ignoreImage &&
            ($isNewVariation || empty($settings['skip_post_images']))
        ) {
            VariationImages::process($element, $variationEntry['ID'], $postAuthor);
        }

        return $variationEntry;
    }

    /**
     * Create/update product variation post main data by offer data.
     *
     * @param \SimpleXMLElement $element
     * @param array $variationEntry
     * @param int $postAuthor
     * @param bool $isNewVariation
     *
     * @return array
     */
    public static function createUpdate($element, $variationEntry, $postAuthor, $isNewVariation)
    {
        global $wpdb;

        // update variation
        if (!$isNewVariation) {
            /**
             * Filters the set of values for the product variation being updated.
             *
             * @since 1.93.0
             *
             * @param array $params Array a set of values for the product variation post.
             * @param \SimpleXMLElement $element 'Предложение' (or `Товар` for old format {@see resolveOldVariant()}) node object.
             */
            $params = apply_filters(
                'itglx_wc1c_update_post_variation_params',
                [
                    'post_title' => (string) $element->Наименование,
                    'post_name' => sanitize_title((string) $element->Наименование),
                    'post_parent' => $variationEntry['post_parent']
                ],
                $element
            );

            $wpdb->update($wpdb->posts, $params, ['ID' => $variationEntry['ID']]);

            Logger::logChanges(
                '(variation) Updated, ID - '
                . $variationEntry['ID']
                . ', parent ID - '
                . $variationEntry['post_parent'],
                [(string) $element->Ид]
            );
        }
        // create variation
        else {
            /**
             * Filters the set of values for the product variation being created.
             *
             * @since 1.93.0
             *
             * @param array $params Array a set of values for the product variation post.
             * @param \SimpleXMLElement $element 'Предложение' (or `Товар` for old format {@see resolveOldVariant()}) node object.
             */
            $params = apply_filters(
                'itglx_wc1c_insert_post_variation_params',
                [
                    'post_title' => (string) $element->Наименование,
                    'post_type' => 'product_variation',
                    'post_name' => sanitize_title((string) $element->Наименование),
                    'post_author' => $postAuthor,
                    'post_parent' => $variationEntry['post_parent'],
                    /**
                     * The variation is created in the off state and the decision on its state is made when
                     * processing the stock.
                     */
                    'post_status' => 'private'
                ],
                $element
            );

            /**
             * @link https://developer.wordpress.org/reference/functions/wp_insert_post/
             */
            $variationEntry['ID'] = \wp_insert_post($params);

            self::saveMetaValue($variationEntry['ID'], '_id_1c', (string) $element->Ид, $variationEntry['post_parent']);

            Logger::logChanges(
                '(variation) Added, ID - '
                . $variationEntry['ID']
                . ', parent ID - '
                . $variationEntry['post_parent'],
                [(string) $element->Ид]
            );
        }

        // processing and recording the sku for variable offers.
        if (isset($element->Артикул)) {
            $parentSku = get_post_meta($variationEntry['post_parent'], '_sku', true);
            $offerSku = trim((string) $element->Артикул);

            if ($offerSku !== $parentSku) {
                self::saveMetaValue($variationEntry['ID'], '_sku', $offerSku, $variationEntry['post_parent']);
            }
        }

        return $variationEntry;
    }

    /**
     * The method allows to create a product variation according to the old format.
     *
     * In this case, the main data, as well as characteristics, come in node `Товар`.
     *
     * @param \SimpleXMLElement $element
     * @param int $postAuthor
     *
     * @return void
     * @throws \Exception
     */
    public static function resolveOldVariant($element, $postAuthor)
    {
        $parseID = explode('#', (string) $element->Ид);

        //empty variation hash
        if (empty($parseID[1])) {
            return;
        }

        if (!ProductVariationAttributes::hasCharacteristics($element)) {
            return;
        }

        $variationEntry = [
            'post_parent' => Product::getProductIdByMeta($parseID[0])
        ];

        if (empty($variationEntry['post_parent'])) {
            Logger::logChanges('(variation) Error! Not exists parent product', [(string) $element->Ид]);

            return;
        }

        $variationEntry['ID'] = self::getIdByMeta((string) $element->Ид, '_id_1c');

        self::mainData($element, $variationEntry, $postAuthor, true, true);
    }

    /**
     * @param $variationID
     * @param $metaKey
     * @param $metaValue
     * @param $parentProductID
     *
     * @return void
     */
    public static function saveMetaValue($variationID, $metaKey, $metaValue, $parentProductID)
    {
        update_post_meta(
            $variationID,
            $metaKey,
            apply_filters('itglx_wc1c_variation_meta_' . $metaKey . '_value', $metaValue, $variationID, $parentProductID)
        );
    }

    /**
     * The method allows to find the post id of the variation by the meta key and value.
     *
     * @param string $value
     * @param string $metaKey
     *
     * @return int|null
     * @link https://developer.wordpress.org/reference/classes/wpdb/
     */
    public static function getIdByMeta($value, $metaKey = '_id_1c')
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

        return $product->post_type === 'product_variation' ? (int) $product->post_id : null;
    }

    /**
     * Deleting a product variation image.
     *
     * @param int $variationID
     *
     * @return void
     * @link https://developer.wordpress.org/reference/functions/has_post_thumbnail/
     * @link https://developer.wordpress.org/reference/functions/wp_delete_attachment/
     * @link https://developer.wordpress.org/reference/functions/get_post_thumbnail_id/
     * @link https://developer.wordpress.org/reference/functions/delete_post_thumbnail/
     */
    public static function removeImage($variationID)
    {
        if (!has_post_thumbnail($variationID)) {
            return;
        }

        wp_delete_attachment(get_post_thumbnail_id($variationID), true);
        delete_post_thumbnail($variationID);

        Logger::logChanges(
            '(image) Removed thumbnail for variation ID - ' . $variationID,
            [get_post_meta($variationID, '_id_1c', true)]
        );
    }

    /**
     * Deleting a product variation.
     *
     * @param int $variationID
     * @param int $productID
     *
     * @return void
     * @link https://developer.wordpress.org/reference/functions/wp_delete_post/
     */
    public static function remove($variationID, $productID = 0)
    {
        self::removeImage($variationID);

        Logger::logChanges(
            '(variation) removed variation, ID - ' . $variationID,
            [get_post_meta($variationID, '_id_1c', true)]
        );

        wp_delete_post($variationID, true);

        if ($productID) {
            delete_transient('wc_product_children_' . $productID);
        }
    }
}
