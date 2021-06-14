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

namespace MediaCloud\Plugin\Tools\Integrations\PlugIns\BuddyPress;

use MediaCloud\Plugin\Tools\Integrations\PlugIns\BuddyPress\CLI\BuddyPressCommands;
use MediaCloud\Plugin\Tools\Integrations\PlugIns\BuddyPress\Tasks\BuddyPressDeleteTask;
use MediaCloud\Plugin\Tools\Integrations\PlugIns\BuddyPress\Tasks\BuddyPressMigrateTask;
use MediaCloud\Plugin\Tools\Storage\StorageGlobals;
use MediaCloud\Plugin\Tools\Storage\StorageToolSettings;
use MediaCloud\Plugin\Tasks\TaskManager;
use MediaCloud\Plugin\Tools\Storage\StorageTool;
use MediaCloud\Plugin\Tools\ToolsManager;
use MediaCloud\Plugin\Utilities\Logging\Logger;
use MediaCloud\Plugin\Utilities\NoticeManager;

if (!defined( 'ABSPATH')) { header( 'Location: /'); die; }

class BuddyPressIntegration {
	private $coverSettings = null;
	private $previousCallback = null;

	/** @var BuddyPressSettings  */
	private $settings = null;

	public function __construct() {

		$this->settings = BuddyPressSettings::instance();

		if (ToolsManager::instance()->toolEnabled('storage') && $this->settings->enabled) {
			BuddyPressMap::init();

			if (!empty(StorageToolSettings::instance()->deleteOnUpload) && empty($this->settings->deleteUploads)) {
				$adminUrl = admin_url('admin.php?page=media-cloud-settings-integrations#buddypress');
				NoticeManager::instance()->displayAdminNotice('warning', "You have <strong>Delete on Upload</strong> enabled in Cloud Storage Settings, but you don't have <strong>Delete Uploads</strong> enabled in <a href='{$adminUrl}'>BuddyPress Integration Settings</a>.  BuddyPress uploads won't be deleted until this setting is enabled.", true, 'buddypress-delete-warning');

			}

			TaskManager::registerTask(BuddyPressDeleteTask::class);
			TaskManager::registerTask(BuddyPressMigrateTask::class);

			if (defined( 'WP_CLI' ) && class_exists('\WP_CLI')) {
				BuddyPressCommands::Register();
			}

			add_filter('replace_aws_img_urls_from_activities', function($dontReplace, $activity) {
				return true;
			}, PHP_INT_MAX, 2);

			add_filter('bp_core_fetch_avatar', [$this, 'fetchCoreAvatar'], PHP_INT_MAX, 9);
			add_filter('bp_core_fetch_avatar_url', [$this, 'fetchCoreAvatarURL'], PHP_INT_MAX, 2);

			add_filter('bp_before_members_cover_image_settings_parse_args', [$this, 'coverImageSettings'], PHP_INT_MAX - 1, 1);
			add_filter('bp_before_groups_cover_image_settings_parse_args', [$this, 'coverImageSettings'], PHP_INT_MAX - 1, 1);
			add_filter('bp_before_xprofile_cover_image_settings_parse_args', [$this, 'coverImageSettings'], PHP_INT_MAX - 1, 1);

			add_filter('bp_attachments_pre_get_attachment', [$this, 'getBuddyPressAttachment'], 10, 2);
		}
	}

	//region Load BuddyPress
	private function loadBuddyPress() {
		if (!function_exists('bp_groups_default_avatar')) {
			require_once \BuddyPress::instance()->plugin_dir . 'bp-groups/bp-groups-filters.php';
		}

		if (!function_exists('bp_get_current_group_id')) {
			require_once \BuddyPress::instance()->plugin_dir . 'bp-groups/bp-groups-template.php';
		}

		if (!function_exists('groups_get_current_group')) {
			require_once \BuddyPress::instance()->plugin_dir . 'bp-groups/bp-groups-functions.php';
		}
	}
	//endregion

	//region Cover Images

