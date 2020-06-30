<?php
namespace Itgalaxy\Wc\Exchange1c\Admin\PageParts;

use Itgalaxy\Wc\Exchange1c\Includes\Bootstrap;

class FieldInput
{
    public static function render($field, $name)
    {
        $settings = get_option(Bootstrap::OPTIONS_KEY);
        $default = isset($field['default']) ? $field['default'] : '';

        if ($name === 'sync_script_address') {
            ?>
            <div class="col-auto">
                <label for="<?php echo esc_attr(Bootstrap::OPTIONS_KEY . '_' . $name); ?>">
                    <strong><?php echo esc_html($field['title']); ?></strong>
                </label>
                <div class="input-group input-group-sm">
                    <div class="input-group-prepend copy-to-clipboard"
                        title="<?php esc_attr_e('Copy to clipboard', 'itgalaxy-woocommerce-1c'); ?>"
                        data-message="<?php
                        esc_attr_e('Link copied to clipboard', 'itgalaxy-woocommerce-1c');
                        ?>"
                        data-clipboard-target="#<?php echo esc_attr(Bootstrap::OPTIONS_KEY . '_' . $name); ?>">
                        <div class="btn btn-sm btn-secondary input-group-text">
                            <?php esc_html_e('Copy', 'itgalaxy-woocommerce-1c'); ?>
                        </div>
                    </div>
                    <input type="url"
                        class="form-control form-control-sm"
                        id="<?php echo esc_attr(Bootstrap::OPTIONS_KEY . '_' . $name); ?>"
                        <?php echo isset($field['readonly']) ? ' readonly' : ''; ?>
                        value="<?php echo isset($settings[$name]) ? esc_attr($settings[$name]) : $default; ?>">
                    <?php if (!empty($field['description'])) { ?>
                        <p class="description">
                            <?php echo esc_html($field['description']); ?>
                        </p>
                    <?php } ?>
                </div>
                <?php
                if (!empty($field['content'])) {
                    echo '<hr>' . wp_kses_post($field['content']);
                }
                ?>
            </div>
            <?php
        } else {
            ?>
            <div>
                <label for="<?php echo esc_attr(Bootstrap::OPTIONS_KEY . '_' . $name); ?>">
                    <strong><?php echo esc_html($field['title']); ?></strong>
                </label>
                <input type="<?php echo isset($field['type']) ? esc_attr($field['type']) : 'text'; ?>"
                    class="large-text"
                    id="<?php echo esc_attr(Bootstrap::OPTIONS_KEY . '_' . $name); ?>"
                    name="<?php echo esc_attr(Bootstrap::OPTIONS_KEY . '[' . $name . ']'); ?>"
                    <?php echo isset($field['readonly']) ? ' readonly disabled ' : ''; ?>
                    value="<?php echo isset($settings[$name]) ? trim(esc_attr($settings[$name])) : $default; ?>">
                <?php if (!empty($field['description'])) { ?>
                    <p class="description">
                        <?php echo esc_html($field['description']); ?>
                    </p>
                <?php } ?>
                <?php
                if (!empty($field['content'])) {
                    echo '<hr>' . wp_kses_post($field['content']);
                }
                ?>
            </div>
            <?php
        }
    }
}
