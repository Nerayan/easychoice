<?php
namespace Itgalaxy\Wc\Exchange1c\ExchangeProcess\DataResolvers;

use Itgalaxy\Wc\Exchange1c\ExchangeProcess\Helpers\HeartBeat;
use Itgalaxy\Wc\Exchange1c\ExchangeProcess\Helpers\ProductAttributeHelper;
use Itgalaxy\Wc\Exchange1c\ExchangeProcess\Helpers\Term;
use Itgalaxy\Wc\Exchange1c\Includes\Logger;

/**
 * Parsing and save info by main product attributes.
 *
 * @since 1.13.0
 */
class GlobalProductAttributes
{
    /**
     * Main loop parsing.
     *
     * Example xml structure (position - Классификатор -> Свойства)
     *
     * ```xml
     * <Свойства>
     *      <СвойствоНоменклатуры>
     *          <Ид>bd9b5fd0-99c7-11ea-9e2b-00155d467e00</Ид>
     *          <Наименование>ouhu</Наименование>
     *          <Обязательное>false</Обязательное>
     *          <Множественное>false</Множественное>
     *          <ИспользованиеСвойства>true</ИспользованиеСвойства>
     *      </СвойствоНоменклатуры>
     * </Свойства>
     *
     * <Свойства>
     *      <Свойство>
     *          <Ид>65fbdca3-85d6-11da-9aea-000d884f5d77</Ид>
     *          <ПометкаУдаления>false</ПометкаУдаления>
     *          <Наименование>Модель</Наименование>
     *          <ТипЗначений>Справочник</ТипЗначений>
     *          <ВариантыЗначений>
     *              <Справочник>
     *                  <ИдЗначения>65fbdca4-85d6-11da-9aea-000d884f5d77</ИдЗначения>
     *                  <Значение>KSF 32420</Значение>
     *              </Справочник>
     *          </ВариантыЗначений>
     *      </Свойство>
     * </Свойства>
     *
     * @param \XMLReader $reader
     *
     * @return void
     * @throws \Exception
     */
    public static function process(\XMLReader $reader)
    {
        $numberOfOptions = 0;

        if (!isset($_SESSION['IMPORT_1C']['numberOfOptions'])) {
            $_SESSION['IMPORT_1C']['numberOfOptions'] = 0;
        }

        $options = get_option('all_product_options');

        if (!is_array($options)) {
            $options = [];
        }

        /**
         * Filters the list of product properties to be ignored during processing.
         *
         * @since 1.74.1
         *
         * @param string[] $ignoreAttributeProcessing Array of strings with property guid to be ignored during processing.
         */
        $ignoreAttributeProcessing = apply_filters('itglx_wc1c_attribute_ignore_guid_array', []);

        while (
            $reader->read() &&
            !($reader->name === 'Свойства' && $reader->nodeType === \XMLReader::END_ELEMENT)
        ) {
            if (
                $reader->name !== 'Свойство' &&
                $reader->name !== 'СвойствоНоменклатуры' &&
                $reader->nodeType !== \XMLReader::ELEMENT
            ) {
                continue;
            }

            if (!HeartBeat::nextTerm()) {
                return;
            }

            $numberOfOptions++;

            if ($numberOfOptions < $_SESSION['IMPORT_1C']['numberOfOptions']) {
                continue;
            }

            $element = simplexml_load_string(trim($reader->readOuterXml()));

            if (!isset($element->Ид) || in_array((string) $element->Ид, $ignoreAttributeProcessing, true)) {
                unset($element);
                $_SESSION['IMPORT_1C']['numberOfOptions'] = $numberOfOptions;
                continue;
            }

            $attribute = ProductAttributeHelper::get((string) $element->Ид);

            if (!$attribute) {
                $attribute = apply_filters('itglx_wc1c_find_exists_product_attribute', null, $element);

                if ($attribute) {
                    Logger::logChanges(
                        '(attribute) Found through filter `itglx_wc1c_find_exists_product_attribute`, `attribute_id` - '
                        . $attribute->attribute_id,
                        [(string) $element->Ид]
                    );

                    ProductAttributeHelper::update(
                        ['id_1c' => (string) $element->Ид],
                        $attribute->attribute_id
                    );
                }
            }

            if ($attribute) {
                $attributeTaxName = 'pa_' . $attribute->attribute_name;

                ProductAttributeHelper::update(
                    ['attribute_label' => (string) $element->Наименование],
                    $attribute->attribute_id
                );

                Logger::logChanges(
                    '(attribute) Update attribute by data `Свойства` - ' . $attributeTaxName,
                    (string) $element->Ид
                );
            } else {
                $attributeCreate = ProductAttributeHelper::insert(
                    (string) $element->Наименование,
                    uniqid(),
                    (string) $element->Ид
                );

                Logger::logChanges('(attribute) Create attribute by data `Свойства`', $attributeCreate);

                /**
                 * Let's interrupt the processing process, that it was started again and the created attribute was
                 * correctly processed and registered by WooCommerce before use.
                 */
                return;
            }

            if (isset($element->ТипЗначений)) {
                $type = (string) $element->ТипЗначений;
            } else {
                $type = 'oldType';
            }

            $options[(string) $element->Ид] = [
                'taxName' => $attributeTaxName,
                'createdTaxName' => isset($options[(string) $element->Ид]['createdTaxName'])
                    ? $options[(string) $element->Ид]['createdTaxName']
                    : $attributeTaxName,
                'type' => $type,
                'values' => []
            ];


            /*
             * Example xml structure
             * position - Классификатор -> Свойства
             *
            <Свойства>
                <Свойство>
                    <Ид>65fbdca3-85d6-11da-9aea-000d884f5d77</Ид>
                    <ПометкаУдаления>false</ПометкаУдаления>
                    <Наименование>Модель</Наименование>
                    <ТипЗначений>Справочник</ТипЗначений>
                    <ВариантыЗначений>
                        <Справочник>
                            <ИдЗначения>65fbdca4-85d6-11da-9aea-000d884f5d77</ИдЗначения>
                            <Значение>KSF 32420</Значение>
                        </Справочник>
                    </ВариантыЗначений>
                </Свойство>
            </Свойства>
            */

            if (isset($element->ВариантыЗначений) && isset($element->ВариантыЗначений->$type)) {
                Logger::logChanges(
                    '(attribute) Start processing variants - ' . $attributeTaxName,
                    (string) $element->Ид
                );
                $numberOfOptionValues = 0;

                if (!isset($_SESSION['IMPORT_1C']['numberOfOptionValues'])) {
                    $_SESSION['IMPORT_1C']['numberOfOptionValues'] = 0;
                }

                if (!isset($_SESSION['IMPORT_1C']['currentOptionValues'])) {
                    $_SESSION['IMPORT_1C']['currentOptionValues'] = [];
                }

                foreach ($element->ВариантыЗначений->$type as $variant) {
                    if (!HeartBeat::nextTerm()) {
                        Logger::logChanges(
                            '(attribute) Progress processing variants - ' . $attributeTaxName,
                            (string) $element->Ид
                        );

                        return;
                    }

                    $numberOfOptionValues++;

                    if ($numberOfOptionValues < $_SESSION['IMPORT_1C']['numberOfOptionValues']) {
                        continue;
                    }

                    if (empty((string) $variant->Значение)) {
                        unset($variant);
                        $_SESSION['IMPORT_1C']['numberOfOptionValues'] = $numberOfOptionValues;
                        continue;
                    }

                    $uniqId1c = md5((string) $variant->ИдЗначения . $options[(string) $element->Ид]['createdTaxName']);
                    $variantTerm = Term::getTermIdByMeta($uniqId1c);

                    if (!$variantTerm) {
                        $variantTerm = Term::getTermIdByMeta((string) $variant->ИдЗначения);
                    }

                    if (!$variantTerm) {
                        $variantTerm = apply_filters(
                            'itglx_wc1c_find_exists_product_attribute_value_term_id',
                            0,
                            $variant,
                            $attributeTaxName
                        );

                        if ($variantTerm) {
                            Logger::logChanges(
                                '(attribute) Found value through filter '
                                . '`itglx_wc1c_find_exists_product_attribute_value_term_id`, `term_id` - '
                                . $variantTerm,
                                [(string) $variant->ИдЗначения]
                            );

                            Term::update1cId($variantTerm, $uniqId1c);
                        }
                    }

                    if ($variantTerm) {
                        wp_update_term(
                            $variantTerm,
                            $attributeTaxName,
                            [
                                'name' => (string) $variant->Значение,
                                'parent' => 0
                            ]
                        );
                    } else {
                        $variantTerm = ProductAttributeHelper::insertValue(
                            (string) $variant->Значение,
                            $attributeTaxName,
                            uniqid()
                        );

                        $variantTerm = $variantTerm['term_id'];

                        // default meta value by ordering
                        update_term_meta($variantTerm, 'order_' . $attributeTaxName, 0);

                        Term::update1cId($variantTerm, $uniqId1c);
                    }

                    $_SESSION['IMPORT_1C']['currentOptionValues'][(string) $variant->ИдЗначения] = $variantTerm;

                    $options[(string) $element->Ид]['values'][(string) $variant->ИдЗначения] = $variantTerm;
                    $_SESSION['IMPORT_1C']['numberOfOptionValues'] = $numberOfOptionValues;
                }

                Logger::logChanges(
                    '(attribute) End processing variants - '
                    . $attributeTaxName
                    . ', count - '
                    . $_SESSION['IMPORT_1C']['numberOfOptionValues'],
                    (string) $element->Ид
                );

                $options[(string) $element->Ид]['values'] = $_SESSION['IMPORT_1C']['currentOptionValues'];

                // reset count resolved values
                $_SESSION['IMPORT_1C']['numberOfOptionValues'] = 0;

                // reset current resolved values
                $_SESSION['IMPORT_1C']['currentOptionValues'] = [];
            }

            if (count($options)) {
                update_option('all_product_options', $options);
            }

            $_SESSION['IMPORT_1C']['numberOfOptions'] = $numberOfOptions;
            delete_option($attributeTaxName . '_children');
            unset($element);
        }

        if (count($options)) {
            update_option('all_product_options', $options);
        }

        self::setParsed();

        return;
    }

    /**
     * Allows you to check if properties have already been processed or not.
     *
     * @return bool
     */
    public static function isParsed()
    {
        if (isset($_SESSION['IMPORT_1C']['optionsIsParsed'])) {
            return true;
        }

        return false;
    }

    /**
     * Sets the flag that properties have been processed.
     *
     * @return void
     */
    private static function setParsed()
    {
        $_SESSION['IMPORT_1C']['optionsIsParsed'] = true;
    }
}
