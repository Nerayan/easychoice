<?php
namespace Itgalaxy\Wc\Exchange1c\Admin\PageParts;

use Itgalaxy\Wc\Exchange1c\Includes\Bootstrap;

class FieldSendOrdersStatusMapping
{
    public static function render($field, $name)
    {
        $settings = get_option(Bootstrap::OPTIONS_KEY);
        ?>
        <div>
            <h4>
                <?php echo esc_html($field['title']); ?>
            </h4>
            <table>
                <?php
                $mappingStatuses = isset($settings[$name]) ? $settings[$name] : [];

                foreach (wc_get_order_statuses() as $status => $label) {
                    $value = str_replace('wc-', '', $status);
                    ?>
                    <tr>
                        <th>
                            <?php echo esc_html($label); ?>
                        </th>
                        <td>
                            <input type="text"
                                id="<?php echo esc_attr(Bootstrap::OPTIONS_KEY . '_' . $name . '_' . $value); ?>"
                                name="<?php echo esc_attr(Bootstrap::OPTIONS_KEY . '[' . $name . '][' . $value . ']'); ?>"
                                value="<?php echo isset($mappingStatuses[$value]) ? esc_attr($mappingStatuses[$value]) : ''; ?>">
                        </td>
                        <td>
                            <small class="description">
                            <?php
                            esc_html_e(
                                'If not specified, then the default value will be used - ',
                                'itgalaxy-woocommerce-1c'
                            );
                            echo '<strong>' . esc_html($value) . '</strong>';
                            ?>
                            </small>
                        </td>
                    </tr>
                    <?php
                }
                ?>
            </table>
        </div>
        <?php
    }
}
