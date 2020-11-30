<?php
namespace Itgalaxy\Wc\Exchange1c\ExchangeProcess\DataResolvers;

use Itgalaxy\Wc\Exchange1c\Includes\Bootstrap;
use Itgalaxy\Wc\Exchange1c\Includes\Logger;

class PriceTypes
{
    /**
     * Processing and save global info by price types.
     *
     * @param \XMLReader $reader
     *
     * @return void
     */
    public static function process(\XMLReader &$reader)
    {
        // if processing is disabled or processing has already occurred
        if (self::isDisabled() || self::isParsed()) {
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

        Logger::logChanges('(price types) according to current data in xml', $prices);

        if (count($prices)) {
            update_option('all_prices_types', $prices);
        }

        self::setParsed();
    }

    private static function isDisabled()
    {
        $settings = get_option(Bootstrap::OPTIONS_KEY);

        if (!empty($settings['skip_product_prices'])) {
            return true;
        }

        return false;
    }

    private static function isParsed()
    {
        if (isset($_SESSION['IMPORT_1C']['price_types_parse'])) {
            return true;
        }

        return false;
    }

    private static function setParsed()
    {
        $_SESSION['IMPORT_1C']['price_types_parse'] = true;
    }
}
