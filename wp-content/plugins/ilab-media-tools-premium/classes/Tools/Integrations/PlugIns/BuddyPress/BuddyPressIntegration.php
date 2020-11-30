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

use MediaCloud\Plugin\Tools\Storage\StorageException;
use MediaCloud\Plugin\Tools\Storage\StorageGlobals;
use MediaCloud\Plugin\Tools\Storage\StorageManager;
use MediaCloud\Plugin\Tools\Storage\StorageToolSettings;
use MediaCloud\Plugin\Tasks\TaskManager;
use MediaCloud\Plugin\Tools\Storage\StorageTool;
use MediaCloud\Plugin\Tools\ToolsManager;
use MediaCloud\Plugin\Utilities\Logging\Logger;

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

			TaskManager::registerTask(BuddyPressDeleteTask::class);

			if (defined( 'WP_CLI' ) && class_exists('\WP_CLI')) {
				BuddyPressCommands::Register();
			}

			add_filter('bp_core_fetch_avatar', [$this, 'fetchCoreAvatar'], PHP_INT_MAX, 9);
			add_filter('bp_core_fetch_avatar_url', [$this, 'fetchCoreAvatarURL'], PHP_INT_MAX, 2);

			add_filter('bp_before_members_cover_image_settings_parse_args', [$this, 'coverImageSettings'], 10, PHP_INT_MAX);
			add_filter('bp_before_groups_cover_image_settings_parse_args', [$this, 'coverImageSettings'], 10, PHP_INT_MAX);
		}
	}

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

		if (empty($url)) {
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
				Logger::info("Error:".$ex->getMessage(), [], __METHOD__, __LINE__);
				return $result;
			}
		}

		return $result;
	}

	public function coverImageSettings($settings = []) {
		$this->coverSettings = $settings;
		$this->previousCallback = $settings['callback'];

		$settings['callback'] = [$this, 'coverImageCallback'];

		return $settings;
	}

	//endregion

	//region Avatars

	public function fetchCoreAvatar($imgTag, $params, $itemID, $subdir, $classID, $width, $height, $folderUrl, $folderDir) {
		$srcRe = '/src\s*=\s*(?:"|\')([^\'"]*)(?:"|\')/m';

		if (!function_exists('bp_groups_default_avatar')) {
			require_once \BuddyPress::instance()->plugin_dir . 'bp-groups/bp-groups-filters.php';
		}


		Logger::info("Start fetchCoreAvatar: $imgTag", [], __METHOD__, __LINE__);

		$mysteryUrl = bp_groups_default_avatar(null, $params);
		Logger::info("Mystery URL: $mysteryUrl", [], __METHOD__, __LINE__);
		if (preg_match($srcRe, $imgTag, $matches)) {
			$url = $matches[1];
			if (($url === $mysteryUrl) || (strpos($url, 'gravatar') !== false)) {
				$url = null;
			}

			$objectKey = "avatar_{$params['object']}_{$params['item_id']}";

			$newUrl = BuddyPressMap::mapURL($url, $objectKey);

			Logger::info("mapURL: $url $objectKey $newUrl", [], __METHOD__, __LINE__);
			if (!empty($newUrl)) {
				Logger::info("Found new URL, $newUrl", [], __METHOD__, __LINE__);
				return str_replace($matches[1], $newUrl, $imgTag);
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
				Logger::info("File does not exist or is a directory: $filePath", [], __METHOD__, __LINE__);
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
				return str_replace($url, $newUrl, $imgTag);
			} catch(\Exception $e) {
				Logger::info("Error:".$e->getMessage(), [], __METHOD__, __LINE__);

				return $imgTag;
			}
		}

		return $imgTag;
	}


	public function fetchCoreAvatarURL($url, $params) {
		$newUrl = BuddyPressMap::mapURL($url);
		if (!empty($newUrl)) {
			return $newUrl;
		}

		return $url;
	}

	//endregion
}