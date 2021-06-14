<?php
namespace Itgalaxy\Wc\Exchange1c\ExchangeProcess\DataResolvers;

use Itgalaxy\Wc\Exchange1c\Includes\Bootstrap;
use Itgalaxy\Wc\Exchange1c\Includes\Helper;
use Itgalaxy\Wc\Exchange1c\Includes\Logger;

class VariationImages
{
    public static function process($element, $variationID, $postAuthor = 0)
    {
        $dirName = apply_filters('itglx_wc1c_root_image_directory', Helper::getTempPath() . '/');
        $oldImages = get_post_meta($variationID, '_old_images', true);

        if (!is_array($oldImages)) {
            $oldImages = [];
        }

        $attachmentIds = [];

        if (isset($element->Картинка) && (string) $element->Картинка) {
            $images = [];

            foreach ($element->Картинка as $image) {
                $images[] = (string) $image;
            }

            update_post_meta($variationID, '_old_images', $images);

            // delete images that do not exist in the current set
            foreach ($oldImages as $oldImage) {
                $attachID = self::findImageByMeta($oldImage);
                $imageSrcPath = self::imageSrcResultPath($dirName, $oldImage, $variationID, $element);
                $removeImage = false;

                if ($attachID && !in_array($oldImage, $images, true)) {
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

                    Logger::logChanges(
                        '(image variation) Removed (is changed) image for ID - ' . $variationID,
                        [$attachID]
                    );
                }
            }

            foreach ($images as $image) {
                $attachID = self::findImageByMeta($image);

                if ($attachID && !is_wp_error($attachID)) {
                    $attachmentIds[] = $attachID;
                } else {
                    $imageSrcPath = self::imageSrcResultPath($dirName, $image, $variationID, $element);

                    if (file_exists($imageSrcPath)) {
                        $attachID = self::resolveImage($image, $imageSrcPath, $variationID, $postAuthor);

                        if ($attachID) {
                            $attachmentIds[] = $attachID;

                            Logger::logChanges('(image variation) Added image for ID - ' . $variationID, [$attachID]);
                        }
                    }
                }
            }

            // distribution of the current set of images
            if (!empty($attachmentIds)) {
                /**
                 * Take the first ID to set it as a variation thumbnail.
                 */
                $attachID = reset($attachmentIds);

                update_post_meta($variationID, '_thumbnail_id', $attachID);
                Logger::logChanges('(image variation) Set thumbnail image for ID - ' . $variationID, [$attachID]);
            }
        // the current data does not contain information about the image, but it was before, so it should be deleted
        } elseif ($oldImages) {
            Logger::logChanges(
                '(image variation) Removed images (the current data does not contain information) for ID - '
                . $variationID,
                [get_post_meta($variationID, '_id_1c', true)]
            );

            self::removeImages($oldImages, $variationID);
        }

        /**
         * Fires after image processing for variation.
         *
         * Fires in any case, even if the resulting image set is empty as a result of processing. If the set was
         * not empty earlier, then based on the empty data, it is possible to make decisions about the necessary
         * cleaning actions.
         *
         * @since 1.93.0
         *
         * @param int $variationID Product variation id.
         * @param int[] $attachmentIds The array contains the IDs of all images. If the set is not empty, then the
         *                              first element is already set as a variation image.
         * @param \SimpleXMLElement $element 'Предложение' node object.
         */
        do_action('itglx_wc1c_product_variation_images', $variationID, $attachmentIds, $element);
    }

    private static function resolveImage($image, $imageSrcPath, $variationID, $postAuthor = 0)
    {
        $settings = get_option(Bootstrap::OPTIONS_KEY, []);
        $imageSha = sha1_file($imageSrcPath);
        $wpFileType = wp_check_filetype(basename($imageSrcPath), null);

        $postData = [
            'post_author' => $postAuthor
        ];

        if (!empty($settings['write_product_name_to_attachment_title'])) {
            $postData['post_title'] = get_post_field('post_title', $variationID);
        }

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
            $postData
        );

        if (!$attachID || is_wp_error($attachID)) {
            return false;
        }

        if (!empty($settings['write_product_name_to_attachment_attribute_alt'])) {
            update_post_meta($attachID, '_wp_attachment_image_alt', get_post_field('post_title', $variationID));
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
                "SELECT `meta`.`post_id` FROM `{$wpdb->postmeta}` as `meta`
                 INNER JOIN `{$wpdb->posts}` as `posts` ON `meta`.`post_id` = `posts`.`ID`
                 WHERE `meta`.`meta_value` = %s AND `meta`.`meta_key` = %s",
                (string) $value,
                (string) $key
            )
        );

        return $attachID;
    }

    private static function imageSrcResultPath($rootImagePath, $imageRelativePath, $variationID, $element)
    {
        $imageSrcPath = $rootImagePath
            . apply_filters(
                'itglx_wc1c_image_path_from_xml',
                $imageRelativePath,
                $variationID,
                $element
            );

        return str_replace('//', '/', $imageSrcPath);
    }
}
