<?php
namespace Itgalaxy\Wc\Exchange1c\ExchangeProcess\RequestProcessing;

use Itgalaxy\Wc\Exchange1c\ExchangeProcess\Responses\SuccessResponse;
use Itgalaxy\Wc\Exchange1c\ExchangeProcess\RootProcessStarter;
use Itgalaxy\Wc\Exchange1c\Includes\Bootstrap;
use Itgalaxy\Wc\Exchange1c\Includes\Logger;

class SaleModeImport
{
    public static function process()
    {
        $settings = get_option(Bootstrap::OPTIONS_KEY);

        // if exchange order not enabled
        if (
            empty($settings['handle_get_order_status_change']) &&
            empty($settings['handle_get_order_product_set_change'])
        ) {
            if (empty($settings['handle_get_order_status_change'])) {
                Logger::logProtocol('handle_get_order_status_change not enabled');
            }

            if (empty($settings['handle_get_order_product_set_change'])) {
                Logger::logProtocol('handle_get_order_product_set_change not enabled');
            }

            SuccessResponse::send();

            return;
        }

        SaleModeFile::processingFile();

        // clean previous getting order file after processing
        if (!empty($settings['not_delete_exchange_files'])) {
            Logger::logProtocol(
                'setting `not_delete_exchange_files` is enabled, order file not deleted',
                [basename(RootProcessStarter::getCurrentExchangeFileAbsPath())]
            );
        } else {
            unlink(RootProcessStarter::getCurrentExchangeFileAbsPath());

            Logger::logProtocol(
                'order file deleted after processing',
                [basename(RootProcessStarter::getCurrentExchangeFileAbsPath())]
            );
        }

        SuccessResponse::send();
    }
}
