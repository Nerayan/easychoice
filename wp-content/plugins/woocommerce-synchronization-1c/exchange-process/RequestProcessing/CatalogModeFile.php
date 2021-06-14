<?php
namespace Itgalaxy\Wc\Exchange1c\ExchangeProcess\RequestProcessing;

use Itgalaxy\Wc\Exchange1c\ExchangeProcess\Responses\SuccessResponse;
use Itgalaxy\Wc\Exchange1c\ExchangeProcess\RootProcessStarter;
use Itgalaxy\Wc\Exchange1c\Includes\Helper;

class CatalogModeFile
{
    public static function process()
    {
        $data = false;

        if (function_exists('file_get_contents')) {
            $data = file_get_contents('php://input');
        } elseif (isset($GLOBALS['HTTP_RAW_POST_DATA'])) {
            $data = &$GLOBALS['HTTP_RAW_POST_DATA'];
        }

        if ($data === false) {
            throw new \Exception(esc_html__('Error reading http stream!', 'itgalaxy-woocommerce-1c'));
        }

        if (
            !is_writable(dirname(RootProcessStarter::getCurrentExchangeFileAbsPath())) ||
            (
                file_exists(RootProcessStarter::getCurrentExchangeFileAbsPath()) &&
                !is_writable(RootProcessStarter::getCurrentExchangeFileAbsPath())
            )
        ) {
            throw new \Exception(
                esc_html__('The directory / file is not writable', 'itgalaxy-woocommerce-1c')
                . ': '
                . basename(RootProcessStarter::getCurrentExchangeFileAbsPath())
            );
        }

        $fp = fopen(RootProcessStarter::getCurrentExchangeFileAbsPath(), 'ab');
        $result = fwrite($fp, $data);

        if ($result !== mb_strlen($data, 'latin1')) {
            throw new \Exception(esc_html__('Error writing file!', 'itgalaxy-woocommerce-1c'));
        }

        if (Helper::isUseZip()) {
            $_SESSION['IMPORT_1C']['zip_file'] = RootProcessStarter::getCurrentExchangeFileAbsPath();
        }

        SuccessResponse::send();
    }
}
