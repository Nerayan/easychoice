<?php
namespace Itgalaxy\Wc\Exchange1c\ExchangeProcess\DataResolvers;

use Itgalaxy\Wc\Exchange1c\ExchangeProcess\Helpers\Product;
use Itgalaxy\Wc\Exchange1c\ExchangeProcess\Helpers\ProductAttributeHelper;
use Itgalaxy\Wc\Exchange1c\ExchangeProcess\Helpers\Term;

/**
 * Parsing and saving data on the attributes of a specific product.
 */
class ProductAttributes
{
    /**
     * Parsing attributes and values for a product according to data in XML.
     *
     * @param \SimpleXMLElement $element
     * @param int $productId
     *
     * @return void
     * @throws \Exception
     */
    public static function process($element, $productId)
    {
        if (
            !isset($element->ЗначенияСвойств) ||
            !isset($element->ЗначенияСвойств->ЗначенияСвойства)
        ) {
            $productAttributes = get_post_meta($productId, '_product_attributes', true);

            /*
             * Execution of logic is necessary to remove attributes that could have been added earlier
             * but now the product in XML does not contain attributes
             */
            if (!empty($productAttributes)) {
                self::setAttributes($productId, [], $productAttributes, []);
            }

            return;
        }

        $productOptions = get_option('all_product_options');
        $productAttributes = get_post_meta($productId, '_product_attributes', true);

        if (empty($productAttributes)) {
            $productAttributes = [];
        }

        $ignoreAttributeProcessing = apply_filters('itglx_wc1c_attribute_ignore_guid_array', []);
        $currentAttributes = [];
        $setAttributes = [];

        foreach ($element->ЗначенияСвойств->ЗначенияСвойства as $property) {
            if (has_action('itglx_wc1c_product_option_custom_processing_' . (string) $property->Ид)) {
                do_action('itglx_wc1c_product_option_custom_processing_' . (string) $property->Ид, $productId, $property);

                continue;
            }

            if (in_array((string) $property->Ид, $ignoreAttributeProcessing, true)) {
                continue;
            }

            if (empty($property->Значение) || empty($productOptions[(string) $property->Ид])) {
                continue;
            }

            $attribute = $productOptions[(string) $property->Ид];

            foreach ($property->Значение as $propertyValue) {
                $propertyValue = trim((string) $propertyValue);

                /*
                 * ignore node  with empty value - <Значение/>
                 * Example
                 *
                 <ЗначенияСвойства>
                    <Ид>5ff7fc04-d7d8-4c80-b6c6-46fe8bf9ceb2</Ид>
                    <Значение>28e4831d-d01b-11e2-aba7-001eec015c4c</Значение>
                    <Значение/>
                 </ЗначенияСвойства>
                 */
                if ($propertyValue === '') {
                    continue;
                }

                /*
                 * ignore attribute with full null value
                 * Example
                 *
                 <ЗначенияСвойства>
                    <Ид>5ff7fc04-d7d8-4c80-b6c6-46fe8bf9ceb2</Ид>
                    <Значение>00000000-0000-0000-0000-000000000000</Значение>
                 </ЗначенияСвойства>
                 */
                if ($propertyValue === '00000000-0000-0000-0000-000000000000') {
                    continue;
                }

                if (
                    $attribute['type'] === 'Справочник' &&
                    isset($attribute['values'][$propertyValue]) &&
                    $attribute['values'][$propertyValue] !== ''
                ) {
                    $optionTermID = $attribute['values'][$propertyValue];
                } else {
                    $uniqId1c = md5($attribute['createdTaxName'] . $propertyValue);

                    $optionTermID = Term::getTermIdByMeta($uniqId1c);

                    if (!$optionTermID) {
                        $optionTermID = get_term_by('name', $propertyValue, $attribute['taxName']);

                        if ($optionTermID) {
                            $optionTermID = $optionTermID->term_id;

                            Term::update1cId($optionTermID, $uniqId1c);
                        }
                    }

                    if (!$optionTermID) {
                        $term = ProductAttributeHelper::insertValue(
                            $propertyValue,
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
                    if (!isset($setAttributes[$attribute['taxName']])) {
                        $setAttributes[$attribute['taxName']] = [];
                    }

                    $setAttributes[$attribute['taxName']][] = (int) $optionTermID;
                }
            }

            if (!empty($setAttributes[$attribute['taxName']])) {
                if (!isset($productAttributes[$attribute['taxName']])) {
                    $productAttributes[$attribute['taxName']] = [
                        'name' => \wc_clean($attribute['taxName']),
                        'value' => '',
                        'position' => 0,
                        'is_visible' => 1,
                        'is_variation' => 0,
                        'is_taxonomy' => 1
                    ];
                }

                $productAttributes[$attribute['taxName']]['position'] = self::resolveAttributePosition(
                    $element,
                    (string) $property->Ид,
                    0
                );

                $currentAttributes[] = $attribute['taxName'];
            }
        }

        self::setAttributes($productId, $setAttributes, $productAttributes, $currentAttributes);
    }

    /**
     * Set resolved attributes to product.
     *
     * @param int $productId
     * @param array $setAttributes
     * @param array $allAttributes
     * @param array $currentList
     *
     * @return void
     */
    private static function setAttributes($productId, $setAttributes, $allAttributes, $currentList)
    {
        $productOptions = get_option('all_product_options', []);

        if (empty($productOptions)) {
            return;
        }

        if ($setAttributes) {
            foreach ($setAttributes as $tax => $values) {
                Term::setObjectTerms(
                    $productId,
                    array_map('intval', $values),
                    $tax
                );
            }
        }

        // remove non exists attributes
        $resolved = $allAttributes;
        $allAttributeTaxes = \array_column($productOptions, 'taxName');

        foreach ($allAttributes as $key => $value) {
            if (empty($key)) {
                unset($resolved[$key]);

                continue;
            }

            // not check variation attribute
            if ($value['is_variation']) {
                continue;
            }

            // if not in current set and attribute was getting from 1C
            if (
                !in_array($key, $currentList, true) &&
                in_array($key, $allAttributeTaxes, true)
            ) {
                unset($resolved[$key]);

                \wp_set_object_terms(
                    $productId,
                    [],
                    $key
                );
            }
        }

        Product::saveMetaValue($productId, '_product_attributes', $resolved);
    }

    /**
     * Sort attributes based on nomenclature category settings.
     *
     * @param \SimpleXMLElement $element
     * @param string $attribute1cId
     * @param int $position
     *
     * @return int
     */
    private static function resolveAttributePosition($element, $attribute1cId, $position)
    {
        if (empty($element->Категория)) {
            return $position;
        }

        $nomenclatureCategories = get_option('itglx_wc1c_nomenclature_categories', []);

        if (
            !$nomenclatureCategories ||
            !isset($nomenclatureCategories[(string) $element->Категория]) ||
            empty($nomenclatureCategories[(string) $element->Категория]['options'])
        ) {
            return $position;
        }

        return (int) array_search(
            $attribute1cId,
            $nomenclatureCategories[(string) $element->Категория]['options'],
            true
        );
    }
}
