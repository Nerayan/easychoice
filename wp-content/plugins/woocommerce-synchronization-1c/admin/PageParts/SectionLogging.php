<?php
namespace Itgalaxy\Wc\Exchange1c\Admin\PageParts;

use Itgalaxy\Wc\Exchange1c\Includes\Bootstrap;
use Itgalaxy\Wc\Exchange1c\Includes\Helper;
use Itgalaxy\Wc\Exchange1c\Includes\Logger;

class SectionLogging
{
    public static function render()
    {
        Section::header(esc_html__('Exchange logging', 'itgalaxy-woocommerce-1c'));
        ?>
        <p class="description">
            <?php
            esc_html_e(
                'Logs of exchange with 1C are recorded in this directory. If it is not available for writing and '
                . 'reading, then logging will not work.',
                'itgalaxy-woocommerce-1c'
            );
            ?>
        </p>
        <?php
        $message = Helper::existOrCreateDir(Logger::getLogPath());

        if (!$message['status']) { ?>
            <span style="<?php echo esc_attr($message['color']); ?>">
                <?php echo esc_html($message['text']); ?>
            </span>
            <?php
        } else {
            ?>
            <span style="<?php echo esc_attr($message['color']); ?>">
                <?php echo esc_html($message['text']); ?>
            </span>
            <?php
            FieldInput::render(
                [
                    'title' => esc_html__('Store for (days):', 'itgalaxy-woocommerce-1c'),
                    'type' => 'number',
                    'description' => esc_html__(
                        'Logs older will be deleted when exchanging.',
                        'itgalaxy-woocommerce-1c'
                    ),
                    'default' => 5
                ],
                'log_days'
            );
            ?>
            <hr>
            <?php
            FieldCheckbox::render(
                [
                    'title' => esc_html__('Enable logging', 'itgalaxy-woocommerce-1c'),
                    'description' => esc_html__(
                        'If enabled, when exchanging from 1C, logs of the exchange protocol are '
                        . 'recorded.',
                        'itgalaxy-woocommerce-1c'
                    )
                ],
                'enable_logs_protocol'
            );
            ?>
            <hr>
            <?php
            FieldCheckbox::render(
                [
                    'title' => esc_html__('Enable change logging', 'itgalaxy-woocommerce-1c'),
                    'description' => esc_html__(
                        'If enabled, then when exchanging from 1C, logs of changes of objects are '
                        . 'recorded.',
                        'itgalaxy-woocommerce-1c'
                    )
                ],
                'enable_logs_changes'
            );
            ?>
            <hr>
            <?php
            // check exists php-zip extension
            if (function_exists('zip_open')) {
                ?>
                <a href="<?php echo esc_url(admin_url()); ?>?itgxl-wc1c-logs-get-in-archive"
                    class="btn btn-outline-info btn-sm text-decoration-none"
                    target="_blank">
                    <?php echo esc_html__('Download in zip archive', 'itgalaxy-woocommerce-1c'); ?>
                </a>
            <?php } ?>
            <button class="btn btn-info btn-sm" type="button" data-ui-component="itglx-wc1c-ajax-clear-logs">
                <span class="text">
                    <?php echo esc_html__('Clear logs', 'itgalaxy-woocommerce-1c'); ?>
                    <span data-ui-component="itglx-wc1c-logs-count-and-size-text"></span>
                </span>
                <span class="spinner-grow spinner-grow-sm" role="status"></span>
            </button>
            <hr>
            <?php $info = \get_option(Bootstrap::OPTION_INFO_KEY, []); ?>
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
            </p>
            <p>
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
            </p>
            <?php
        }

        Section::footer();
    }
}