	public function coverImageCallback($params = []) {
		Logger::info("Start coverImageCallback", [], __METHOD__, __LINE__);

		$result = '';

		if (!empty($this->previousCallback) && is_callable($this->previousCallback)) {
			$result = call_user_func($this->previousCallback, $params);
		}

		if (empty($result) || empty($params) || !isset($params['cover_image'])) {
			return $result;
		}

		$url = $params['cover_image'];

		global $bp;
		if ($bp->current_component === 'front') {
			$objectKey = "cover_profile_{$params['object_id']}";//{$bp->current_item}";
		} else {
			$objectKey = "cover_{$bp->current_component}_{$params['object_id']}";
		}

		$newUrl = BuddyPressMap::mapURL($url, $objectKey);
		if (!empty($newUrl)) {
			if (empty($url)) {
				return preg_replace('#url\(\s*\)#', "url($newUrl)", $result);
			} else {
				return str_replace($url, $newUrl, $result);
			}
		}

		$process = apply_filters('media-cloud/integrations/buddypress-realtime', $this->settings->realtimeProcessing);
		if (empty($url) || empty($process)) {
			return $result;
		}

		$upload_dir = wp_get_upload_dir();

		if (strpos($url, $upload_dir['baseurl']) === false) {
			return $result;
		}

		$key = ltrim(str_replace($upload_dir['baseurl'], '', $url), '/');
		$filePath = trailingslashit($upload_dir['basedir']).$key;

		if (file_exists($filePath)) {
			/** @var StorageTool $storageTool */
			$storageTool = ToolsManager::instance()->tools['storage'];

			try {
				Logger::info("Uploading $filePath => $key", [], __METHOD__, __LINE__);
				$s3Url = $storageTool->client()->upload($key, $filePath, StorageGlobals::privacy('image'));

				$s3Data = [
					'url' => $s3Url,
					'key' => $key,
					'bucket' => $storageTool->client()->bucket(),
					'region' => $storageTool->client()->region(),
					'v' => MEDIA_CLOUD_INFO_VERSION,
					'privacy' => StorageGlobals::privacy('image'),
					'driver' => StorageToolSettings::driver()
				];

				$newUrl = BuddyPressMap::updateMap($url, $objectKey, $filePath, $s3Data);
				return str_replace($url, $newUrl, $result);
			} catch(\Exception $e) {
				Logger::error("Error:".$e->getMessage(), [], __METHOD__, __LINE__);
				return $result;
			}
		}

		return $result;
	}

	public function coverImageSettings($settings = []) {
		if (isset($settings['callback'])) {
			is_callable($settings['callback'], true, $oldCallback);
			is_callable([$this, 'coverImageCallback'], true, $newCallback);

			if ($oldCallback === $newCallback) {
				return $settings;
			}
		}

//		if (!empty($this->coverSettings)) {
//			return $settings;
//		}

		$this->coverSettings = $settings;
		$this->previousCallback = $settings['callback'];

		$settings['callback'] = [$this, 'coverImageCallback'];

		return $settings;
	}

	//endregion

	//region Avatars

