<?php
namespace Itgalaxy\Wc\Exchange1c\Admin\AjaxActions;

use Itgalaxy\Wc\Exchange1c\Includes\Helper;
use Itgalaxy\Wc\Exchange1c\Includes\Logger;

class ItglxWc1cClearLogs
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
        add_action('wp_ajax_itglxWc1cClearLogs', [$this, 'actionProcessing']);
    }

    public function actionProcessing()
    {
        if (!Helper::isUserCanWorkingWithExchange()) {
            exit();
        }

        $logsPath =  Logger::getLogPath();

        if (!file_exists($logsPath)) {
            wp_send_json_success(
                [
                    'message' => esc_html__('Successfully cleared', 'itgalaxy-woocommerce-1c')
                ]
            );
        }

        if (!is_writable($logsPath)) {
            wp_send_json_error(
                [
                    'message' => esc_html__('Not available for write', 'itgalaxy-woocommerce-1c')
                ]
            );
        }

        Helper::removeDir($logsPath);
        mkdir($logsPath, 0755, true);

        wp_send_json_success(
            [
                'message' => esc_html__('Successfully cleared', 'itgalaxy-woocommerce-1c')
            ]
        );
    }
}
