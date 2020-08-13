<?php
namespace Itgalaxy\Wc\Exchange1c\ExchangeProcess\DataResolvers;

use Itgalaxy\Wc\Exchange1c\ExchangeProcess\Helpers\ProductAttributeHelper;
use Itgalaxy\Wc\Exchange1c\Includes\Logger;

class VariationCharacteristicsToGlobalProductAttributes
{
    public static function process($element)
    {
        $options = get_option('all_product_options', []);

        if (
            !isset($element->ХарактеристикиТовара) ||
            !isset($element->ХарактеристикиТовара->ХарактеристикаТовара)
        ) {
            return;
        }

        foreach ($element->ХарактеристикиТовара->ХарактеристикаТовара as $property) {
            $label = (string) $property->Наименование;
            $taxByLabel = trim(strtolower($label));
            $taxByLabel = hash('crc32', $taxByLabel);

            $attributeName = 'simple_' . $taxByLabel;
            $attributeTaxName = 'pa_' . $attributeName;

            $attribute = ProductAttributeHelper::get($attributeTaxName);

            // exists
            if ($attribute && isset($options[$attributeName])) {
                $options[$attributeName]['taxName'] = 'pa_' . $attribute->attribute_name;

                if (!isset($options[$attributeName]['createdTaxName'])) {
                    $options[$attributeName]['createdTaxName'] = $options[$attributeName]['taxName'];
                }

                update_option('all_product_options', $options);

                continue;
            }

            $attributeCreate = ProductAttributeHelper::insert($label, $attributeName, $attributeTaxName);
            Logger::logChanges('(attribute) Create attribute by data `ХарактеристикиТовара`', $attributeCreate);
            $attributeTaxName = 'pa_' . $attributeCreate['attribute_name'];

            $options[$attributeName] = [
                'taxName' => $attributeTaxName,
                'createdTaxName' => $attributeTaxName,
                'type' => 'simple',
                'values' => []
            ];

            \register_taxonomy($attributeTaxName, null);
        }

        if (count($options)) {
            update_option('all_product_options', $options);
        }
    }
}
