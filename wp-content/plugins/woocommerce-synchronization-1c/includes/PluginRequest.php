<?php
namespace Itgalaxy\Wc\Exchange1c\Includes;

class PluginRequest
{
    public static function call($action, $code = '')
    {
        if (empty($code)) {
            $code = \get_site_option(Bootstrap::PURCHASE_CODE_OPTIONS_KEY, '');
        }

        $response = \wp_remote_post(
            'https://envato.itgalaxy.company/envato/plugin-request',
            [
                'body' => [
                    'purchaseCode' => $code,
                    'itemID' => '24768513',
                    'version' => ITGALAXY_WC_1C_PLUGIN_VERSION,
                    'action' => $action,
                    'domain' => \network_site_url()
                ],
                'timeout' => 20
            ]
        );

        if (!\is_wp_error($response)) {
            $response = json_decode(\wp_remote_retrieve_body($response));
        }

        return $response;
    }
}
