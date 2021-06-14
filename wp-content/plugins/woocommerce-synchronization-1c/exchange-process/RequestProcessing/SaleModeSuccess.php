<?php
namespace Itgalaxy\Wc\Exchange1c\ExchangeProcess\RequestProcessing;

use Itgalaxy\Wc\Exchange1c\ExchangeProcess\Responses\SuccessResponse;
use Itgalaxy\Wc\Exchange1c\Includes\Bootstrap;
use Itgalaxy\Wc\Exchange1c\Includes\Logger;

class SaleModeSuccess
{
    public static function process()
    {
        $settings = get_option(Bootstrap::OPTIONS_KEY);

        // if exchange order not enabled
        if (empty($settings['send_orders'])) {
            Logger::logProtocol('order unload not enabled - ignore set setting `send_orders_last_success_export`');
        } else {
            $settings['send_orders_last_success_export'] = str_replace(' ', 'T', date_i18n('Y-m-d H:i'));
            update_option(Bootstrap::OPTIONS_KEY, $settings);
            Logger::logProtocol(
                '1c send success, setting `send_orders_last_success_export` set',
                [date_i18n('Y-m-d H:i')]
            );
        }

        SuccessResponse::send();
    }
}
