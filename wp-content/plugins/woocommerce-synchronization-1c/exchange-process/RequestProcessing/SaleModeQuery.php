<?php
namespace Itgalaxy\Wc\Exchange1c\ExchangeProcess\RequestProcessing;

use Itgalaxy\Wc\Exchange1c\Includes\Bootstrap;
use Itgalaxy\Wc\Exchange1c\Includes\Logger;

class SaleModeQuery
{
    public static function process()
    {
        $version = self::resolveVersion();
        $settings = get_option(Bootstrap::OPTIONS_KEY);

        // if exchange order not enabled
        if (empty($settings['send_orders'])) {
            self::notEnabled($version);
        }

        $xml = self::getStartedXmlObject($version);
        $orders = self::getOrders();

        Logger::logProtocol('count orders', count($orders));
        Logger::logProtocol('list order ids', $orders);

        if (count($orders) > 0) {
            foreach ($orders as $orderID) {
                $order = \wc_get_order($orderID);

                if (!$order) {
                    Logger::logProtocol('wrong order', $orderID);

                    continue;
                }

                $orderData = $order->get_data();

                if (self::resolveVersion() === '3.1') {
                    $container = $xml->addChild('Контейнер');
                    $document = $container->addChild('Документ');
                } else {
                    $document = $xml->addChild('Документ');
                }

                $document->addChild('Ид', $order->get_id());
                $document->addChild('Номер', $order->get_id());
                $document->addChild('Дата', $order->get_date_created()->date_i18n('Y-m-d'));
                $document->addChild('Время', $order->get_date_created()->date_i18n('H:i:s'));
                $document->addChild('ХозОперация', 'Заказ товара');
                $document->addChild('Роль', 'Продавец');
                $document->addChild('Валюта', self::getCurrency($order));
                $document->addChild('Курс', 1);
                $document->addChild('Сумма', $orderData['total']);

                $comment =  apply_filters(
                    'itglx_wc1c_xml_order_comment',
                    htmlspecialchars($order->get_customer_note()),
                    $order
                );

                if ($comment) {
                    $document->addChild('Комментарий', $comment);
                }

                // can be used if you want to transfer custom data
                $moreOrderInfo = apply_filters('itglx_wc1c_xml_order_info_custom', [], $orderID);

                if ($moreOrderInfo) {
                    foreach ($moreOrderInfo as $key => $moreOrderInfoValue) {
                        $document->addChild($key, $moreOrderInfoValue);
                    }
                }

                self::generateOrderContragent($document, $order);
                self::generateOrderDiscount($document, $order);
                self::generateOrderProducts($document, $order);
                self::generateOrderRequisites($document, $order);
            }
        }

        self::sendResponse($xml);

        Logger::logProtocol('order query send result');
        Logger::saveLastResponseInfo('orders content');

        // with 3.1 - 1c maybe not send request success
        // https://dev.1c-bitrix.ru/api_help/sale/algorithms/doc_from_site.php
        // ignore manual query
        if (!isset($_GET['manual-1c-import']) && self::resolveVersion() === '3.1') {
            $settings = get_option(Bootstrap::OPTIONS_KEY);

            $settings['send_orders_last_success_export'] = str_replace(' ', 'T', date_i18n('Y-m-d H:i'));
            update_option(Bootstrap::OPTIONS_KEY, $settings);

            Logger::logProtocol('setting `send_orders_last_success_export` set', [date_i18n('Y-m-d H:i')]);
        }
    }

    public static function hasNewOrders()
    {
        return !empty(self::getOrders(false));
    }

