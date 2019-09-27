<?php

// Copyright (c) 2016 Interfacelab LLC. All rights reserved.
//
// Released under the GPLv3 license
// http://www.gnu.org/licenses/gpl-3.0.html
//
// **********************************************************************
// This program is distributed in the hope that it will be useful, but
// WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
// **********************************************************************

namespace ILAB\MediaCloud\Tools\Integrations\PlugIns;

use ILAB\MediaCloud\Storage\StorageManager;
use ILAB\MediaCloud\Storage\StorageSettings;
use ILAB\MediaCloud\Tools\Glide\GlideTool;
use ILAB\MediaCloud\Tools\Imgix\ImgixTool;
use ILAB\MediaCloud\Tools\Storage\StorageTool;
use ILAB\MediaCloud\Tools\ToolsManager;
use function ILAB\MediaCloud\Utilities\gen_uuid;

if (!defined( 'ABSPATH')) { header( 'Location: /'); die; }

class UltimateMemberIntegration {
	private $currentS3Info = null;

	private $imgixEnabled = false;
	private $dynamicEnabled = false;

	public function __construct() {
		$this->imgixEnabled = apply_filters('media-cloud/imgix/enabled', false);
		$this->dynamicEnabled = apply_filters('media-cloud/glide/enabled', false);

		add_filter('wp_handle_upload', [$this, 'handleUpload'], 10000, 2);
		add_filter('media-cloud/storage/add-upload-filter', function($addFilter) {
			if (isset($_POST['action']) && ($_POST['action'] == 'um_imageupload')) {
				return false;
			}

			return $addFilter;
		});

		add_action('um_upload_image_process__cover_photo', [$this, 'resizeCoverPhoto'], 1000, 6);
		add_action('um_upload_image_process__profile_photo', [$this, 'resizeProfilePhoto'], 1000, 6);

		add_filter('um_user_cover_photo_uri__filter', [$this, 'filterCoverPhotoUri'], 1000, 3);
		add_filter('um_user_avatar_url_filter', [$this, 'filterUserAvatarUrl'], 1000, 3);
	}

	private function generateUrl($umKey, $s3Info) {
		if ($this->imgixEnabled) {
			/** @var ImgixTool $imgixTool */
			$imgixTool = ToolsManager::instance()->tools['imgix'];

			$params = apply_filters('media-cloud/integrations/ultimate-member/imgix-params', $umKey, []);

			return $imgixTool->urlForStorageMedia($s3Info['key'], $params);
		} if ($this->dynamicEnabled) {
			/** @var GlideTool $glideTool */
			$glideTool = ToolsManager::instance()->tools['glide'];

			$params = apply_filters('media-cloud/integrations/ultimate-member/glide-params', $umKey, []);

			return $glideTool->urlForStorageMedia($s3Info['key'], $params);
		} else {
			/** @var StorageTool $storageTool */
			$storageTool = ToolsManager::instance()->tools['storage'];

			if($storageTool->client()->usesSignedURLs()) {
				$url = $storageTool->client()->url($s3Info['key']);
				if(!empty(StorageSettings::cdn())) {
					$cdnScheme = parse_url(StorageSettings::cdn(), PHP_URL_SCHEME);
					$cdnHost = parse_url(StorageSettings::cdn(), PHP_URL_HOST);

					$urlScheme = parse_url($url, PHP_URL_SCHEME);
					$urlHost = parse_url($url, PHP_URL_HOST);

					return str_replace("{$urlScheme}://{$urlHost}", "{$cdnScheme}://{$cdnHost}", $url);
				} else {
					return $url;
				}
			} else if(!empty(StorageSettings::cdn())) {
				return StorageSettings::cdn() . '/' . $s3Info['key'];
			}

			return $s3Info['url'];
		}
	}

	public function filterUserAvatarUrl($avatarUrl, $userId, $data) {
		$this->currentS3Info = get_user_meta($userId, 'um_s3_info', true);

		if (!empty($this->currentS3Info) && isset($this->currentS3Info['profile_photo'])) {
			return $this->generateUrl('profile_photo', $this->currentS3Info['profile_photo']);
		}

		return $avatarUrl;
	}

	public function filterCoverPhotoUri($coverUri, $isDefault, $attrs) {
		if (!empty($this->currentS3Info) && isset($this->currentS3Info['cover_photo'])) {
			return $this->generateUrl('cover_photo', $this->currentS3Info['cover_photo']);
		}

		return $coverUri;
	}

	public function handleUpload($upload, $sideload) {
		if (isset($_POST['action']) && ($_POST['action'] == 'um_imageupload')) {
			$umKey = $_POST['key'];

			/** @var StorageTool $storageTool */
			$storageTool = ToolsManager::instance()->tools['storage'];

			if (file_exists($upload['file'])) {
				if (preg_match('/ultimatemember\/([0-9]+)\//m', $upload['file'], $matches)) {
					$userId = intval($matches[1]);
					if (!empty($userId)) {
						$key = str_replace(trailingslashit(WP_CONTENT_DIR), '', $upload['file']);
						if (strpos($key, 'uploads'.DIRECTORY_SEPARATOR) === 0) {
							$key = str_replace('uploads'.DIRECTORY_SEPARATOR, '', $key);
						}

						$url = $storageTool->client()->upload($key, $upload['file'], StorageSettings::privacy());

						$s3data = get_user_meta($userId, 'um_s3_info', true);
						if (empty($s3data)) {
							$s3data = [];
						}

						$s3data[$umKey] = [
							'url' => $url,
							'key' => $key,
							'driver' => StorageManager::driver()
						];

						update_user_meta($userId, 'um_s3_info', $s3data);
					}

				}
			}
		}

		return $upload;
	}

	private function handleResize($umKey, $image_path, $src, $key, $user_id, $coord, $crop) {
		if (preg_match('/ultimatemember\/([0-9]+)\//m', $image_path, $matches)) {
			$userId = intval($matches[1]);
			if (!empty($userId)) {
				$key = str_replace(trailingslashit(WP_CONTENT_DIR), '', $image_path);
				if (strpos($key, 'uploads'.DIRECTORY_SEPARATOR) === 0) {
					$key = str_replace('uploads'.DIRECTORY_SEPARATOR, '', $key);
				}

				$basename = basename($image_path);
				$rootKey = str_replace($basename, '', $key);
				$key = $rootKey.gen_uuid(8).'/'.$basename;

				/** @var StorageTool $storageTool */
				$storageTool = ToolsManager::instance()->tools['storage'];
				$url = $storageTool->client()->upload($key, $image_path, StorageSettings::privacy());

				$s3data = get_user_meta($userId, 'um_s3_info', true);
				if (empty($s3data)) {
					$s3data = [];
				}

				$s3data[$umKey] = [
					'url' => $url,
					'key' => $key,
					'driver' => StorageManager::driver()
				];

				update_user_meta($userId, 'um_s3_info', $s3data);
			}
		}
	}

	public function resizeCoverPhoto($image_path, $src, $key, $user_id, $coord, $crop) {
		$this->handleResize('cover_photo', $image_path, $src, $key, $user_id, $coord, $crop);
	}

	public function resizeProfilePhoto($image_path, $src, $key, $user_id, $coord, $crop) {
		$this->handleResize('profile_photo', $image_path, $src, $key, $user_id, $coord, $crop);
	}
}