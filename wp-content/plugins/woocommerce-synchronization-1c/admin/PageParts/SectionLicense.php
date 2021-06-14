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
            $response = Bootstrap::$common->requester->code(
                isset($_POST['verify']) ? 'code_activate' : 'code_deactivate',
                trim(wp_unslash($_POST['purchase-code']))
            );

            if ($response['state'] == 'successCheck') {
                echo sprintf(
                    '<div class="updated notice notice-success is-dismissible"><p>%s</p></div>',
                    esc_html($response['message'])
                );
            } elseif ($response['message']) {
                echo sprintf(
                    '<div class="error notice notice-error is-dismissible"><p>%s</p></div>',
                    esc_html($response['message'])
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
        <form method="post" action="#" id="wc1c-license-verify">
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
