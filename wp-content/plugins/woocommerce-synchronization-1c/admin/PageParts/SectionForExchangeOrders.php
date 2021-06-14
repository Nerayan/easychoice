<?php

namespace Itgalaxy\Wc\Exchange1c\Admin\PageParts;

use Itgalaxy\Wc\Exchange1c\ExchangeProcess\RequestProcessing\SaleModeQuery;
use Itgalaxy\Wc\Exchange1c\Includes\Bootstrap;

class SectionForExchangeOrders
{
    public static function render()
    {
        $orderStatusList = [
            '' => esc_html__('Not chosen', 'itgalaxy-woocommerce-1c')
        ];

        foreach (wc_get_order_statuses() as $status => $label) {
            $orderStatusList[str_replace('wc-', '', $status)] = $label;
        }

        $gateways = \WC()->payment_gateways->get_available_payment_gateways();
        $gatewayList = [
            '' => esc_html__('Not chosen', 'itgalaxy-woocommerce-1c')
        ];

        if($gateways) {
            foreach ($gateways as $gateway) {
                if ($gateway->enabled === 'yes') {
                    $gatewayList[$gateway->id] = !empty($gateway->title) ? $gateway->title : $gateway->id;
                }
            }
        }

        $section = [
            'title' => esc_html__('Exchange orders with 1C', 'itgalaxy-woocommerce-1c'),
            'tabs' => [
                [
                    'title' => esc_html__('Main', 'itgalaxy-woocommerce-1c'),
                    'id' => 'unload-orders',
                    'fields' => [
                        'send_orders' => [
                            'type' => 'checkbox',
                            'title' => esc_html__('Unload orders', 'itgalaxy-woocommerce-1c'),
                            'description' => esc_html__(
                                'If enabled, when exchanging with 1C, the site gives all changed and new orders '
                                . 'since the last synchronization.',
                                'itgalaxy-woocommerce-1c'
                            ),
                            'content' => self::sendOrdersInfoContent()
                        ],
                        'send_orders_response_encoding' => [
                            'type' => 'select',
                            'title' => esc_html__('Response encoding:', 'itgalaxy-woocommerce-1c'),
                            'options' => [
                                'utf-8' => esc_html__(
                                    'UTF-8',
                                    'itgalaxy-woocommerce-1c'
                                ),
                                'cp1251' => esc_html__(
                                    'CP1251 (windows-1251)',
                                    'itgalaxy-woocommerce-1c'
                                )
                            ],
                            'description' => esc_html__(
                                'If you have a problem with receiving orders and in 1C you see an error like '
                                . '"Failed to read XML", try changing the encoding.',
                                'itgalaxy-woocommerce-1c'
                            )
                        ],
                        'send_orders_use_scheme31' => [
                            'type' => 'checkbox',
                            'title' => esc_html__('Use scheme 3.1', 'itgalaxy-woocommerce-1c'),
                            'description' => esc_html__(
                                'If enabled, then the unloading of orders will be formed indicating version 3.1, a '
                                . 'number of mandatory details, as well as nesting of documents in containers.',
                                'itgalaxy-woocommerce-1c'
                            )
                        ],
                        'send_orders_last_success_export' => [
                            'title' => esc_html__('Date / time of last request:', 'itgalaxy-woocommerce-1c'),
                            'type' => 'datetime-local',
                            'description' => esc_html__(
                                'At the next request for loading orders, which will come from 1C, the plugin will '
                                . 'unload new / changed orders starting from this date / time.',
                                'itgalaxy-woocommerce-1c'
                            ),
                            'fieldsetStart' => true,
                            'legend' => esc_html__('Conditions for export orders', 'itgalaxy-woocommerce-1c')
                        ],
                        'send_orders_date_create_start' => [
                            'title' => esc_html__(
                                'Date / time of order creation no earlier:',
                                'itgalaxy-woocommerce-1c'
                            ),
                            'type' => 'datetime-local',
                            'description' => esc_html__(
                                'Use this setting if you want to set a lower bound for the date of creation of the '
                                . 'order that can be unloaded. If the order is created earlier than this date, '
                                . 'it will never be unloaded.',
                                'itgalaxy-woocommerce-1c'
                            )
                        ],
                        'send_orders_exclude_if_status' => [
                            'title' => esc_html__(
                                'Do not unload orders in selected statuses:',
                                'itgalaxy-woocommerce-1c'
                            ),
                            'type' => 'select2',
                            'options' => $orderStatusList,
                            'description' => esc_html__(
                                'Use this setting if you want to exclude orders in some status from unloading.',
                                'itgalaxy-woocommerce-1c'
                            ),
                            'fieldsetEnd' => true,
                        ]
                    ]
                ],
                [
                    'title' => esc_html__('Unloading orders (additional)', 'itgalaxy-woocommerce-1c'),
                    'id' => 'unload-orders-additional',
                    'fields' => [
                        'send_orders_use_product_id_from_site' => [
                            'type' => 'checkbox',
                            'title' => esc_html__(
                                'Use product id from the site (if there is no 1С guid)',
                                'itgalaxy-woocommerce-1c'
                            ),
                            'description' => esc_html__(
                                'If enabled, then when generating data on products, if the product / variation is not '
                                . 'connected with the data from the uploading from 1C (does not have a guid), then the '
                                . 'product / variation id will be added to the "Ид" node, otherwise if the product / '
                                . 'variation is not associated with data upload from 1C, node "Ид" will not be added.',
                                'itgalaxy-woocommerce-1c'
                            ),
                            'fieldsetStart' => true,
                            'legend' => esc_html__('For positions without GUID', 'itgalaxy-woocommerce-1c')
                        ],
                        'send_orders_use_variation_characteristics_from_site' => [
                            'type' => 'checkbox',
                            'title' => esc_html__(
                                'Generate attribute data for variations (if there is no 1С guid)',
                                'itgalaxy-woocommerce-1c'
                            ),
                            'description' => esc_html__(
                                'If enabled, then when generating data about goods, if this is a variation and it does'
                                . 'not have a guid, that is, it is not associated with unloading data, then generate'
                                . 'data on the attributes and values of the variation in node "ХарактеристикиТовара".',
                                'itgalaxy-woocommerce-1c'
                            ),
                            'fieldsetEnd' => true,
                        ],
                        'send_orders_combine_data_variation_as_main_product' => [
                            'type' => 'checkbox',
                            'title' => esc_html__(
                                'Combine data on variations and pass it as one line with the main product',
                                'itgalaxy-woocommerce-1c'
                            )
                        ],
                        'send_orders_set_currency_by_order_data' => [
                            'type' => 'checkbox',
                            'title' => esc_html__(
                                'Specify currency according to order data',
                                'itgalaxy-woocommerce-1c'
                            ),
                            'description' => esc_html__(
                                'By default the currency is used from the base price type.',
                                'itgalaxy-woocommerce-1c'
                            )
                        ],
                        'send_orders_include_tax_to_price_item_and_shipping' => [
                            'type' => 'checkbox',
                            'title' => esc_html__(
                                'Add tax to item / shipping cost',
                                'itgalaxy-woocommerce-1c'
                            ),
                            'description' => esc_html__(
                                'If enabled, then if the item has tax in the order, the value will be taken into '
                                    .'account in the information about the value of the goods in the order being '
                                    . 'unloaded.',
                                'itgalaxy-woocommerce-1c'
                            )
                        ],
                        'send_orders_add_information_discount_for_each_item' => [
                            'type' => 'checkbox',
                            'title' => esc_html__(
                                'Add information about a discount for each item of the order, if there is a discount',
                                'itgalaxy-woocommerce-1c'
                            ),
                            'description' => esc_html__(
                                'If enabled, then if there is a discount for the order item, the information about the '
                                . 'discount amount will be added to the unloading of the item. Discount information is '
                                . 'uploaded with attribute `УчтеноВСумме` in the value `true`.',
                                'itgalaxy-woocommerce-1c'
                            )
                        ],
                        'send_orders_do_not_generate_contragent_data' => [
                            'type' => 'checkbox',
                            'title' => esc_html__('Do not generate data on the contragent', 'itgalaxy-woocommerce-1c'),
                            'description' => esc_html__(
                                'If enabled, then data on the contragent, that is "Контрагенты->Контрагент", is not'
                                . 'added to the order.',
                                'itgalaxy-woocommerce-1c'
                            )
                        ],
                        'send_orders_status_is_paid' => [
                            'title' => esc_html__(
                                'Order statuses for prop `Заказ оплачен` = `true`:',
                                'itgalaxy-woocommerce-1c'
                            ),
                            'type' => 'select2',
                            'options' => $orderStatusList,
                            'description' => esc_html__(
                                'Select the order statuses at which you want to transfer the requisite in the value '
                                . '`true`, if the order status is not one of the selected, `false` will be transferred.',
                                'itgalaxy-woocommerce-1c'
                            ),
                            'fieldsetStart' => true,
                            'legend' => esc_html__('Requisite `Заказ оплачен`', 'itgalaxy-woocommerce-1c')
                        ],
                        'send_orders_payment_method_is_paid' => [
                            'title' => esc_html__(
                                'Payment methods for prop `Заказ оплачен` = `true`:',
                                'itgalaxy-woocommerce-1c'
                            ),
                            'type' => 'select2',
                            'options' => $gatewayList,
                            'description' => esc_html__(
                                'Select the payment methods at which you want to transfer the requisite in the value '
                                . '`true`, if the order status is not one of the selected, `false` will be transferred.',
                                'itgalaxy-woocommerce-1c'
                            ),
                            'fieldsetEnd' => true,
                        ],
                        'send_orders_status_mapping' => [
                            'title' => esc_html__('Names of order statuses for 1C', 'itgalaxy-woocommerce-1c'),
                            'type' => 'send_orders_status_mapping',
                            'description' => esc_html__(
                                'Use this setting if you want to set a lower bound for the date of creation of the '
                                . 'order that can be unloaded. If the order is created earlier than this date, '
                                . 'it will never be unloaded.',
                                'itgalaxy-woocommerce-1c'
                            )
                        ],
                    ]
                ],
                [
                    'title' => esc_html__(
                        'Loading changes (for previously unloaded orders)',
                        'itgalaxy-woocommerce-1c'
                    ),
                    'id' => 'upload-orders',
                    'fields' => [
                        'handle_get_order_status_change' => [
                            'type' => 'checkbox',
                            'title' => esc_html__('Handle status change', 'itgalaxy-woocommerce-1c'),
                            'description' => esc_html__(
                                'If enabled, when exchanging with 1C, then the site will accept and process changes in '
                                . 'the status of the order when 1C sends this data.',
                                'itgalaxy-woocommerce-1c'
                            ),
                            'fieldsetStart' => true,
                            'legend' => esc_html__('Change of status by requisites', 'itgalaxy-woocommerce-1c')
                        ],
                        'handle_get_order_status_change_if_paid' => [
                            'type' => 'select',
                            'title' => esc_html__(
                                'Order status, if there is "Дата оплаты по 1С":',
                                'itgalaxy-woocommerce-1c'
                            ),
                            'options' => $orderStatusList
                        ],
                        'handle_get_order_status_change_if_shipped' => [
                            'type' => 'select',
                            'title' => esc_html__(
                                'Order status, if there is "Дата отгрузки по 1С":',
                                'itgalaxy-woocommerce-1c'
                            ),
                            'options' => $orderStatusList
                        ],
                        'handle_get_order_status_change_if_paid_and_shipped' => [
                            'type' => 'select',
                            'title' => esc_html__(
                                'Order status, if there is "Дата оплаты по 1С" and "Дата отгрузки по 1С":',
                                'itgalaxy-woocommerce-1c'
                            ),
                            'options' => $orderStatusList
                        ],
                        'handle_get_order_status_change_if_passed' => [
                            'type' => 'select',
                            'title' => esc_html__('Order status if "Проведен" = "true":', 'itgalaxy-woocommerce-1c'),
                            'options' => $orderStatusList
                        ],
                        'handle_get_order_status_change_if_deleted' => [
                            'type' => 'select',
                            'title' => esc_html__(
                                'Order status if "ПометкаУдаления" = "true":',
                                'itgalaxy-woocommerce-1c'
                            ),
                            'options' => $orderStatusList,
                            'fieldsetEnd' => true
                        ],
                        'handle_get_order_product_set_change' => [
                            'type' => 'checkbox',
                            'title' => esc_html__('Handle changes in the set of products', 'itgalaxy-woocommerce-1c'),
                            'description' => esc_html__(
                                'If enabled, when exchanging with 1C, then the site will accept and process changes in '
                                . 'the set of products of the order when 1C sends this data (Add, remove, quantity, '
                                . 'price). Changes apply only if the product / variation on the site has guid from '
                                . '1C.',
                                'itgalaxy-woocommerce-1c'
                            )
                        ],
                    ]
                ]
            ]
        ];

        Section::render($section);
    }

