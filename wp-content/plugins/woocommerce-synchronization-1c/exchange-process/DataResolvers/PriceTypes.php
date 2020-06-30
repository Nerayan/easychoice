<?php
namespace Itgalaxy\Wc\Exchange1c\ExchangeProcess\DataResolvers;

class PriceTypes
{
    public static function process(&$reader)
    {
        // run once per exchange
        if (isset($_SESSION['IMPORT_1C']['price_types_parse'])) {
            return;
        }

        $prices = [];

        /*
         * Example xml structure
         *
        <ТипыЦен>
            <ТипЦены>
                <Ид>bb14a3a4-6b17-11e0-9819-e0cb4ed5eed4</Ид>
                <Наименование>Розничная</Наименование>
                <Валюта>RUB</Валюта>
            </ТипЦены>
        </ТипыЦен>
        */

        while ($reader->read() &&
            !($reader->name === 'ТипыЦен' &&
                $reader->nodeType === \XMLReader::END_ELEMENT)
        ) {
            if ($reader->name !== 'ТипЦены' || $reader->nodeType !== \XMLReader::ELEMENT) {
                continue;
            }

            $element = simplexml_load_string(trim($reader->readOuterXml()));

            $prices[(string) $element->Ид] = [
                'id' => (string) $element->Ид,
                'name' => (string) $element->Наименование,
                'currency' => (string) $element->Валюта
            ];
        }

        if (count($prices)) {
            update_option('all_prices_types', $prices);
        }

        $_SESSION['IMPORT_1C']['price_types_parse'] = true;
    }
}
