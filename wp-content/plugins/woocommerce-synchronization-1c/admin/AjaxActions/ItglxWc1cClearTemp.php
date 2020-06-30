<?php
namespace Itgalaxy\Wc\Exchange1c\Admin\AjaxActions;

use Itgalaxy\Wc\Exchange1c\Includes\Helper;

class ItglxWc1cClearTemp
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
        add_action('wp_ajax_itglxWc1cClearTemp', [$this, 'actionProcessing']);
    }

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
