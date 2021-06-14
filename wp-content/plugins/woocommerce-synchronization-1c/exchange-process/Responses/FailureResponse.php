<?php
namespace Itgalaxy\Wc\Exchange1c\ExchangeProcess\Responses;

use Itgalaxy\Wc\Exchange1c\ExchangeProcess\Base\Response;
use Itgalaxy\Wc\Exchange1c\Includes\Logger;

/**
 * Error response as a result of processing the exchange request.
 */
class FailureResponse extends Response
{
    public static function getType()
    {
        return 'failure';
    }

    public static function send($message = '', $error = null)
    {
        Logger::logProtocol(static::getType() . ($message ? ' - ' . $message : ''), $error ? $error : []);
        parent::send($message);
    }
}
