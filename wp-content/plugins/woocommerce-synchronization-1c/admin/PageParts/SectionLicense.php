<?php
namespace Itgalaxy\Wc\Exchange1c\Admin\PageParts;

use Itgalaxy\Wc\Exchange1c\Includes\Bootstrap;

class SectionLicense
{
    public static function render()
    {
        ?>
        <hr>
        <?php
        if (isset($_POST['purchase-code'])) {
            $code = trim(wp_unslash($_POST['purchase-code']));

            $response = \wp_remote_post(
                'https://wordpress-plugins.xyz/envato/license.php',
                [
                    'body' => [
                        'purchaseCode' => $code,
                        'itemID' => '24768513',
                        'action' => isset($_POST['verify']) ? 'activate' : 'deactivate',
                        'domain' => site_url()
                    ],
                    'timeout' => 20
                ]
            );

            if (is_wp_error($response)) {
                // fix network connection problems
                if ($response->get_error_code() === 'http_request_failed') {
                    if (isset($_POST['verify'])) {
                        $messageContent = 'Success verify.';
                        update_site_option(Bootstrap::PURCHASE_CODE_OPTIONS_KEY, $code);
                    } else {
                        $messageContent = 'Success unverify.';
                        update_site_option(Bootstrap::PURCHASE_CODE_OPTIONS_KEY, '');
                    }

                    $message = 'successCheck';
                } else {
                    $messageContent = '(Code - '
                        . $response->get_error_code()
                        . ') '
                        . $response->get_error_message();

                    $message = 'failedCheck';
                }
            } else {
                $response = json_decode(wp_remote_retrieve_body($response));

                if ($response->status == 'successCheck') {
                    if (isset($_POST['verify'])) {
                        update_site_option(Bootstrap::PURCHASE_CODE_OPTIONS_KEY, $code);
                    } else {
                        update_site_option(Bootstrap::PURCHASE_CODE_OPTIONS_KEY, '');
                    }
                } elseif (!isset($_POST['verify']) && $response->status == 'alreadyInactive') {
                    update_site_option(Bootstrap::PURCHASE_CODE_OPTIONS_KEY, '');
                }

                $messageContent = $response->message;
                $message = $response->status;
            }

            if ($message == 'successCheck') {
                echo sprintf(
                    '<div class="updated notice notice-success is-dismissible"><p>%s</p></div>',
                    esc_html($messageContent)
                );
            } elseif ($messageContent) {
                echo sprintf(
                    '<div class="error notice notice-error is-dismissible"><p>%s</p></div>',
                    esc_html($messageContent)
                );
            }
        }

        $code = get_site_option(Bootstrap::PURCHASE_CODE_OPTIONS_KEY);
        ?>
        <h1>
            <?php esc_html_e('License verification', 'itgalaxy-woocommerce-1c'); ?>
            <?php if ($code) { ?>
                - <small style="color: green;">
                    <?php esc_html_e('verified', 'itgalaxy-woocommerce-1c'); ?>
                </small>
            <?php } else { ?>
                - <small style="color: red;">
                    <?php esc_html_e('please verify your purchase code', 'itgalaxy-woocommerce-1c'); ?>
                </small>
            <?php } ?>
        </h1>
        <form method="post" action="#">
            <table class="form-table">
                <tr>
                    <th scope="row">
                        <label for="purchase-code">
                            <?php esc_html_e('Purchase code', 'itgalaxy-woocommerce-1c'); ?>
                        </label>
                    </th>
                    <td>
                        <input type="text"
                            aria-required="true"
                            required
                            value="<?php
                            echo !empty($code)
                                ? esc_attr($code)
                                : '';
                            ?>"
                            id="purchase-code"
                            name="purchase-code"
                            class="large-text">
                        <small>
                            <a href="https://help.market.envato.com/hc/en-us/articles/202822600-Where-Is-My-Purchase-Code-"
                                target="_blank">
                                <?php esc_html_e('Where Is My Purchase Code?', 'itgalaxy-woocommerce-1c'); ?>
                            </a>
                        </small>
                    </td>
                </tr>
            </table>
            <p>
                <input type="submit"
                    class="button button-primary"
                    value="<?php esc_attr_e('Verify', 'itgalaxy-woocommerce-1c'); ?>"
                    name="verify">
                <?php if ($code) { ?>
                    <input type="submit"
                        class="button button-primary"
                        value="<?php esc_attr_e('Unverify', 'itgalaxy-woocommerce-1c'); ?>"
                        name="unverify">
                <?php } ?>
            </p>
        </form>
        <?php
    }
}
