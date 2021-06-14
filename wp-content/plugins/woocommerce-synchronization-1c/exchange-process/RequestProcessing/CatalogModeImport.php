<?php
namespace Itgalaxy\Wc\Exchange1c\ExchangeProcess\RequestProcessing;

use Itgalaxy\Wc\Exchange1c\ExchangeProcess\ParserXml;
use Itgalaxy\Wc\Exchange1c\ExchangeProcess\ParserXml31;
use Itgalaxy\Wc\Exchange1c\ExchangeProcess\Responses\ProgressResponse;
use Itgalaxy\Wc\Exchange1c\ExchangeProcess\Responses\SuccessResponse;
use Itgalaxy\Wc\Exchange1c\ExchangeProcess\RootProcessStarter;
use Itgalaxy\Wc\Exchange1c\Includes\Helper;
use Itgalaxy\Wc\Exchange1c\Includes\Logger;

class CatalogModeImport
{
    public static function process()
    {
        $baseName = basename(RootProcessStarter::getCurrentExchangeFileAbsPath());
        $message = '';

        if (!isset($_SESSION['IMPORT_1C'])) {
            $_SESSION['IMPORT_1C'] = [];
        }

        if (!isset($_SESSION['IMPORT_1C_STEP'])) {
            $_SESSION['IMPORT_1C_STEP'] = 1;
        }

        if ((int) $_SESSION['IMPORT_1C_STEP'] === 1) {
            if (
                isset($_SESSION['IMPORT_1C']['zip_file']) &&
                file_exists($_SESSION['IMPORT_1C']['zip_file'])
            ) {
                Helper::extractArchive($_SESSION['IMPORT_1C']['zip_file']);

                $message = esc_html__('Archive unpacked', 'itgalaxy-woocommerce-1c')
                    . ' - '
                    . esc_html(basename($_SESSION['IMPORT_1C']['zip_file']));

                Logger::logProtocol($message);
            }

            $_SESSION['IMPORT_1C_STEP'] = 2;
        }

        $ignoreProcessing = apply_filters(
            'itglx_wc1c_ignore_catalog_file_processing',
            false,
            basename(RootProcessStarter::getCurrentExchangeFileAbsPath())
        );

        if ($ignoreProcessing) {
            Logger::logChanges(
                'Ignore file processing by `itglx_wc1c_ignore_catalog_file_processing',
                [basename(RootProcessStarter::getCurrentExchangeFileAbsPath())]
            );

            $_SESSION['IMPORT_1C_STEP'] = 3;
        }

        if ((int) $_SESSION['IMPORT_1C_STEP'] === 2) {
            // check requested parse file exists
            if (!file_exists(RootProcessStarter::getCurrentExchangeFileAbsPath())) {
                throw new \Exception(
                    esc_html('File not exists! - ' . basename(RootProcessStarter::getCurrentExchangeFileAbsPath()))
                );
            }

            // get version scheme
            $reader = new \XMLReader();
            $reader->open(RootProcessStarter::getCurrentExchangeFileAbsPath());
            $reader->read();
            $version = (float) $reader->getAttribute('ВерсияСхемы');
            $reader->close();
            unset($reader);

            // resolve parser base version
            if ($version < 3) {
                $ParserXml = new ParserXml();
            } else {
                $ParserXml = new ParserXml31();
            }

            $_SESSION['xmlVersion'] = $version;

            // load required image working functions
            include_once ABSPATH . 'wp-admin/includes/image.php';
            include_once ABSPATH . 'wp-admin/includes/file.php';
            include_once ABSPATH . 'wp-admin/includes/media.php';
            include_once ABSPATH . 'wp-includes/pluggable.php';

            if ($ParserXml->parse(RootProcessStarter::getCurrentExchangeFileAbsPath())) {
                $_SESSION['IMPORT_1C_STEP'] = 3;
                unset($_SESSION['IMPORT_1C']);

                if (
                    strpos($baseName, 'offers') !== false ||
                    strpos($baseName, 'rests') !== false // scheme 3.1
                ) {
                    $_SESSION['IMPORT_1C_PROCESS'] = [];
                }
            } else {
                // manual import auto progress
                if (isset($_GET['manual-1c-import']) && Helper::isUserCanWorkingWithExchange()) {
                    header('refresh:1');
                }

                if (
                    strpos($baseName, 'import') !== false &&
                    !isset($_SESSION['IMPORT_1C']['heartbeat']['Товар'])
                ) {
                    if (isset($_SESSION['IMPORT_1C']['numberOfCategories'])) {
                        $message = esc_html__('Processing groups', 'itgalaxy-woocommerce-1c')
                            . ' '
                            . $baseName
                            . '...'
                            . (int) $_SESSION['IMPORT_1C']['numberOfCategories'];
                    } else {
                        $message = "Processing {$baseName} ...";
                    }
                } else {
                    if (strpos($baseName, 'import') !== false) {
                        $count = isset($_SESSION['IMPORT_1C']['heartbeat']['Товар'])
                            ? $_SESSION['IMPORT_1C']['heartbeat']['Товар']
                            : 0;
                    } else {
                        $count = isset($_SESSION['IMPORT_1C']['heartbeat']['Предложение'])
                            ? $_SESSION['IMPORT_1C']['heartbeat']['Предложение']
                            : 0;
                    }

                    $message = esc_html__('Reading file', 'itgalaxy-woocommerce-1c') . " {$baseName}...{$count}";
                }
            }
        } else {
            $_SESSION['IMPORT_1C_STEP']++;
        }

        if ($_SESSION['IMPORT_1C_STEP'] < 3) {
            ProgressResponse::send($message);

            return;
        }

        /**
         * Success processing.
         */
        $_SESSION['IMPORT_1C_STEP'] = 1;

        SuccessResponse::send(
            esc_html__('Import file', 'itgalaxy-woocommerce-1c')
            . " {$baseName} "
            . esc_html__('completed!', 'itgalaxy-woocommerce-1c')
        );

        /**
         * Hook makes it possible to perform some of your actions when the file is processing processing.
         *
         * @since 1.84.9
         *
         * @param string $baseName The name of the file that has been processed
         */
        do_action('itglx_wc1c_exchange_catalog_import_file_processing_completed', $baseName);
    }
}
