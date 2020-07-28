<?php
namespace Itgalaxy\Wc\Exchange1c\ExchangeProcess\DataResolvers;

use Itgalaxy\Wc\Exchange1c\ExchangeProcess\Helpers\Term;
use Itgalaxy\Wc\Exchange1c\Includes\Logger;

/*
* Example xml structure
* position - Товар -> Изготовитель
*
<Изготовитель>
    <Ид>404fc2e6-cd9d-11e6-8b9d-60eb696dc507</Ид>
    <Наименование>Наименование изготовителя</Наименование>
</Изготовитель>
*/

class ProductManufacturer
{
    public static function process($element, $productId)
    {
        if (
            !isset($element->Изготовитель) ||
            !isset($element->Изготовитель->Ид)
        ) {
            return;
        }

        $taxName = self::resolveAttribute();

        if (empty($taxName)) {
            return;
        }

        $productAttributes = get_post_meta($productId, '_product_attributes', true);

        if (empty($productAttributes)) {
            $productAttributes = [];
        }

        $uniqueId1c = md5(
            (string) $element->Изготовитель->Ид
            . $_SESSION['IMPORT_1C']['brand_taxonomy']['createdTaxName']
        );

        if (
            !isset($_SESSION['IMPORT_1C']['brand_taxonomy']['values']) ||
            !isset($_SESSION['IMPORT_1C']['brand_taxonomy']['values'][$uniqueId1c])
        ) {
            $optionTermID = self::resolveValue($element, $uniqueId1c, $taxName);
        } else {
            $optionTermID = $_SESSION['IMPORT_1C']['brand_taxonomy']['values'][$uniqueId1c];
        }

        if ($optionTermID) {
            if (!isset($productAttributes[$taxName])) {
                $productAttributes[$taxName] = [
                    'name' => \wc_clean($taxName),
                    'value' => '',
                    'position' => 0,
                    'is_visible' => 1,
                    'is_variation' => 0,
                    'is_taxonomy' => 1
                ];
            }

            Term::setObjectTerms(
                $productId,
                (int) $optionTermID,
                $taxName
            );

            update_post_meta($productId, '_product_attributes', $productAttributes);
        }
    }

    private static function resolveValue($element, $uniqueId1c, $taxName)
    {
        if (!isset($_SESSION['IMPORT_1C']['brand_taxonomy']['values'])) {
            $_SESSION['IMPORT_1C']['brand_taxonomy']['values'] = [];
        }

        $optionTermID = Term::getTermIdByMeta($uniqueId1c);

        if ($optionTermID) {
            $realTerm = get_term($optionTermID, $taxName);

            if (!$realTerm) {
                $optionTermID = false;
            }
        }

        if ($optionTermID) {
            wp_update_term(
                $optionTermID,
                $taxName,
                [
                    'name' => (string) $element->Изготовитель->Наименование,
                    'parent' => 0
                ]
            );
        } else {
            $optionTermID = Term::insertProductAttributeValue(
                (string) $element->Изготовитель->Наименование,
                $taxName,
                $uniqueId1c
            );

            if (is_wp_error($optionTermID)) {
                throw new \Exception(
                    'ERROR ADD ATTRIBUTE VALUE - '
                    . $optionTermID->get_error_message()
                    . ', tax - '
                    . $taxName
                    . ', value - '
                    . (string) $element->Изготовитель->Наименование
                );
            }

            $optionTermID = $optionTermID['term_id'];

            // default meta value by ordering
            update_term_meta($optionTermID, 'order_' . $taxName, 0);

            Term::update1cId($optionTermID, $uniqueId1c);
        }

        if ($optionTermID) {
            $_SESSION['IMPORT_1C']['brand_taxonomy']['values'][$uniqueId1c] = $optionTermID;
        }

        return $optionTermID;
    }

    private static function resolveAttribute()
    {
        if (isset($_SESSION['IMPORT_1C']['brand_taxonomy'])) {
            return $_SESSION['IMPORT_1C']['brand_taxonomy']['name'];
        } else {
            $_SESSION['IMPORT_1C']['brand_taxonomy'] = [];
        }

        global $wpdb;

        $taxByLabel = hash('crc32', 'Изготовитель');

        $attributeName = 'brand_' . $taxByLabel;
        $attributeTaxName = 'pa_' . $attributeName;

        $attribute = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM `{$wpdb->prefix}woocommerce_attribute_taxonomies` WHERE `id_1c` = %s",
                $attributeTaxName
            )
        );

        // exists
        if ($attribute) {
            $_SESSION['IMPORT_1C']['brand_taxonomy']['name'] = 'pa_' . $attribute->attribute_name;
            $_SESSION['IMPORT_1C']['brand_taxonomy']['createdTaxName'] = $attributeTaxName;

            return 'pa_' . $attribute->attribute_name;
        }

        $attributeCreate = [
            'attribute_label' => 'Изготовитель',
            'attribute_name' => $attributeName,
            'attribute_type' => 'select',
            'attribute_public' => 0,
            'attribute_orderby' => 'menu_order',
            'id_1c' => $attributeTaxName
        ];

        $wpdb->insert(
            $wpdb->prefix . 'woocommerce_attribute_taxonomies',
            $attributeCreate
        );

        // maybe error when insert processing, for example, non exists column `id_1c`
        if (empty($wpdb->insert_id)) {
            throw new \Exception(
                'LAST ERROR - '
                . $wpdb->last_error
                . ', LAST QUERY - '
                . $wpdb->last_query
            );
        }

        Logger::logChanges(
            '(attribute) Create attribute `Изготовитель` - ' . $attributeName,
            $attributeCreate
        );

        do_action('woocommerce_attribute_added', $wpdb->insert_id, $attributeCreate);

        flush_rewrite_rules();
        delete_transient('wc_attribute_taxonomies');

        if (
            class_exists('\\WC_Cache_Helper') &&
            method_exists('\\WC_Cache_Helper', 'invalidate_cache_group')
        ) {
            \WC_Cache_Helper::invalidate_cache_group('woocommerce-attributes');
        }

        delete_option($attributeTaxName . '_children');

        register_taxonomy($attributeTaxName, null);

        $_SESSION['IMPORT_1C']['brand_taxonomy']['name'] = $attributeTaxName;
        $_SESSION['IMPORT_1C']['brand_taxonomy']['createdTaxName'] = $attributeTaxName;

        return $attributeTaxName;
    }
}
