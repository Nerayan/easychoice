<?php
namespace Itgalaxy\Wc\Exchange1c\ExchangeProcess\DataResolvers;

use Itgalaxy\Wc\Exchange1c\ExchangeProcess\Helpers\HeartBeat;
use Itgalaxy\Wc\Exchange1c\ExchangeProcess\Helpers\Term;
use Itgalaxy\Wc\Exchange1c\Includes\Logger;

class GlobalProductAttributes
{
    public static function process(&$reader)
    {
        global $wpdb;

        $numberOfOptions = 0;

        if (!isset($_SESSION['IMPORT_1C']['numberOfOptions'])) {
            $_SESSION['IMPORT_1C']['numberOfOptions'] = 0;
        }

        $options = get_option('all_product_options');

        if (!is_array($options)) {
            $options = [];
        }

        while (
            $reader->read() &&
            !($reader->name === 'Свойства' && $reader->nodeType === \XMLReader::END_ELEMENT)
        ) {
            /*
             * Example xml structure
             * position - Классификатор -> Свойства
             *
            <Свойства>
                <СвойствоНоменклатуры>
                    <Ид>bd9b5fd0-99c7-11ea-9e2b-00155d467e00</Ид>
                    <Наименование>ouhu</Наименование>
                    <Обязательное>false</Обязательное>
                    <Множественное>false</Множественное>
                    <ИспользованиеСвойства>true</ИспользованиеСвойства>
                </СвойствоНоменклатуры>
		    </Свойства>

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

            if (
                $reader->name !== 'Свойство' &&
                $reader->name !== 'СвойствоНоменклатуры' &&
                $reader->nodeType !== \XMLReader::ELEMENT
            ) {
                continue;
            }

            if (!HeartBeat::nextTerm()) {
                return false;
            }

            $numberOfOptions++;

            if ($numberOfOptions < $_SESSION['IMPORT_1C']['numberOfOptions']) {
                continue;
            }

            $element = simplexml_load_string(trim($reader->readOuterXml()));

            if (!isset($element->Ид)) {
                unset($element);
                $_SESSION['IMPORT_1C']['numberOfOptions'] = $numberOfOptions;
                continue;
            }

            $attribute = $wpdb->get_row(
                $wpdb->prepare(
                    "SELECT * FROM `{$wpdb->prefix}woocommerce_attribute_taxonomies` WHERE `id_1c` = %s",
                    (string) $element->Ид
                )
            );

            if ($attribute) {
                $attributeTaxName = 'pa_' . $attribute->attribute_name;

                $attributeUpdate = [
                    'attribute_label' => (string) $element->Наименование
                ];

                $wpdb->update(
                    $wpdb->prefix . 'woocommerce_attribute_taxonomies',
                    $attributeUpdate,
                    [
                        'attribute_id' => $attribute->attribute_id
                    ]
                );

                Logger::logChanges(
                    '(attribute) Update attribute by data `Свойства` - ' . $attributeTaxName,
                    (string) $element->Ид
                );
            } else {
                $attributeTaxName = uniqid();

                $attributeCreate = [
                    'attribute_label' => (string) $element->Наименование,
                    'attribute_name' => $attributeTaxName,
                    'attribute_type' => 'select',
                    'attribute_public' => 0,
                    'attribute_orderby' => 'menu_order',
                    'id_1c' => (string) $element->Ид
                ];

                $wpdb->insert(
                    $wpdb->prefix . 'woocommerce_attribute_taxonomies',
                    $attributeCreate
                );

                // maybe error when insert processing, for example, non exists column `id_1c`
                if (empty($wpdb->insert_id)) {
                    throw new \Exception(
                        'LAST ERROR - '
                        . $wpdb->last_error
                        . ', LAST QUERY - '
                        . $wpdb->last_query
                    );
                }

                Logger::logChanges(
                    '(attribute) Create attribute by data `Свойства` - ' . $attributeTaxName,
                    $attributeCreate
                );

                do_action('woocommerce_attribute_added', $wpdb->insert_id, $attributeCreate);

                flush_rewrite_rules();

                // Clear transients.
                delete_transient('wc_attribute_taxonomies');

                if (
                    class_exists('\\WC_Cache_Helper') &&
                    method_exists('\\WC_Cache_Helper', 'invalidate_cache_group')
                ) {
                    \WC_Cache_Helper::invalidate_cache_group('woocommerce-attributes');
                }

                return false;
            }

            if (isset($element->ТипЗначений)) {
                $type = (string) $element->ТипЗначений;
            } else {
                $type = 'oldType';
            }

            $options[(string) $element->Ид] = [
                'taxName' => $attributeTaxName,
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
                $numberOfOptionValues = 0;

                if (!isset($_SESSION['IMPORT_1C']['numberOfOptionValues'])) {
                    $_SESSION['IMPORT_1C']['numberOfOptionValues'] = 0;
                }

                if (!isset($_SESSION['IMPORT_1C']['currentOptionValues'])) {
                    $_SESSION['IMPORT_1C']['currentOptionValues'] = [];
                }

                foreach ($element->ВариантыЗначений->$type as $variant) {
                    if (!HeartBeat::nextTerm()) {
                        return false;
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

                    $uniqId1c = md5((string) $variant->ИдЗначения . $attributeTaxName);
                    $variantTerm = Term::getTermIdByMeta($uniqId1c);

                    if (!$variantTerm) {
                        $variantTerm = Term::getTermIdByMeta((string) $variant->ИдЗначения);
                    }

                    if ($variantTerm) {
                        $realTerm = get_term($variantTerm, $attributeTaxName);

                        if (!$realTerm) {
                            $variantTerm = false;
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
                        $variantTerm =
                            wp_insert_term(
                                (string) $variant->Значение,
                                $attributeTaxName,
                                [
                                    'slug' => uniqid(),
                                    'description' => '',
                                    'parent' => 0
                                ]
                            );

                        if (is_wp_error($variantTerm)) {
                            print_r($variantTerm);
                            // 1c response does not require escape

                            exit();
                        }

                        $variantTerm = $variantTerm['term_id'];

                        // default meta value by ordering
                        update_term_meta($variantTerm, 'order_' . $attributeTaxName, 0);

                        Term::update1cId($variantTerm, $uniqId1c);
                    }

                    $_SESSION['IMPORT_1C']['currentOptionValues'][(string) $variant->ИдЗначения] = $variantTerm;

                    $options[(string) $element->Ид]['values'][(string) $variant->ИдЗначения] = $variantTerm;
                    $_SESSION['IMPORT_1C']['numberOfOptionValues'] = $numberOfOptionValues;
                }

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

        $_SESSION['IMPORT_1C']['optionsIsParse'] = true;

        return false;
    }
}
