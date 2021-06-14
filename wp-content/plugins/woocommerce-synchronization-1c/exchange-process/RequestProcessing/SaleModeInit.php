<?php
namespace Itgalaxy\Wc\Exchange1c\ExchangeProcess\RequestProcessing;

use Itgalaxy\Wc\Exchange1c\Includes\Helper;
use Itgalaxy\Wc\Exchange1c\Includes\Logger;

class SaleModeInit
{
    public static function process()
    {
        if (isset($_GET['version'])) {
            $_SESSION['version'] = $_GET['version'];
        }

        if (!is_dir(Helper::getTempPath())) {
            throw new \Exception(esc_html__('Initialization Error!', 'itgalaxy-woocommerce-1c'));
        }

        Helper::clearBuffer();

        if (isset($_SESSION['version'])) {
            echo "zip=no\n"
                . "file_limit=10000000\n"
                . "sessid=\n"
                . "version=2.08";
        } else {
            echo "zip=no\n"
                . 'file_limit=10000000';
        }
        // 1c response does not require escape

        Logger::logProtocol('zip=no, file_limit=10000000');
        Logger::saveLastResponseInfo('parameters');
    }
}
