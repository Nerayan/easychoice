<?php
namespace Itgalaxy\Wc\Exchange1c\ExchangeProcess\RequestProcessing;

use Itgalaxy\Wc\Exchange1c\ExchangeProcess\RootProcessStarter;
use Itgalaxy\Wc\Exchange1c\Includes\Logger;

class Listen
{
    public static function process()
    {
        $timeLimit = 25;
        $startTime = time();

        while (!SaleModeQuery::hasNewOrders()) {
            usleep(5000);

            if ((int) (time() - $startTime) > $timeLimit) {
                break;
            }
        }

        if (SaleModeQuery::hasNewOrders()) {
            RootProcessStarter::successResponse();
            Logger::logProtocol('success');
        } else {
            \status_header(304);

            Logger::logProtocol('not modified');
        }
    }
}
