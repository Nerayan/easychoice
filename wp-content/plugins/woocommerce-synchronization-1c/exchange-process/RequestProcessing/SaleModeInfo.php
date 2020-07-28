<?php
namespace Itgalaxy\Wc\Exchange1c\ExchangeProcess\RequestProcessing;

use Itgalaxy\Wc\Exchange1c\Includes\Bootstrap;
use Itgalaxy\Wc\Exchange1c\Includes\Logger;

class SaleModeInfo
{
    public static function process()
    {
        $dom = new \DOMDocument();
        $dom->loadXML(
            "<?xml version='1.0' encoding='utf-8'?><Справочник></Справочник>"
        );
        $xml = simplexml_import_dom($dom);
        unset($dom);

        $statusList = $xml->addChild('Статусы');
        $statusList1 = $xml->addChild('Cтатусы'); // first symbol eng `C` - compatible 1c module typo

        foreach (wc_get_order_statuses() as $status => $label) {
            $statusElement = $statusList->addChild('Элемент');
            $statusElement1 = $statusList1->addChild('Элемент');
            $statusElement->addChild('Ид', str_replace('wc-', '', $status));
            $statusElement1->addChild('Ид', str_replace('wc-', '', $status));
            $statusElement->addChild('Название', esc_html($label));
            $statusElement1->addChild('Название', esc_html($label));
        }

        $paymentMethodList = $xml->addChild('ПлатежныеСистемы');

        foreach (\WC()->payment_gateways->payment_gateways() as $id => $gateway) {
            if (isset($gateway->enabled) && 'yes' === $gateway->enabled) {
                $paymentMethodElement = $paymentMethodList->addChild('Элемент');
                $paymentMethodElement->addChild('Ид', $id);
                $paymentMethodElement->addChild('Название', $gateway->title);
                $paymentMethodElement->addChild('ТипОплаты', '');
            }
        }

        $shippingMethodList = $xml->addChild('СлужбыДоставки');

        foreach (\WC()->shipping->get_shipping_methods() as $id => $gateway) {
            if (isset($gateway->enabled) && 'yes' === $gateway->enabled) {
                $shippingMethodElement = $shippingMethodList->addChild('Элемент');
                $shippingMethodElement->addChild('Ид', $id);
                $shippingMethodElement->addChild(
                    'Название',
                    !empty($gateway->title)
                        ? $gateway->title
                        : $gateway->method_title
                );
            }
        }

        self::sendResponse($xml);

        Logger::logProtocol('info query send result');
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
}
