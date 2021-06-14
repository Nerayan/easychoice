<?php
namespace Itgalaxy\Wc\Exchange1c\ExchangeProcess\Base;

use Itgalaxy\Wc\Exchange1c\Includes\Helper;
use Itgalaxy\Wc\Exchange1c\Includes\Logger;

/**
 * Class Response
 */
abstract class Response {
    /**
     * Getting response type, one of the options - `success`, `progress` or `failure`
     *
     * @return string
     */
    public static function getType()
    {
        return 'success';
    }

    /**
     * Displaying the response content for 1C.
     *
     * @param string $message Additional message text.
     *
     * @return void
     */
    public static function send($message = '')
    {
        Helper::clearBuffer();

        echo static::getType() . "\n" . $message;
        // escape ok

        Logger::saveLastResponseInfo(static::getType() . ($message ? ' - ' . $message : ''));
    }
}