    public static function getOrders($withLog = true)
    {
        global $wpdb;

        $settings = get_option(Bootstrap::OPTIONS_KEY);
        $lastTime = !empty($settings['send_orders_last_success_export'])
            ? date_i18n('Y-m-d H:i:s', strtotime($settings['send_orders_last_success_export']))
            : date_i18n('Y-m-d H:i:s');

        if ($withLog) {
            Logger::logProtocol('start orders modified date', $lastTime);
        }

        $startOrderCreateDate = '';

        if (!empty($settings['send_orders_date_create_start'])) {
            $startOrderCreateDate = date_i18n('Y-m-d H:i:s', strtotime($settings['send_orders_date_create_start']));
        }

        if ($withLog) {
            Logger::logProtocol('setting - send_orders_date_create_start', $startOrderCreateDate);
        }

        $excludeOrdersWithStatus = !empty($settings['send_orders_exclude_if_status'])
            ? $settings['send_orders_exclude_if_status']
            : [];

        if ($withLog && $excludeOrdersWithStatus) {
            Logger::logProtocol('setting - send_orders_exclude_if_status', $excludeOrdersWithStatus);
        }

        $statuses = [];

        foreach (\wc_get_order_statuses() as $status => $_) {
            if (in_array(str_replace('wc-', '', $status), $excludeOrdersWithStatus, true)) {
                continue;
            }

            $statuses[] = $status;
        }

        $placeholders = array_fill(0, count($statuses), '%s');
        $format = implode(', ', $placeholders);
        $params = $statuses;

        if (empty($startOrderCreateDate)) {
            array_unshift($params, $lastTime);

            $orders = $wpdb->get_col(
                $wpdb->prepare(
                    "SELECT `ID` FROM `{$wpdb->posts}`
                              WHERE `post_modified` >= '%s'
                              AND `post_type` = 'shop_order'
                              AND `post_status` IN ({$format})
                              ORDER BY `post_modified`",
                    $params // 0 - $lastTime
                )
            );
        } else {
            array_unshift($params, $lastTime);
            array_unshift($params, $startOrderCreateDate);

            $orders = $wpdb->get_col(
                $wpdb->prepare(
                    "SELECT `ID` FROM `{$wpdb->posts}`
                              WHERE `post_date` >= '%s'
                              AND `post_modified` >= '%s'
                              AND `post_type` = 'shop_order'
                              AND `post_status` IN ({$format})
                              ORDER BY `post_modified`",
                    $params // 0 - $startOrderCreateDate, 1 - $lastTime
                )
            );
        }

        return $orders;
    }

    private static function resolveVersion()
    {
        $settings = get_option(Bootstrap::OPTIONS_KEY);

        if (!empty($settings['send_orders_use_scheme31'])) {
            return '3.1';
        }

        $version = '2.05';

        if (isset($_SESSION['version']) && (float) $_SESSION['version'] > 2.08) {
            $version = '2.08';
        }

        return $version;
    }

    private static function getCurrency($order)
    {
        $settings = get_option(Bootstrap::OPTIONS_KEY);

        if (!empty($settings['send_orders_set_currency_by_order_data'])) {
            return $order->get_currency();
        }

        $basePriceType = isset($settings['price_type_1']) ? $settings['price_type_1'] : '';
        $allPriceTypes = get_option('all_prices_types');

        // if empty, then use the first
        if (empty($basePriceType) && $allPriceTypes) {
            $value = reset($allPriceTypes);
            $basePriceType = $value['id'];
        }

        $currency = 'руб';

        if (!empty($basePriceType) && !empty($allPriceTypes[$basePriceType])) {
            $currency = $allPriceTypes[$basePriceType]['currency'];
        }

        return $currency;
    }

    private static function sendResponse($xml)
    {
        $settings = get_option(Bootstrap::OPTIONS_KEY);
        $resultEncoding = 'windows-1251';

        if (!empty($settings['send_orders_response_encoding'])) {
            $resultEncoding = $settings['send_orders_response_encoding'];
        }

        Logger::logProtocol('used encoding', $resultEncoding);

        switch ($resultEncoding) {
            case 'utf-8':
                header("Content-Type: text/xml; charset=utf-8");

                echo $xml->asXML();
                // escape ok

                break;
            default:
                header("Content-Type: text/xml; charset=windows-1251");

                echo mb_convert_encoding(
                    str_replace('encoding="utf-8"', 'encoding="windows-1251"', $xml->asXML()),
                    'cp1251',
                    'utf-8'
                );
                // escape ok

                break;
        }
    }

    private static function notEnabled($version)
    {
        self::sendResponse(self::getStartedXmlObject($version));

        Logger::logProtocol('order unload not enabled');
        Logger::saveLastResponseInfo('empty orders content');
        Logger::endProcessingRequestLogProtocolEntry();

        exit();
    }

    private static function getStartedXmlObject($version)
    {
        $dom = new \DOMDocument();
        $dom->loadXML(
            "<?xml version='1.0' encoding='utf-8'?><КоммерческаяИнформация></КоммерческаяИнформация>"
        );
        $xml = simplexml_import_dom($dom);
        unset($dom);

        $xml->addAttribute('ВерсияСхемы', $version);
        $xml->addAttribute('ДатаФормирования', date('Y-m-d H:i', current_time('timestamp', 0)));

        Logger::logProtocol('response scheme version - ' . $version);

        return $xml;
    }

