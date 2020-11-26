<?php
namespace Itgalaxy\Wc\Exchange1c\Admin\AjaxActions;

use Itgalaxy\Wc\Exchange1c\Includes\Helper;
use Itgalaxy\Wc\Exchange1c\Includes\Logger;

/**
 * Handling ajax request to clear the logs directory
 */
class ClearLogsAjaxAction
{
    private static $instance = false;

    /**
     * Returns an instance of a class or creates a new instance if it doesn't exist.
     *
     * @return ClearLogsAjaxAction
     */
    public static function getInstance()
    {
        if (!self::$instance) {
            self::$instance = new self;
        }

        return self::$instance;
    }

    /**
     * Create new instance.
     *
     * @link https://developer.wordpress.org/reference/functions/add_action/
     * @link https://developer.wordpress.org/reference/hooks/wp_ajax__requestaction/
     *
     * @return void
     */
    private function __construct()
    {
        add_action('wp_ajax_itglxWc1cClearLogs', [$this, 'actionProcessing']);
    }

    /**
     * Action callback. Clearing the logs directory.
     *
     * @link https://developer.wordpress.org/reference/functions/wp_send_json_success/
     * @link https://developer.wordpress.org/reference/functions/wp_send_json_error/
     *
     * @return void
     */
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
