<?php
namespace Itgalaxy\Wc\Exchange1c\Admin\AjaxActions;

use Itgalaxy\Wc\Exchange1c\Includes\Helper;

/**
 * Handling ajax request to clear the temporary directory
 */
class ClearTempAjaxAction
{
    private static $instance = false;

    /**
     * Returns an instance of a class or creates a new instance if it doesn't exist.
     *
     * @return ClearTempAjaxAction
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
     * @return void
     */
    private function __construct()
    {
        add_action('wp_ajax_itglxWc1cClearTemp', [$this, 'actionProcessing']);
    }

    /**
     * Action callback. Clearing the temporary directory.
     *
     * @link https://developer.wordpress.org/reference/functions/wp_send_json_success/
     * @link https://developer.wordpress.org/reference/functions/wp_send_json_error/
     * @return void
     */
    public function actionProcessing()
    {
        if (!Helper::isUserCanWorkingWithExchange()) {
            exit();
        }

        $tempPath =  Helper::getTempPath();

        if (!file_exists($tempPath)) {
            wp_send_json_success(
                [
                    'message' => esc_html__('Successfully cleared', 'itgalaxy-woocommerce-1c')
                ]
            );
        }

        if (!is_writable($tempPath)) {
            wp_send_json_error(
                [
                    'message' => esc_html__('Not available for write', 'itgalaxy-woocommerce-1c')
                ]
            );
        }

        Helper::removeDir($tempPath);
        mkdir($tempPath, 0755, true);

        wp_send_json_success(
            [
                'message' => esc_html__('Successfully cleared', 'itgalaxy-woocommerce-1c')
            ]
        );
    }
}
