<?php
namespace Itgalaxy\Wc\Exchange1c\ExchangeProcess\DataResolvers;

use Itgalaxy\Wc\Exchange1c\ExchangeProcess\Helpers\Term;

class ProductAttributes
{
    public static function process($element, $productId)
    {
        if (
            !isset($element->ЗначенияСвойств) ||
            !isset($element->ЗначенияСвойств->ЗначенияСвойства)
        ) {
            return;
        }

        $productOptions = get_option('all_product_options');
        $productAttributes = get_post_meta($productId, '_product_attributes', true);

        if (empty($productAttributes)) {
            $productAttributes = [];
        }

        $currentAttributes = [];
        $setAttributes = [];

        foreach ($element->ЗначенияСвойств->ЗначенияСвойства as $property) {
            if (empty($property->Значение) || empty($productOptions[(string) $property->Ид])) {
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
            if ((string) $property->Значение === '00000000-0000-0000-0000-000000000000') {
                continue;
            }

            $attribute = $productOptions[(string) $property->Ид];

            if (
                $attribute['type'] === 'Справочник' &&
                isset($attribute['values'][(string) $property->Значение]) &&
                $attribute['values'][(string) $property->Значение] !== ''
            ) {
                $optionTermID = $attribute['values'][(string) $property->Значение];
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
                    $term = Term::insertProductAttributeValue(
                        (string) $property->Значение,
                        $attribute['taxName'],
                        $uniqId1c
                    );

                    if (!is_wp_error($term)) {
                        $optionTermID = $term['term_id'];

                        // default meta value by ordering
                        update_term_meta($optionTermID, 'order_' . $attribute['taxName'], 0);

                        Term::update1cId($optionTermID, $uniqId1c);
                    }
                }
            }

            if (!$optionTermID) {
                continue;
            }

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

            if (!isset($setAttributes[$attribute['taxName']])) {
                $setAttributes[$attribute['taxName']] = [];
            }

            $setAttributes[$attribute['taxName']][] = (int) $optionTermID;
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
        $resolvedAttributes = $productAttributes;
        $allAttributeTaxes = \array_column($productOptions, 'taxName');

        foreach ($productAttributes as $key => $value) {
            if (empty($key)) {
                unset($resolvedAttributes[$key]);

                continue;
            }

            // not check variation attribute
            if ($value['is_variation']) {
                continue;
            }

            // if not in current set and attribute was getting from 1C
            if (
                !in_array($key, $currentAttributes, true) &&
                in_array($key, $allAttributeTaxes, true)
            ) {
                unset($resolvedAttributes[$key]);

                \wp_set_object_terms(
                    $productId,
                    [],
                    $key
                );
            }
        }

        update_post_meta($productId, '_product_attributes', $resolvedAttributes);
    }

    // sort attributes based on nomenclature category settings
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