    private static function sendOrdersInfoContent()
    {
        $settings = get_option(Bootstrap::OPTIONS_KEY, []);

        $orders = !empty($settings['send_orders']) ? SaleModeQuery::getOrders(false) : [];

        if (count($orders)) {
            $orderEditList = [];

            foreach ($orders as $orderID) {
                $orderEditList[] = '<a href="'
                    . get_edit_post_link($orderID)
                    . '" target="_blank">'
                    . (int) $orderID
                    . '</a>';
            }

            $content = sprintf(
                '%1$s (<strong>%2$d</strong>): %3$s',
                esc_html__(
                    'At the next request, 1C will receive the following orders in response',
                    'itgalaxy-woocommerce-1c'
                ),
                count($orders),
                implode(', ', $orderEditList)
            );
        } else {
            $content = '<strong>'
                . esc_html__('There are no orders to be unloaded at the next request.', 'itgalaxy-woocommerce-1c')
                . '</strong>';
        }

        $content .= sprintf(
            '<br>%1$s: <a href="%2$s" target="_blank">%3$s</a>',
            esc_html__(
                'You can see the content that will receive 1C in response to the following request',
                'itgalaxy-woocommerce-1c'
            ),
            esc_url(admin_url()) . '?manual-1c-import=true&type=sale&mode=query',
            esc_html__('open', 'itgalaxy-woocommerce-1c')
        );

        return $content;
    }
}
