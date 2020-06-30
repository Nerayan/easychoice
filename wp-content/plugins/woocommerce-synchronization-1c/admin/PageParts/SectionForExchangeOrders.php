<?php

namespace Itgalaxy\Wc\Exchange1c\Admin\PageParts;

use Itgalaxy\Wc\Exchange1c\ExchangeProcess\RequestProcessing\SaleModeQuery;

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

        $section = [
            'title' => esc_html__('Exchange orders with 1C', 'itgalaxy-woocommerce-1c'),
            'tabs' => [
                [
                    'title' => esc_html__('Unloading orders', 'itgalaxy-woocommerce-1c'),
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
                        'send_orders_last_success_export' => [
                            'title' => esc_html__('Date / time of last request:', 'itgalaxy-woocommerce-1c'),
                            'type' => 'datetime-local',
                            'description' => esc_html__(
                                'At the next request for loading orders, which will come from 1C, the plugin will '
                                . 'unload new / changed orders starting from this date / time.',
                                'itgalaxy-woocommerce-1c'
                            )
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
                            )
                        ],
                        'send_orders_status_is_paid' => [
                            'title' => esc_html__(
                                'Order statuses at which to transfer props `Заказ оплачен` in the value `true`:',
                                'itgalaxy-woocommerce-1c'
                            ),
                            'type' => 'select2',
                            'options' => $orderStatusList,
                            'description' => esc_html__(
                                'Select the order statuses at which you want to transfer the requisite in the value '
                                . '`true`, if the order status is not one of the selected, `false` will be transferred.',
                                'itgalaxy-woocommerce-1c'
                            )
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
                            )
                        ],
                        'handle_get_order_status_change_if_paid' => [
                            'type' => 'select',
                            'title' => esc_html__(
                                'Order status, if there is "Дата оплаты по 1С" or "Дата отгрузки по 1С":',
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
                            'options' => $orderStatusList
                        ],
                    ]
                ]
            ]
        ];

        Section::render($section);
    }

    private static function sendOrdersInfoContent()
    {
        $orders = SaleModeQuery::getOrders(false);

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