    private static function generateOrderContragent($document, $order)
    {
        $settings = get_option(Bootstrap::OPTIONS_KEY);

        if (!empty($settings['send_orders_do_not_generate_contragent_data'])) {
            Logger::logProtocol('do not generate contragent data, order id - ' . $order->get_id());

            return;
        }

        $contragents = $document->addChild('Контрагенты');

        if (function_exists('itglx_wc1c_xml_order_contragent_data')) {
            itglx_wc1c_xml_order_contragent_data($contragents, $order);

            return;
        }

        $contactData = [];

        if ($order->get_billing_email()) {
            $contactData[] = [
                'Тип' => 'Почта',
                'Значение' => $order->get_billing_email()
            ];
        }

        if ($order->get_billing_phone()) {
            $contactData[] = [
                'Тип' => 'ТелефонРабочий',
                'Значение' => htmlspecialchars($order->get_billing_phone()),
                'Представление' => htmlspecialchars($order->get_billing_phone())
            ];
        }

        $contragentData = [
            'Ид' => $order->get_customer_id(),
            'Роль' => 'Покупатель',
            'Наименование' => htmlspecialchars(
                $order->get_billing_last_name() . ' ' . $order->get_billing_first_name()
            ),
            'ПолноеНаименование' => htmlspecialchars(
                $order->get_billing_last_name() . ' ' . $order->get_billing_first_name()
            ),
            'Фамилия' => htmlspecialchars($order->get_billing_last_name()),
            'Имя' => htmlspecialchars($order->get_billing_first_name()),
            'АдресРегистрации' => [
                'Вид' => 'Адрес доставки',
                'Представление' => htmlspecialchars(self::resolveAddress('shipping', $order)),
                'АдресноеПоле' => self::resolveContragentAddressRegistration($order)
            ],
            'Контакты' => [
                'Контакт' => $contactData
            ]
        ];

        $contragentData = apply_filters(
            'itglx_wc1c_order_xml_contragent_data_array',
            $contragentData,
            $order
        );

        if (empty($contragentData)) {
            return;
        }

        self::generateContragentXml($contragents->addChild('Контрагент'), $contragentData);
    }

    // todo: refactor to universal
    private static function generateContragentXml($xml, $data)
    {
        foreach ($data as $name => $value) {
            if (!is_array($value)) {
                $xml->addChild($name, $value);
            } else {
                $level2 = $xml->addChild($name);

                foreach ($value as $level2name => $level2value) {
                    if (!is_array($level2value)) {
                        $level2->addChild($level2name, $level2value);
                    } else {
                        foreach ($level2value as $level3name => $level3value) {
                            if (!is_array($level3value)) {
                                $level2->addChild($level3name, $level3value);
                            } else {
                                $level3 = $level2->addChild(!is_numeric($level3name) ? $level3name : $level2name);

                                foreach ($level3value as $level4name => $level4value) {
                                    $level3->addChild($level4name, $level4value);
                                }
                            }
                        }
                    }
                }
            }
        }
    }

    private static function generateOrderDiscount($document, $order)
    {
        if ($order->get_discount_total() <= 0) {
            return;
        }

        $discounts = $document->addChild('Скидки');
        $discount = $discounts->addChild('Скидка');
        $discount->addChild('Наименование', 'Скидка');
        $discount->addChild('Сумма', $order->get_discount_total());
        $discount->addChild('УчтеноВСумме', 'true');
    }

