<?php
namespace Itgalaxy\Wc\Exchange1c\ExchangeProcess\RequestProcessing;

use Itgalaxy\Wc\Exchange1c\Includes\Bootstrap;
use Itgalaxy\Wc\Exchange1c\Includes\Helper;
use Itgalaxy\Wc\Exchange1c\Includes\Logger;

class CatalogModeInit
{
    public static function process()
    {
        Logger::logProtocol('php `memory_limit` string - ' . ini_get('memory_limit'));

        $settings = get_option(Bootstrap::OPTIONS_KEY);

        if (!is_dir(Helper::getTempPath())) {
            throw new \Exception(esc_html__('Initialization Error!', 'itgalaxy-woocommerce-1c'));
        }

        // clean previous exchange files
        if (!empty($settings['not_delete_exchange_files'])) {
            Logger::logProtocol(
                'setting `not_delete_exchange_files` is enabled, data from the previous exchange session is not deleted'
            );
        } elseif (is_writable(Helper::getTempPath())) {
            Helper::removeDir(Helper::getTempPath());
            mkdir(Helper::getTempPath(), 0755, true);
        }

        $zip = Helper::isUseZip() ? 'yes' : 'no';

        Helper::clearBuffer();

        echo "zip={$zip}\n" . 'file_limit=' . (int) Helper::getFileSizeLimit();
        // 1c response does not require escape

        Logger::logProtocol('zip=' . $zip . ', file_limit=' . Helper::getFileSizeLimit());

        if (!isset($_SESSION['IMPORT_1C'])) {
            $_SESSION['IMPORT_1C'] = [];
        }

        if (!isset($_SESSION['IMPORT_1C_PROCESS'])) {
            $_SESSION['IMPORT_1C_PROCESS'] = [];
        }
    }
}
