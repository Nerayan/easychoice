<?php
namespace Itgalaxy\Wc\Exchange1c\ExchangeProcess\RequestProcessing;

use Itgalaxy\Wc\Exchange1c\ExchangeProcess\RootProcessStarter;
use Itgalaxy\Wc\Exchange1c\Includes\Logger;

class CatalogModeCheckAuth
{
    public static function process()
    {
        $sessionId = session_id();

        RootProcessStarter::successResponse(
            session_name()
            . "\n"
            . $sessionId
            . "\n"
        );

        Logger::clearOldLogs();
        Logger::logProtocol('success', $sessionId);
    }
}