    private static function generateOrderProducts($document, $order)
    {
        $settings = get_option(Bootstrap::OPTIONS_KEY);
        $productsXml = $document->addChild('Товары');

        $products = [];

        foreach ($order->get_items() as $item) {
            if (version_compare(WC_VERSION, '4.4', '<')) {
                $product = $order->get_product_from_item($item);
            } else {
                $product = $item->get_product();
            }

            $sku = '';

            if ($product instanceof \WC_Product && $product->get_sku()) {
                $sku = $product->get_sku();
            }

            if (
                !empty($settings['send_orders_combine_data_variation_as_main_product']) &&
                $item['variation_id']
            ) {
                if (!isset($products[$item['product_id']])) {
                    $products[$item['product_id']] = [
                        'id' => $item['product_id'],
                        'productId' => $item['product_id'],
                        'variationId' => '',
                        '_id_1c' => get_post_meta($item['product_id'], '_id_1c', true),
                        'quantity' => (float) $item['qty'],
                        'name' => htmlspecialchars(get_post_field('post_title', $item['product_id'])),
                        'lineTotal' => (float) $item['line_total'],
                        'sku' => $sku,
                        'attributes' => []
                    ];
                } else {
                    $products[$item['product_id']]['quantity'] += (float) $item['qty'];
                    $products[$item['product_id']]['lineTotal'] += (float) $item['line_total'];
                }
            } else {
                $exportProduct = [
                    'originalItem' => $item,
                    'originalProduct' => $product,
                    'id' => $item['variation_id'] ? $item['variation_id'] : $item['product_id'],
                    'productId' => $item['product_id'],
                    'variationId' => $item['variation_id'],
                    '_id_1c' => get_post_meta(
                        $item['variation_id'] ? $item['variation_id'] : $item['product_id'],
                        '_id_1c',
                        true
                    ),
                    'quantity' => $item['qty'],
                    'name' => htmlspecialchars($item['name']),
                    'lineTotal' => $item['line_total'],
                    'sku' => $sku,
                    'attributes' => []
                ];

                if (
                    empty($exportProduct['_id_1c']) &&
                    $product instanceof \WC_Product &&
                    $item['variation_id'] &&
                    !empty($settings['send_orders_use_variation_characteristics_from_site']) &&
                    $product->get_attribute_summary()
                ) {
                    $attributes = explode(', ', $product->get_attribute_summary());

                    foreach ($attributes as $attribute) {
                        $exportProduct['attributes'][] = explode(': ', $attribute);
                    }
                }

                $products[$item['variation_id'] ? $item['variation_id'] : $item['product_id']] = $exportProduct;
            }
        }

        $products = apply_filters('itglx_wc1c_xml_order_product_rows', $products, $order);

        foreach ($products as $product) {
            $product = apply_filters('itglx_wc1c_xml_order_product_row_params', $product, $order);
            $productXml = $productsXml->addChild('Товар');

            // has 1C guid
            if (!empty($product['_id_1c'])) {
                $productXml->addChild('Ид', $product['_id_1c']);
            } else {
                if (!empty($settings['send_orders_use_product_id_from_site'])) {
                    Logger::logProtocol(
                        'used product/variation id form site in node "Ид"',
                        [$product['id'], $order->get_id()]
                    );

                    $productXml->addChild('Ид', $product['id']);
                } else {
                    Logger::logProtocol(
                        'generate product without node "Ид"',
                        [$product['id'], $order->get_id()]
                    );
                }

                if ($product['sku'] !== '') {
                    Logger::logProtocol(
                        'no 1C guid, added "Артикул"',
                        [$product['id'], $product['sku'], $order->get_id()]
                    );

                    $productXml->addChild('Артикул', $product['sku']);
                } else {
                    Logger::logProtocol(
                        'no 1C guid and empty sku, "Артикул" no added',
                        [$product['id'], $order->get_id()]
                    );
                }
            }

            $productXml->Наименование = wp_strip_all_tags(html_entity_decode($product['name']));
            $unit = get_post_meta($product['id'], '_unit', true);

            if ($unit) {
                $base = $productXml->addChild('БазоваяЕдиница', $unit['value']);
                $base->addAttribute('Код', $unit['code']);
                $base->addAttribute('НаименованиеПолное', $unit['nameFull']);
                $base->addAttribute('МеждународноеСокращение', $unit['internationalAcronym']);
            } else {
                $base = $productXml->addChild('БазоваяЕдиница', 'шт');
                $base->addAttribute('Код', 796);
                $base->addAttribute('НаименованиеПолное', 'Штука');
                $base->addAttribute('МеждународноеСокращение', 'PCE');
            }

            $productXml->addChild(
                'ЦенаЗаЕдиницу',
                $product['quantity'] ? $product['lineTotal'] / $product['quantity'] : 0
            );
            $productXml->addChild('Количество', $product['quantity']);
            $productXml->addChild('Сумма', $product['lineTotal']);

            if (!empty($product['attributes'])) {
                $characteristics = $productXml->addChild('ХарактеристикиТовара');

                foreach ($product['attributes'] as $attribute) {
                    $characteristic = $characteristics->addChild('ХарактеристикаТовара');
                    $characteristic->addChild('Наименование', $attribute[0]);
                    $characteristic->addChild('Значение', $attribute[1]);
                }
            }

            $details = $productXml->addChild('ЗначенияРеквизитов');

            $detail = $details->addChild('ЗначениеРеквизита');
            $detail->addChild('Наименование', 'ВидНоменклатуры');
            $detail->addChild('Значение', 'Товар');

            $detail = $details->addChild('ЗначениеРеквизита');
            $detail->addChild('Наименование', 'ТипНоменклатуры');
            $detail->addChild('Значение', 'Товар');

            // can be used if you want to transfer custom data
            $moreProductInfo = apply_filters(
                'itglx_wc1c_xml_product_info_custom',
                [],
                $product['productId'],
                $product['variationId']
            );

            if ($moreProductInfo) {
                foreach ($moreProductInfo as $key => $moreProductInfoValue) {
                    $productXml->addChild($key, $moreProductInfoValue);
                }
            }
        }

        if ($order->get_shipping_total() > 0) {
            $productXml = $productsXml->addChild('Товар');
            $productXml->addChild('Ид', 'ORDER_DELIVERY');
            $productXml->addChild(
                'Наименование',
                wp_strip_all_tags(html_entity_decode($order->get_shipping_method()))
            );

            $base = $productXml->addChild('БазоваяЕдиница', 'шт');
            $base->addAttribute('Код', 796);
            $base->addAttribute('НаименованиеПолное', 'Штука');
            $base->addAttribute('МеждународноеСокращение', 'PCE');

            $productXml->addChild('ЦенаЗаЕдиницу', $order->get_shipping_total());
            $productXml->addChild('Количество', '1');
            $productXml->addChild('Сумма', $order->get_shipping_total());

            $details = $productXml->addChild('ЗначенияРеквизитов');

            $detail = $details->addChild('ЗначениеРеквизита');
            $detail->addChild('Наименование', 'ВидНоменклатуры');
            $detail->addChild('Значение', 'Услуга');

            $detail = $details->addChild('ЗначениеРеквизита');
            $detail->addChild('Наименование', 'ТипНоменклатуры');
            $detail->addChild('Значение', 'Услуга');
        }
    }

