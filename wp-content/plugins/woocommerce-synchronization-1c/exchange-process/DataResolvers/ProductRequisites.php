<?php
namespace Itgalaxy\Wc\Exchange1c\ExchangeProcess\DataResolvers;

use Itgalaxy\Wc\Exchange1c\Includes\Bootstrap;

class ProductRequisites
{
    private static $ignoreRequisites = [
        'Файл',
        'ОписаниеФайла'
    ];

    private static $excludeFromAllRequisites = [
        'ОписаниеВФорматеHTML'
    ];

    public static function process($element)
    {
        $requisites = [
            'fullName' => '',
            'weight' => 0,
            'htmlPostContent' => '',
            'allRequisites' => []
        ];

        /*
         * Example xml structure
         * position - Товар -> ЗначенияРеквизитов
         *
        <ЗначенияРеквизитов>
            <ЗначениеРеквизита>
                <Наименование>ТипНоменклатуры</Наименование>
                <Значение>Запас</Значение>
            </ЗначениеРеквизита>
            <ЗначениеРеквизита>
                <Наименование>Полное наименование</Наименование>
                <Значение>Стол Трансформер Сонома</Значение>
            </ЗначениеРеквизита>
        </ЗначенияРеквизитов>
        */

        if (
            isset($element->ЗначенияРеквизитов) &&
            isset($element->ЗначенияРеквизитов->ЗначениеРеквизита)
        ) {
            $requisites = self::resolveMainRequisitesData($element, $requisites);
        }

        $requisites = self::resolveVariantPositionData($element, $requisites);

        return $requisites;
    }

    private static function resolveMainRequisitesData($element, $requisites)
    {
        $settings = get_option(Bootstrap::OPTIONS_KEY);

        foreach ($element->ЗначенияРеквизитов->ЗначениеРеквизита as $requisite) {
            $requisiteName = trim((string) $requisite->Наименование);

            /*
            * ignore requisite as this is useless information
            * example xml
            *
            <ЗначениеРеквизита>
               <Наименование>ОписаниеФайла</Наименование>
               <Значение>import_files/dd/ddd52f065b2511ea2c8cfa163e1c47cc_1ceaf6265b3f11ea2c8cfa163e1c47cc.jpg#569</Значение>
            </ЗначениеРеквизита>
            */

            if (in_array($requisiteName, self::$ignoreRequisites, true)) {
                continue;
            }

            if (!in_array($requisiteName, self::$excludeFromAllRequisites, true)) {
                $requisites['allRequisites'][$requisiteName] = (string) $requisite->Значение;
            }

            switch ($requisiteName) {
                case 'Полное наименование':
                case 'Повне найменування': // requisite name in Ukrainian configurations
                    $fullName = (string) $requisite->Значение;

                    if (!empty($fullName) && !empty($settings['product_use_full_name'])) {
                        $requisites['fullName'] = $fullName;
                    }

                    break;
                case 'ОписаниеВФорматеHTML':
                    $htmlPostContent = html_entity_decode((string) $requisite->Значение);

                    if (!empty($htmlPostContent) && !empty($settings['use_html_description'])) {
                        $requisites['htmlPostContent'] = $htmlPostContent;
                    }

                    break;
                case 'Вес':
                    $weight = (float) $requisite->Значение;

                    if ($weight > 0) {
                        $requisites['weight'] = $weight;
                    }

                    break;
                case 'Длина':
                    $value = (float) $requisite->Значение;

                    if ($value > 0) {
                        $requisites['length'] = $value;
                    }

                    break;
                case 'Ширина':
                    $value = (float) $requisite->Значение;

                    if ($value > 0) {
                        $requisites['width'] = $value;
                    }

                    break;
                case 'Высота':
                    $value = (float) $requisite->Значение;

                    if ($value > 0) {
                        $requisites['height'] = $value;
                    }

                    break;
                default:
                    // Nothing
                    break;
            }
        }

        return $requisites;
    }

    private static function resolveVariantPositionData($element, $requisites)
    {
        /*
         * resolve xml position variant - Товар -> Реквизит
         * example xml structure
         *
        <Товар>
            ....
            <Длина>1</Длина>
            <Ширина>1</Ширина>
            <Высота>1</Высота>
            <Вес>1</Вес>
            ....
        </Товар>
        */

        $resolveArray = [
            'weight' => 'Вес',
            'length' => 'Длина',
            'width' => 'Ширина',
            'height' => 'Высота'
        ];

        foreach ($resolveArray as $requisitesArrayKey => $xmlNodeName) {
            if (
                !empty($requisites[$requisitesArrayKey]) ||
                !isset($element->$xmlNodeName)
            ) {
                continue;
            }

            $value = (float) $element->$xmlNodeName;

            if ($value <= 0) {
                continue;
            }

            $requisites[$requisitesArrayKey] = $value;
        }

        return $requisites;
    }
}
