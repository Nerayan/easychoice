<?php
namespace Itgalaxy\Wc\Exchange1c\ExchangeProcess\Responses;

use Itgalaxy\Wc\Exchange1c\ExchangeProcess\Base\Response;
use Itgalaxy\Wc\Exchange1c\Includes\Logger;

/**
 * Response if the exchange request was processed successfully.
 */
class SuccessResponse extends Response
{
    public static function getType()
    {
        return 'success';
    }

    public static function send($message = '', $data = [])
    {
        Logger::logProtocol(static::getType() . ($message ? ' - ' . $message : ''), $data);
        parent::send($message);
    }

}
