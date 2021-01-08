<?php
namespace Itgalaxy\Wc\Exchange1c\ExchangeProcess\DataResolvers;

use Itgalaxy\Wc\Exchange1c\ExchangeProcess\Helpers\Product;
use Itgalaxy\Wc\Exchange1c\ExchangeProcess\Helpers\ProductAttributeHelper;
use Itgalaxy\Wc\Exchange1c\ExchangeProcess\Helpers\Term;
use Itgalaxy\Wc\Exchange1c\Includes\Logger;

/**
 * Parsing and saving data on the manufacturer of a specific product.
 *
 * Example xml structure (position - Товар -> Изготовитель)
 *
 * ```xml
 * <Изготовитель>
 *      <Ид>404fc2e6-cd9d-11e6-8b9d-60eb696dc507</Ид>
 *      <Наименование>Наименование изготовителя</Наименование>
 * </Изготовитель>
 */
class ProductManufacturer
{
    /**
     * @param \SimpleXMLElement $element
     * @param int $productId
     *
     * @return void
     */
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

        if (!$optionTermID) {
            return;
        }

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

        Product::saveMetaValue($productId, '_product_attributes', $productAttributes);
    }

    private static function resolveValue($element, $uniqueId1c, $taxName)
    {
        if (!isset($_SESSION['IMPORT_1C']['brand_taxonomy']['values'])) {
            $_SESSION['IMPORT_1C']['brand_taxonomy']['values'] = [];
        }

        $optionTermID = Term::getTermIdByMeta($uniqueId1c);

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
            $optionTermID = ProductAttributeHelper::insertValue(
                (string) $element->Изготовитель->Наименование,
                $taxName,
                $uniqueId1c
            );

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

        $taxByLabel = hash('crc32', 'Изготовитель');

        $attributeName = 'brand_' . $taxByLabel;
        $attributeTaxName = 'pa_' . $attributeName;

        $attribute = ProductAttributeHelper::get($attributeTaxName);

        // exists
        if ($attribute) {
            $_SESSION['IMPORT_1C']['brand_taxonomy']['name'] = 'pa_' . $attribute->attribute_name;
            $_SESSION['IMPORT_1C']['brand_taxonomy']['createdTaxName'] = $attributeTaxName;

            return 'pa_' . $attribute->attribute_name;
        }

        $attributeCreate = ProductAttributeHelper::insert('Изготовитель', $attributeName, $attributeTaxName);
        Logger::logChanges('(attribute) Create attribute `Изготовитель`', $attributeCreate);
        $attributeTaxName = 'pa_' . $attributeCreate['attribute_name'];

        \register_taxonomy($attributeTaxName, null);

        $_SESSION['IMPORT_1C']['brand_taxonomy']['name'] = $attributeTaxName;
        $_SESSION['IMPORT_1C']['brand_taxonomy']['createdTaxName'] = $attributeTaxName;

        return $attributeTaxName;
    }
}
