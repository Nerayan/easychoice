<?php
namespace Itgalaxy\Wc\Exchange1c\Admin\PageParts;

class Section
{
    public static function header($title)
    {
         ?>
        <div class="postbox wc1c-padding">
            <h3 class="hndle">
                <?php echo esc_html($title); ?>
            </h3>
            <div class="inside">
        <?php
    }

    public static function render($section)
    {
        self::header($section['title']);

        if (!empty($section['subtitle'])) {
            ?>
            <p class="description">
                <?php echo esc_html($section['subtitle']); ?>
            </p>
            <hr>
            <?php
        }

        if (isset($section['tabs'])) {
            ?>
            <div data-ui-component="tabs"><ul>
            <?php
            foreach ($section['tabs'] as $tab) {
                echo '<li>'
                    . '<a href="#'
                    . esc_attr($tab['id'])
                    . '">'
                    . esc_html($tab['title'])
                    . '</a></li>';
            }

            echo '</ul>';

            foreach ($section['tabs'] as $tab) {
                echo '<div id="'
                    . esc_attr($tab['id'])
                    . '">';

                self::body($tab['fields']);

                echo '</div>';
            }
            ?>
            </div>
            <?php
        } else {
            self::body($section['fields']);
        }

        self::footer();
    }

    public static function body($fields)
    {
        foreach ($fields as $name => $field) {
            switch ($field['type']) {
                case 'checkbox':
                    FieldCheckbox::render($field, $name);
                    break;
                case 'select':
                    FieldSelect::render($field, $name);
                    break;
                case 'select2':
                    FieldSelect2::render($field, $name);
                    break;
                case 'text':
                case 'password':
                case 'number':
                case 'datetime-local':
                    FieldInput::render($field, $name);
                    break;
                case 'send_orders_status_mapping':
                    FieldSendOrdersStatusMapping::render($field, $name);
                    break;
                case 'textarea':
                    FieldTextArea::render($field, $name);
                    break;
                default:
                    // Nothing
                    break;
            }

            echo end($fields) !== $field ? '<hr>' : '';
        }
    }

    public static function footer()
    {
            ?>
            </div>
        </div>
        <?php
    }
}