    private static function generateOrderRequisites($document, $order)
    {
        $settings = get_option(Bootstrap::OPTIONS_KEY);
        $shippingAddress = self::resolveAddress('shipping', $order);
        $billingAddress = self::resolveAddress('billing', $order);

        $requisitesArray = [];
        $paymentGateway = wc_get_payment_gateway_by_order($order);

        if ($paymentGateway && isset($paymentGateway->title)) {
            $requisitesArray['Способ оплаты'] = wp_strip_all_tags(html_entity_decode($paymentGateway->title));
            $requisitesArray['Метод оплаты'] = $requisitesArray['Способ оплаты'];

            if (self::resolveVersion() === '3.1') {
                $requisitesArray['Метод оплаты ИД'] = $paymentGateway->id;
            }
        }

        $orderStatus = $order->get_status();

        // resolve status name - maybe mapping
        if (
            !empty($settings['send_orders_status_mapping']) &&
            !empty($settings['send_orders_status_mapping'][$orderStatus])
        ) {
            $redefinedStatus = trim($settings['send_orders_status_mapping'][$orderStatus]);

            Logger::logProtocol(
                'setting - send_orders_status_mapping is configured for current order status',
                [
                    $order->get_id(),
                    $orderStatus,
                    $redefinedStatus
                ]
            );

            $orderStatus = $redefinedStatus;
        }

        if ((float) self::resolveVersion() > 3) {
            $requisitesArray['Статус заказа ИД'] = htmlspecialchars($orderStatus);
        }

        $requisitesArray['Статус заказа'] = htmlspecialchars($orderStatus);

        $requisitesArray['Дата изменения статуса'] = $order->get_date_modified()->date_i18n('Y-m-d H:i');

        if ($order->get_shipping_method()) {
            $requisitesArray['Способ доставки'] = htmlspecialchars($order->get_shipping_method());

            if (self::resolveVersion() === '3.1') {
                $requisitesArray['Метод доставки ИД'] = current($order->get_shipping_methods())->get_method_id();
            }

            $requisitesArray['Доставка разрешена'] = $order->get_shipping_total() > 0 ? 'true' : 'false';
            $requisitesArray['Адрес доставки'] = wp_strip_all_tags(
                html_entity_decode(empty($shippingAddress) ? $billingAddress : $shippingAddress)
            );
        }

        $requisitesArray['Адрес плательщика'] = htmlspecialchars($billingAddress);
        $requisitesArray['ПометкаУдаления'] = $order->get_status() === 'cancelled' ? 'true' : 'false';
        $requisitesArray['Отменен'] = $order->get_status() === 'cancelled' ? 'true' : 'false';
        $requisitesArray['Заказ оплачен'] = self::resolveIsPaidRequisiteValue($order);

        $requisitesArray = (array) apply_filters(
            'itglx_wc1c_order_xml_requisites_data_array',
            $requisitesArray,
            $order
        );

        if (empty($requisitesArray)) {
            return;
        }

        $requisites = $document->addChild('ЗначенияРеквизитов');

        foreach ($requisitesArray as $name => $value) {
            if ($value === '') {
                continue;
            }

            $requisite = $requisites->addChild('ЗначениеРеквизита');
            $requisite->addChild('Наименование', $name);
            $requisite->addChild('Значение', $value);
        }
    }

