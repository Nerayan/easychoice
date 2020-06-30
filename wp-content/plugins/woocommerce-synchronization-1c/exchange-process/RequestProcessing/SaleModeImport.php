<?php
namespace Itgalaxy\Wc\Exchange1c\ExchangeProcess\RequestProcessing;

use Itgalaxy\Wc\Exchange1c\ExchangeProcess\RootProcessStarter;
use Itgalaxy\Wc\Exchange1c\Includes\Bootstrap;
use Itgalaxy\Wc\Exchange1c\Includes\Logger;

class SaleModeImport
{
    public static function process()
    {
        $settings = get_option(Bootstrap::OPTIONS_KEY);

        SaleModeFile::processingFile();

        // clean previous getting order file after processing
        if (!empty($settings['not_delete_exchange_files'])) {
            Logger::logProtocol(
                'setting `not_delete_exchange_files` is enabled, order file not deleted',
                [basename(RootProcessStarter::getCurrentExchangeFileAbsPath())]
            );
        } else {
            unlink(RootProcessStarter::getCurrentExchangeFileAbsPath());

            Logger::logProtocol(
                'order file deleted after processing',
                [basename(RootProcessStarter::getCurrentExchangeFileAbsPath())]
            );
        }

        RootProcessStarter::successResponse();
        Logger::logProtocol('success');
    }
}
