<?php
namespace Itgalaxy\Wc\Exchange1c\Admin\AjaxActions;

use Itgalaxy\Wc\Exchange1c\Includes\Helper;
use Itgalaxy\Wc\Exchange1c\Includes\Logger;

class ItglxWc1cLogsCountAndSize
{
    private static $instance = false;

    public static function getInstance()
    {
        if (!self::$instance) {
            self::$instance = new self;
        }
        return self::$instance;
    }

    private function __construct()
    {
        // https://developer.wordpress.org/reference/hooks/wp_ajax__requestaction/
        add_action('wp_ajax_itglxWc1cLogsCountAndSize', [$this, 'actionProcessing']);
    }

    public function actionProcessing()
    {
        if (!Helper::isUserCanWorkingWithExchange()) {
            exit();
        }

        wp_send_json_success(
            [
                'message' => $this->fileInfo()
            ]
        );
    }

    private function fileInfo()
    {
        $files = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator(Logger::getLogPath()),
            \RecursiveIteratorIterator::LEAVES_ONLY
        );
        $size = 0;
        $countFiles = 0;

        foreach ($files as $name => $file) {
            if ($file->isDir()) {
                continue;
            }

            $filePath = $file->getRealPath();

            $size += filesize($filePath);
            $countFiles++;
        }

        if ($countFiles === 0) {
            return esc_html__('(no files)', 'itgalaxy-woocommerce-1c');
        }

        return sprintf(
            esc_html__(
                '(files - %d, size - %s MB)',
                'itgalaxy-woocommerce-1c'
            ),
            $countFiles,
            round($size / 1024 / 1024, 2) // show value in megabytes
        );
    }
}
