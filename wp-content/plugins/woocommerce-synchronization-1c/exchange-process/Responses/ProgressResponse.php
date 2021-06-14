<?php
namespace Itgalaxy\Wc\Exchange1c\ExchangeProcess\Responses;

use Itgalaxy\Wc\Exchange1c\ExchangeProcess\Base\Response;
use Itgalaxy\Wc\Exchange1c\Includes\Logger;

/**
 * Response if processing has not yet been completed.
 *
 * @link https://dev.1c-bitrix.ru/api_help/sale/algorithms/data_2_site.php
 */
class ProgressResponse extends Response
{
    public static function getType()
    {
        return 'progress';
    }

    public static function send($message = '', $data = [])
    {
        Logger::logProtocol(static::getType() . ($message ? ' - ' . $message : ''), $data);
        parent::send($message);
    }

}
