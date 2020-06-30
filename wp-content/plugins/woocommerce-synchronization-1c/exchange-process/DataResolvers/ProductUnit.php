<?php
namespace Itgalaxy\Wc\Exchange1c\ExchangeProcess\DataResolvers;

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
        */

        if (!isset($element->БазоваяЕдиница)) {
            return [];
        }

        return [
            'code' => (string) $element->БазоваяЕдиница['Код'],
            'nameFull' => (string) $element->БазоваяЕдиница['НаименованиеПолное'],
            'internationalAcronym' => (string) $element->БазоваяЕдиница['МеждународноеСокращение'],
            'value' => (string) $element->БазоваяЕдиница
        ];
    }
}
