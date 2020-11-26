<?php
namespace Itgalaxy\Wc\Exchange1c\Admin\AjaxActions;

use Itgalaxy\Wc\Exchange1c\Includes\Bootstrap;
use Itgalaxy\Wc\Exchange1c\Includes\Helper;

class LastRequestResponseAjaxAction
{
    private static $instance = false;

    /**
     * Returns an instance of a class or creates a new instance if it doesn't exist.
     *
     * @return LastRequestResponseAjaxAction
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
        add_action('wp_ajax_ItglxWc1cLastRequestResponse', [$this, 'actionProcessing']);
    }

    /**
     * Execute the ajax action.
     *
     * @return void
     */
    public function actionProcessing()
    {
        if (!Helper::isUserCanWorkingWithExchange()) {
            exit();
        }

        $info = \get_option(Bootstrap::OPTION_INFO_KEY, []);
        ?>
        <p>
            <strong><?php esc_html_e('Last request from 1C', 'itgalaxy-woocommerce-1c'); ?>:</strong>
            <?php
            echo empty($info['last_request'])
                ? esc_html__('No requests have been made yet', 'itgalaxy-woocommerce-1c')
                : esc_html(
                    $info['last_request']['date']
                    . ' | '
                    . $info['last_response']['user']
                    . ' | '
                    . $info['last_request']['query']
                );
            ?>
            <span class="spinner-grow spinner-grow-sm d-none" role="status"></span>
        </p>
        <p class="mb-0">
            <strong><?php esc_html_e('Last response for 1C', 'itgalaxy-woocommerce-1c'); ?>:</strong>
            <?php
            echo empty($info['last_response'])
                ? esc_html__('No response has been sent yet', 'itgalaxy-woocommerce-1c')
                : esc_html($info['last_response']['date']
                    . ' | '
                    . $info['last_response']['user']
                    . ' | '
                    . $info['last_response']['query']
                    . ' | '
                    . $info['last_response']['message']
                );
            ?>
            <span class="spinner-grow spinner-grow-sm d-none" role="status"></span>
        </p>
        <?php
        exit();
    }
}