    private static function resolveIsPaidRequisiteValue($order)
    {
        $settings = get_option(Bootstrap::OPTIONS_KEY);

        if (
            empty($settings['send_orders_status_is_paid']) &&
            empty($settings['send_orders_payment_method_is_paid'])
        ) {
            return 'false';
        }

        $paymentGateway = \wc_get_payment_gateway_by_order($order);

        if (!$paymentGateway && !empty($settings['send_orders_payment_method_is_paid'])) {
            return 'false';
        }

        if (
            !empty($settings['send_orders_status_is_paid']) &&
            !empty($settings['send_orders_payment_method_is_paid'])
        ) {
            $status = in_array($order->get_status(), $settings['send_orders_status_is_paid'], true);
            $gateway = in_array($paymentGateway->id, $settings['send_orders_payment_method_is_paid'], true);

            return $status && $gateway ? 'true' : 'false';
        }

        if (!empty($settings['send_orders_status_is_paid'])) {
            return in_array($order->get_status(), $settings['send_orders_status_is_paid'], true) ? 'true' : 'false';
        }

        return in_array($paymentGateway->id, $settings['send_orders_payment_method_is_paid'], true)
            ? 'true'
            : 'false';
    }

    private static function resolveContragentAddressRegistration($order)
    {
        $contragentAddressRegistration = [];

        if (htmlspecialchars($order->get_shipping_postcode())) {
            $contragentAddressRegistration[] = [
                'Тип' => 'Почтовый индекс',
                'Значение' => htmlspecialchars($order->get_shipping_postcode())
            ];
        }

        if ($order->get_shipping_country()) {
            $contragentAddressRegistration[] =  [
                'Тип' => 'Страна',
                'Значение' => htmlspecialchars(\WC()->countries->countries[$order->get_shipping_country()])
            ];
        }

        if (htmlspecialchars($order->get_shipping_state())) {
            $contragentAddressRegistration[] =  [
                'Тип' => 'Регион',
                'Значение' => htmlspecialchars($order->get_shipping_state())
            ];
        }

        if (htmlspecialchars($order->get_shipping_city())) {
            $contragentAddressRegistration[] =  [
                'Тип' => 'Город',
                'Значение' => htmlspecialchars($order->get_shipping_city())
            ];
        }

        if (htmlspecialchars($order->get_shipping_address_1())) {
            $contragentAddressRegistration[] =  [
                'Тип' => 'Улица',
                'Значение' => htmlspecialchars($order->get_shipping_address_1())
            ];
        }

        return $contragentAddressRegistration;
    }

    private static function resolveAddress($type, $order)
    {
        $addressArray = $order->get_address($type);
        $combineItems = ['postcode', 'country', 'state', 'city', 'address_1', 'address_2'];
        $resultAddress = [];

        foreach ($combineItems as $addressItem) {
            if (empty($addressArray[$addressItem])) {
                continue;
            }

            switch ($addressItem) {
                case 'country':
                    $resultAddress[] = \WC()->countries->countries[$addressArray[$addressItem]];
                    break;
                default:
                    $resultAddress[] = $addressArray[$addressItem];
                    break;
            }
        }

        if (empty($resultAddress)) {
            return '';
        }

        return implode(', ', $resultAddress);
    }
}
