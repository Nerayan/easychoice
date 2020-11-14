<?php
namespace Itgalaxy\Wc\Exchange1c\Admin\Other;

use Itgalaxy\Wc\Exchange1c\Includes\Helper;

class AdminNoticeIfNotVerified
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
        // https://developer.wordpress.org/reference/hooks/admin_notices/
        add_action('admin_notices', [$this, 'notice']);
    }

    public function notice()
    {
        if (Helper::isVerify()) {
            return;
        }

        echo sprintf(
            '<div class="notice notice-error"><p><strong>%1$s</strong>: %2$s <a href="%3$s">%4$s</a></p></div>',
            esc_html__('1C Data Exchange', 'itgalaxy-woocommerce-1c'),
            esc_html__(
                'Please verify the purchase code on the plugin settings page - ',
                'itgalaxy-woocommerce-1c'
            ),
            esc_url(admin_url() . 'admin.php?page=wc-itgalaxy-1c-exchange-settings#wc1c-license-verify'),
            esc_html__('open', 'itgalaxy-woocommerce-1c')
        );
    }
}
