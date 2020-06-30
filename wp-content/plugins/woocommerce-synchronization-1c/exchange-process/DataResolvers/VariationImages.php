<?php
namespace Itgalaxy\Wc\Exchange1c\ExchangeProcess\DataResolvers;

use Itgalaxy\Wc\Exchange1c\Includes\Helper;

class VariationImages
{
    public static function process($element, $variationID, $postAuthor = 0)
    {
        $dirName = Helper::getTempPath() . '/';
        $oldImages = get_post_meta($variationID, '_old_images', true);

        if (!is_array($oldImages)) {
            $oldImages = [];
        }

        if (isset($element->Картинка) && (string) $element->Картинка) {
            $images = [];

            foreach ($element->Картинка as $image) {
                $images[] = (string) $image;
            }

            update_post_meta($variationID, '_old_images', $images);

            // delete images that do not exist in the current set
            foreach ($oldImages as $oldImage) {
                $attachID = self::findImageByMeta($oldImage);
                $imageSrcPath = str_replace('//', '/', $dirName . $oldImage);
                $removeImage = false;

                if ($attachID && !in_array($oldImage, $images)) {
                    $removeImage = true;
                } elseif (
                    $attachID &&
                    file_exists($imageSrcPath) &&
                    sha1_file($imageSrcPath) !== get_post_meta($attachID, '_1c_image_sha_hash', true)
                ) {
                    $removeImage = true;
                }

                if ($removeImage) {
                    // clean product thumbnail if removed
                    if ((int) $attachID === (int) get_post_meta($variationID, '_thumbnail_id', true)) {
                        update_post_meta($variationID, '_thumbnail_id', '');
                    }

                    wp_delete_attachment($attachID, true);
                }
            }

            $attachmentIds = [];

            foreach ($images as $image) {
                $attachID = self::findImageByMeta($image);

                if ($attachID && !is_wp_error($attachID)) {
                    $attachmentIds[] = $attachID;
                } else {
                    $imageSrcPath = str_replace('//', '/', $dirName . $image);

                    if (file_exists($imageSrcPath)) {
                        $attachID = self::resolveImage($image, $imageSrcPath, $variationID, $postAuthor);

                        if ($attachID) {
                            $attachmentIds[] = $attachID;
                        }
                    }
                }
            }

            // distribution of the current set of images
            if (!empty($attachmentIds)) {
                foreach ($attachmentIds as $attachID) {
                    update_post_meta($variationID, '_thumbnail_id', $attachID);
                }
            }
        // the current data does not contain information about the image, but it was before, so it should be deleted
        } elseif ($oldImages) {
            self::removeImages($oldImages, $variationID);
        }
    }

    private static function resolveImage($image, $imageSrcPath, $variationID, $postAuthor = 0)
    {
        $imageSha = sha1_file($imageSrcPath);
        $wpFileType = wp_check_filetype(basename($imageSrcPath), null);

        $attachID = media_handle_sideload(
            [
                'name' => trim(str_replace(' ', '', basename($imageSrcPath))),
                'type' => $wpFileType['type'],
                'tmp_name' => $imageSrcPath,
                'error' => 0,
                'size' => filesize($imageSrcPath)
            ],
            $variationID,
            null,
            [
                'post_author' => $postAuthor
            ]
        );

        if (!$attachID || is_wp_error($attachID)) {
            return false;
        }

        update_post_meta($attachID, '_1c_image_path', $image);
        update_post_meta($attachID, '_1c_image_sha_hash', $imageSha);

        return $attachID;
    }

    private static function removeImages($oldImages, $variationID)
    {
        update_post_meta($variationID, '_old_images', []);
        update_post_meta($variationID, '_thumbnail_id', '');

        // delete a known set of images
        foreach ($oldImages as $oldImage) {
            $attachID = self::findImageByMeta($oldImage);

            if ($attachID) {
                wp_delete_attachment($attachID, true);
            }
        }
    }

    private static function findImageByMeta($value, $key = '_1c_image_path')
    {
        global $wpdb;

        $attachID = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT `post_id` FROM `{$wpdb->postmeta}` WHERE `meta_value` = %s AND `meta_key` = %s",
                (string) $value,
                (string) $key
            )
        );

        return $attachID;
    }
}
