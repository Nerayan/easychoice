<?php

namespace Itgalaxy\Wc\Exchange1c\ExchangeProcess;

use Itgalaxy\Wc\Exchange1c\ExchangeProcess\Helpers\Product;
use Itgalaxy\Wc\Exchange1c\Includes\Bootstrap;
use Itgalaxy\Wc\Exchange1c\Includes\Logger;

class ParserXmlOrders
{
    public function __construct()
    {
        // nothing
    }

    public function parce($filename)
    {
        $settings = get_option(Bootstrap::OPTIONS_KEY);
        $reader = new \XMLReader();
        $reader->open($filename);

        $hasDocuments = false;

        while ($reader->read()) {
            if (
                $reader->name !== 'Документ' ||
                $reader->nodeType !== \XMLReader::ELEMENT
            ) {
                continue;
            }

            $hasDocuments = true;

            $element = $reader->readOuterXml();
            $element = simplexml_load_string(trim($element));

            if (empty($element->ХозОперация)) {
                Logger::logProtocol('empty `ХозОперация` - ignore');

                continue;
            }

            if ((string) $element->ХозОперация !== 'Заказ товара') {
                Logger::logProtocol('`ХозОперация` != `Заказ товара` - ignore', [(string) $element->Ид]);

                continue;
            }

            if (empty($element->Номер)) {
                Logger::logProtocol('empty `Номер` - ignore');

                continue;
            }

            $order = wc_get_order((int) $element->Номер);

            if (!$order) {
                Logger::logProtocol('not exist order by `Номер` - ' . (int) $element->Номер);

                continue;
            }

            Logger::logProtocol('exist order by `Номер` - ' . (int) $element->Номер);

            if (!empty($settings['handle_get_order_product_set_change'])) {
                Logger::logProtocol('apply changes set of products - ' . (int) $element->Номер);

                $this->applyProductChanges($order, $this->resolveXmlProductData($element));
            }

            if (empty($settings['handle_get_order_status_change'])) {
                continue;
            }

            $requisites = [];

            if (isset($element->Номер1С)) {
                $requisites['Номер по 1С'] = (string) $element->Номер1С;
            }

            if (
                isset($element->ЗначенияРеквизитов) &&
                isset($element->ЗначенияРеквизитов->ЗначениеРеквизита)
            ) {
                foreach ($element->ЗначенияРеквизитов->ЗначениеРеквизита as $requisite) {
                    $requisites[trim((string) $requisite->Наименование)] =
                        (string) $requisite->Значение;
                }
            }

            $requisites = $this->fixEmptyDateRequisites($requisites);

            update_post_meta($order->get_id(), '_itgxl_wc1c_order_requisites', $requisites);

            $resultStatus = $this->resolveResultStatus($requisites, $element);

            if (empty($resultStatus)) {
                Logger::logProtocol('empty result status - ignore', [(string) $element->Ид]);

                continue;
            }

            if ($order->get_status() === $resultStatus) {
                Logger::logProtocol(
                    'current status = result status - ignore',
                    [$order->get_status(), (string) $element->Ид]
                );

                continue;
            }

            Logger::logProtocol(
                'change order status',
                [$order->get_status(), $resultStatus, (string) $element->Ид]
            );

            $order->update_status(
                $resultStatus,
                esc_html__('Order status changed through 1C, order id - ', 'itgalaxy-woocommerce-1c')
                . (string) $element->Ид
                . (isset($requisites['Номер по 1С']) ? ' / ' . $requisites['Номер по 1С'] : '')
            );
        }

        if (!$hasDocuments) {
            Logger::logProtocol('no documents (items with tag <Документ>) to processing');
        }

        return true;
    }

    private function resolveXmlProductData($element)
    {
        $products = [];

        foreach ($element->Товары->Товар as $product) {
            $products[(string) $product->Ид] = [
                'qty' => (float) $product->Количество,
                'total' => (float) $product->Сумма
            ];
        }

        return $products;
    }

    private function applyProductChanges($order, $current1CData)
    {
        foreach ($order->get_items() as $item) {
            $guid = get_post_meta($item['variation_id'] ? $item['variation_id'] : $item['product_id'], '_id_1c', true);

            if ($guid) {
                if (!isset($current1CData[$guid])) {
                    $order->remove_item($item->get_id());

                    unset($item);

                    continue;
                }

                $item->set_quantity($current1CData[$guid]['qty']);
                $item->set_total($current1CData[$guid]['total']);
                $item->set_subtotal($current1CData[$guid]['total']);
                $item->save();

                unset($current1CData[$guid]);
            }
        }

        if (!empty($current1CData)) {
            foreach ($current1CData as $guid => $itemData) {
                $elementID = Product::getProductIdByMeta($guid);

                if (!$elementID) {
                    continue;
                }

                $product = wc_get_product($elementID);

                // must be a valid WC_Product
                if (!is_object($product)) {
                    continue;
                }

                $item = new \WC_Order_Item_Product();
                $item->set_product($product);
                $item->set_order_id($order->get_id());

                if ($product->get_type() === 'variable') {
                    $item->set_variation_id($elementID);
                }

                $item->set_quantity($itemData['qty']);
                $item->set_total($itemData['total']);
                $item->set_subtotal($itemData['total']);

                $order->add_item($item);
            }
        }

        $order->calculate_totals(true);
        $order->save();
    }

    private function resolveResultStatus($requisites, $element)
    {
        $settings = get_option(Bootstrap::OPTIONS_KEY);
        $resultStatus = '';

        if (
            !empty($settings['handle_get_order_status_change_if_paid']) &&
            (
                !empty($requisites['Дата оплаты по 1С']) ||
                !empty($requisites['Дата отгрузки по 1С'])
            )
        ) {
            Logger::logProtocol('order is paid', [(string) $element->Ид]);
            $resultStatus = $settings['handle_get_order_status_change_if_paid'];
        }

        if (
            !empty($settings['handle_get_order_status_change_if_passed']) &&
            isset($requisites['Проведен']) &&
            $requisites['Проведен'] === 'true'
        ) {
            Logger::logProtocol('order is passed', [(string) $element->Ид]);
            $resultStatus = $settings['handle_get_order_status_change_if_passed'];
        }

        if (
            !empty($settings['handle_get_order_status_change_if_deleted']) &&
            isset($requisites['ПометкаУдаления']) &&
            $requisites['ПометкаУдаления'] === 'true'
        ) {
            Logger::logProtocol('order is deleted', [(string) $element->Ид]);
            $resultStatus = $settings['handle_get_order_status_change_if_deleted'];
        }

        return $resultStatus;
    }

    private function fixEmptyDateRequisites($requisites)
    {
        /*
        * Example xml structure
        *
        <ЗначениеРеквизита>
	        <Наименование>Дата оплаты по 1С</Наименование>
	        <Значение>T</Значение>
        </ЗначениеРеквизита>
        */

        if (!empty($requisites['Дата оплаты по 1С']) && $requisites['Дата оплаты по 1С'] === 'T') {
            $requisites['Дата оплаты по 1С'] = '';
        }

        /*
        * Example xml structure
        *
        <ЗначениеРеквизита>
	        <Наименование>Дата отгрузки по 1С</Наименование>
	        <Значение>T</Значение>
        </ЗначениеРеквизита>
        */

        if (!empty($requisites['Дата отгрузки по 1С']) && $requisites['Дата отгрузки по 1С'] === 'T') {
            $requisites['Дата отгрузки по 1С'] = '';
        }

        return $requisites;
    }
}
