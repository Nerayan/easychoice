<?php
namespace Itgalaxy\Wc\Exchange1c\ExchangeProcess\RequestProcessing;

use Itgalaxy\Wc\Exchange1c\ExchangeProcess\RootProcessStarter;
use Itgalaxy\Wc\Exchange1c\Includes\Bootstrap;
use Itgalaxy\Wc\Exchange1c\Includes\Cron;
use Itgalaxy\Wc\Exchange1c\Includes\Logger;

class CatalogModeDeactivate
{
    public static function process()
    {
        $settings = get_option(Bootstrap::OPTIONS_KEY);

        if (!empty($settings['remove_missing_products'])) {
            update_option('not_clear_1c_complete', 1);

            $cron = Cron::getInstance();
            $cron->createCronDisableItems();
        }

        RootProcessStarter::successResponse(esc_html__('Task deactivate registered!', 'itgalaxy-woocommerce-1c'));
        Logger::logProtocol('success - Task deactivate registered!');
    }
}
