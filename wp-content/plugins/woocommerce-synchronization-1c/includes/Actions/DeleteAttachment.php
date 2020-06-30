<?php
namespace Itgalaxy\Wc\Exchange1c\Includes\Actions;

class DeleteAttachment
{
    private static $instance = false;

    public static function getInstance()
    {
        if (!self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    private function __construct()
    {
        // https://developer.wordpress.org/reference/hooks/delete_attachment/
        add_action('delete_attachment', [$this, 'actionDeleteAttachment'], 10, 1);
    }

    public function actionDeleteAttachment($postID)
    {
        if ($postID) {
            global $wpdb;

            $termID = $wpdb->get_var(
                $wpdb->prepare(
                    "SELECT `term_id` FROM `{$wpdb->termmeta}` WHERE `meta_value` = %d AND `meta_key` = 'thumbnail_id'",
                    (int) $postID
                )
            );

            if ($termID) {
                update_term_meta((int) $termID, 'thumbnail_id', '');
            }
        }
    }
}
