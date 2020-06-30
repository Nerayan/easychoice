<?php
namespace Itgalaxy\Wc\Exchange1c\Admin\PageParts;

use Itgalaxy\Wc\Exchange1c\Includes\Bootstrap;

class FieldSelect
{
    public static function render($field, $name)
    {
        $settings = get_option(Bootstrap::OPTIONS_KEY);
        ?>
        <div>
            <label for="<?php echo esc_attr(Bootstrap::OPTIONS_KEY . '_' . $name); ?>">
                <strong><?php echo esc_html($field['title']); ?></strong>
            </label>
            <br>
            <select class="wc1c-settings-select"
                id="<?php echo esc_attr(Bootstrap::OPTIONS_KEY . '_' . $name); ?>"
                name="<?php echo esc_attr(Bootstrap::OPTIONS_KEY . '[' . $name . ']'); ?>">
                <?php
                foreach ($field['options'] as $optionValue => $optionLabel) {
                    echo '<option value="'
                        . esc_attr($optionValue)
                        . '"'
                        . (isset($settings[$name]) && $settings[$name] == $optionValue ? ' selected' : '')
                        . '>'
                        . esc_html($optionLabel)
                        . '</option>';
                }
                ?>
            </select>
            <?php if (!empty($field['description'])) { ?>
                <p class="description">
                    <?php echo esc_html($field['description']); ?>
                </p>
            <?php } ?>
        </div>
        <?php
    }
}
