<?php
namespace Itgalaxy\Wc\Exchange1c\ExchangeProcess\DataResolvers;

use Itgalaxy\Wc\Exchange1c\Includes\Bootstrap;
use Itgalaxy\Wc\Exchange1c\Includes\Logger;

class Units
{
    /**
     * Processing and save global info by units.
     *
     * @param \XMLReader $reader
     *
     * @return void
     */
    public static function process(\XMLReader &$reader)
    {
        // run once per exchange
        if (self::isParsed()) {
            return;
        }

        $units = [];

        /*
         * Example xml structure
         *
        <ЕдиницыИзмерения>
            <ЕдиницаИзмерения>
                <Ид>796 </Ид>
                <НомерВерсии>AAAAAQAAAAE=</НомерВерсии>
                <ПометкаУдаления>false</ПометкаУдаления>
                <НаименованиеКраткое>шт</НаименованиеКраткое>
                <Код>796 </Код>
                <НаименованиеПолное>Штука</НаименованиеПолное>
                <МеждународноеСокращение>PCE</МеждународноеСокращение>
            </ЕдиницаИзмерения>
        </ЕдиницыИзмерения>
        */

        while ($reader->read() &&
            !($reader->name === 'ЕдиницыИзмерения' &&
                $reader->nodeType === \XMLReader::END_ELEMENT)
        ) {
            if ($reader->name !== 'ЕдиницаИзмерения' || $reader->nodeType !== \XMLReader::ELEMENT) {
                continue;
            }

            $element = simplexml_load_string(trim($reader->readOuterXml()));

            $units[trim((string) $element->Код)] = [
                'code' => trim((string) $element->Код),
                'nameFull' => trim((string) $element->НаименованиеПолное),
                'internationalAcronym' => trim((string) $element->МеждународноеСокращение),
                'value' => trim((string) $element->НаименованиеКраткое)
            ];
        }

        Logger::logChanges('(units) according to current data in xml', $units);

        if (count($units)) {
            update_option(Bootstrap::OPTION_UNITS_KEY, $units);
        }

        self::setParsed();
    }

    private static function isParsed()
    {
        if (isset($_SESSION['IMPORT_1C']['units_parse'])) {
            return true;
        }

        return false;
    }

    private static function setParsed()
    {
        $_SESSION['IMPORT_1C']['units_parse'] = true;
    }
}
