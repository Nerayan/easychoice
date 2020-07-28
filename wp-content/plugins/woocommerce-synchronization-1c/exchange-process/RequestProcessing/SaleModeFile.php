<?php
namespace Itgalaxy\Wc\Exchange1c\ExchangeProcess\RequestProcessing;

use Itgalaxy\Wc\Exchange1c\ExchangeProcess\ParserXmlOrders;
use Itgalaxy\Wc\Exchange1c\ExchangeProcess\RootProcessStarter;
use Itgalaxy\Wc\Exchange1c\Includes\Bootstrap;
use Itgalaxy\Wc\Exchange1c\Includes\Logger;

class SaleModeFile
{
    public static function process()
    {
        $settings = get_option(Bootstrap::OPTIONS_KEY);

        // if exchange order not enabled
        if (
            empty($settings['handle_get_order_status_change']) &&
            empty($settings['handle_get_order_product_set_change'])
        ) {
            RootProcessStarter::successResponse();
            Logger::logProtocol('success');

            if (empty($settings['handle_get_order_status_change'])) {
                Logger::logProtocol('handle_get_order_status_change not enabled');
            }

            if (empty($settings['handle_get_order_product_set_change'])) {
                Logger::logProtocol('handle_get_order_product_set_change not enabled');
            }

            Logger::endProcessingRequestLogProtocolEntry();

            exit();
        }

        $data = false;

        if (function_exists('file_get_contents')) {
            $data = file_get_contents('php://input');
        } elseif (isset($GLOBALS['HTTP_RAW_POST_DATA'])) {
            $data = &$GLOBALS['HTTP_RAW_POST_DATA'];
        }

        if ($data === false) {
            throw new \Exception(esc_html__('Error reading http stream!', 'itgalaxy-woocommerce-1c'));
        }

        if (
            !is_writable(dirname(RootProcessStarter::getCurrentExchangeFileAbsPath())) ||
            (
                file_exists(RootProcessStarter::getCurrentExchangeFileAbsPath()) &&
                !is_writable(RootProcessStarter::getCurrentExchangeFileAbsPath())
            )
        ) {
            throw new \Exception(
                esc_html__('The directory / file is not writable', 'itgalaxy-woocommerce-1c')
                . ': '
                . basename(RootProcessStarter::getCurrentExchangeFileAbsPath())
            );
        }

        $fp = fopen(RootProcessStarter::getCurrentExchangeFileAbsPath(), 'ab');
        $result = fwrite($fp, $data);

        if ($result !== mb_strlen($data, 'latin1')) {
            throw new \Exception(esc_html__('Error writing file!', 'itgalaxy-woocommerce-1c'));
        }

        // old modules compatible, processing without import request
        if (empty($_SESSION['version'])) {
            self::processingFile();
        }

        RootProcessStarter::successResponse();
        Logger::logProtocol('success');
    }

    public static function processingFile()
    {
        // check requested parse file exists
        if (!file_exists(RootProcessStarter::getCurrentExchangeFileAbsPath())) {
            throw new \Exception(
                esc_html('File not exists! - ' . basename(RootProcessStarter::getCurrentExchangeFileAbsPath()))
            );
        }

        $parserXml = new ParserXmlOrders();
        $parserXml->parce(RootProcessStarter::getCurrentExchangeFileAbsPath());
    }
}