	public function fetchCoreAvatar($imgTag, $params, $itemID, $subdir, $classID, $width, $height, $folderUrl, $folderDir) {
		Logger::info("Start fetchCoreAvatar: $imgTag", [], __METHOD__, __LINE__);

		$srcRe = '/src\s*=\s*(?:"|\')([^\'"]*)(?:"|\')/m';

		$this->loadBuddyPress();

		$mysteryUrl = bp_groups_default_avatar(null, $params);
		Logger::info("Mystery URL: $mysteryUrl", [], __METHOD__, __LINE__);
		if (preg_match($srcRe, $imgTag, $matches)) {
			$url = $matches[1];
			if (($url === $mysteryUrl) || (strpos($url, 'gravatar') !== false) || (strpos($url, 'mystery-man') !== false)) {
				$url = null;
			}

			$size = strtolower($params['type']);
			$objectKey = "avatar_{$params['object']}_{$params['item_id']}_$size";

			$newUrl = BuddyPressMap::mapURL($url, $objectKey);

			if (empty($newUrl)) {
				$oldObjectKey = "avatar_{$params['object']}_{$params['item_id']}";
				$newUrl = BuddyPressMap::mapURL($url, $oldObjectKey);

				if (!empty($newUrl)) {
					$urlSize = (strpos($newUrl, '-bpfull') === false) ? 'thumb' : 'full';
					if ($urlSize === $size) {
						$newObjectKey = "avatar_{$params['object']}_{$params['item_id']}_$urlSize";
						BuddyPressMap::copyMap($oldObjectKey, $newObjectKey);
						$objectKey = $newObjectKey;
					} else {
						$newUrl = null;
					}
				}
			}

			Logger::info("mapURL: $url $objectKey $newUrl", [], __METHOD__, __LINE__);
			if (!empty($newUrl)) {
				Logger::info("Found new URL, $newUrl", [], __METHOD__, __LINE__);
				return str_replace($matches[1], $newUrl, $imgTag);
			}

			$process = apply_filters('media-cloud/integrations/buddypress-realtime', $this->settings->realtimeProcessing);
			if (empty($process)) {
				return $imgTag;
			}

			/** @var StorageTool $storageTool */
			$storageTool = ToolsManager::instance()->tools['storage'];

			$urlInfo = parse_url($url);

			$upload_dir = wp_get_upload_dir();
			$keyPrefix = ltrim(trailingslashit(str_replace($upload_dir['basedir'], '', $folderDir)), '/');
			$file = basename($urlInfo['path']);

			$key = $keyPrefix.$file;

			$filePath = $folderDir.'/'.$file;
			if (!file_exists($filePath) || is_dir($filePath)) {
				Logger::warning("File does not exist or is a directory: $filePath", [], __METHOD__, __LINE__);
				return $imgTag;
			}

			try {
				Logger::info("Uploading $filePath to $key", [], __METHOD__, __LINE__);
				$s3Url = $storageTool->client()->upload($key, $filePath, StorageGlobals::privacy('image'));

				$s3Data = [
					'url' => $s3Url,
					'key' => $key,
					'bucket' => $storageTool->client()->bucket(),
					'region' => $storageTool->client()->region(),
					'v' => MEDIA_CLOUD_INFO_VERSION,
					'privacy' => StorageGlobals::privacy('image'),
					'driver' => StorageToolSettings::driver()
				];

				$newUrl = BuddyPressMap::updateMap($url, $objectKey, $filePath, $s3Data);

				if ($size === 'full') {
					$thumbFiles = glob(str_replace($file, '*-bpthumb.jpg', $filePath));

					if (count($thumbFiles) > 0) {
						$newParams = $params;
						$newParams['type'] = 'thumb';
						$newImgTag = str_replace($file, basename($thumbFiles[0]), $imgTag);
						$this->fetchCoreAvatar($newImgTag, $newParams, $itemID, $subdir, $classID, $width, $height, $folderUrl, $folderDir);
					}
				}

				return str_replace($url, $newUrl, $imgTag);
			} catch(\Exception $e) {
				Logger::error("Error:".$e->getMessage(), [], __METHOD__, __LINE__);

				return $imgTag;
			}
		}

		return $imgTag;
	}


	public function fetchCoreAvatarURL($url, $params) {
		$this->loadBuddyPress();

		$urlToCheck = $url;
		$mysteryUrl = bp_groups_default_avatar(null, $params);
		if (($url === $mysteryUrl) || (strpos($url, 'gravatar') !== false) || (strpos($url, 'mystery-man') !== false)) {
			$urlToCheck = null;
		}

		$size = strtolower($params['type']);

		$key = "avatar_{$params['object']}_{$params['item_id']}_$size";

		$newUrl = BuddyPressMap::mapURL($urlToCheck, $key);
		if (!empty($newUrl)) {
			return $newUrl;
		}

		$oldObjectKey = "avatar_{$params['object']}_{$params['item_id']}";
		$newUrl = BuddyPressMap::mapURL($urlToCheck, $oldObjectKey);
		if (!empty($newUrl)) {
			$urlSize = (strpos($newUrl, '-bpfull') === false) ? 'thumb' : 'full';
			if ($urlSize === $size) {
				$newObjectKey = "avatar_{$params['object']}_{$params['item_id']}_$urlSize";
				BuddyPressMap::copyMap($oldObjectKey, $newObjectKey);
				return $newUrl;
			}
		}

		if (($size === 'thumb') && empty($newUrl)) {
			$newObjectKey = "avatar_{$params['object']}_{$params['item_id']}_full";
			$newUrl = BuddyPressMap::mapURL($urlToCheck, $newObjectKey);
			if (!empty($newUrl)) {
				return $newUrl;
			}
		}

		return $url;
	}

	public function getBuddyPressAttachment($value, $params) {
		if ($params['type'] === 'cover-image') {
			if ($params['object_dir'] === 'groups') {
				$url = BuddyPressMap::mapURL(null, 'cover_groups_'.$params['item_id']);
			} else {
				$url = BuddyPressMap::mapURL(null, 'cover_profile_'.$params['item_id']);
			}

			if (!empty($url)) {
				return $url;
			}
		}

		return $value;
	}

	//endregion
}