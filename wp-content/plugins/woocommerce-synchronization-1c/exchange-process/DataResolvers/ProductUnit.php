<?php
namespace Itgalaxy\Wc\Exchange1c\ExchangeProcess\DataResolvers;

use Itgalaxy\Wc\Exchange1c\Includes\Bootstrap;

class ProductUnit
{
    public static function process($element)
    {
        /*
         * Examples xml structure
         * position - Товар -> БазоваяЕдиница
         *
        <БазоваяЕдиница Код="796" НаименованиеПолное="Штука" МеждународноеСокращение="PCE"/>
        <БазоваяЕдиница Код="796" НаименованиеПолное="Штука" МеждународноеСокращение="PCE">шт</БазоваяЕдиница>
        <БазоваяЕдиница Код="778 " НаименованиеПолное="Упаковка">уп.</БазоваяЕдиница>
        <БазоваяЕдиница>796 </БазоваяЕдиница>
        */

        if (!isset($element->БазоваяЕдиница)) {
            return [];
        }

        $value = trim((string) $element->БазоваяЕдиница);
        $globalUnits = get_option(Bootstrap::OPTION_UNITS_KEY, []);

        if (!empty($globalUnits) && isset($globalUnits[$value])) {
            return $globalUnits[$value];
        }

        return [
            'code' => (string) $element->БазоваяЕдиница['Код'],
            'nameFull' => (string) $element->БазоваяЕдиница['НаименованиеПолное'],
            'internationalAcronym' => (string) $element->БазоваяЕдиница['МеждународноеСокращение'],
            'value' => $value
        ];
    }
}
