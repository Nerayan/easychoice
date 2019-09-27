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

use ILAB\MediaCloud\Storage\StorageConstants;
use ILAB\MediaCloud\Storage\StorageSettings;
use ILAB\MediaCloud\Tools\DynamicImages\DynamicImagesTool;
use ILAB\MediaCloud\Tools\Storage\StorageTool;
use ILAB\MediaCloud\Tools\ToolsManager;
use ILAB\MediaCloud\Utilities\Environment;

if (!defined( 'ABSPATH')) { header( 'Location: /'); die; }

class NextGenGalleryIntegeration {
	/** @var StorageTool|null $storageTool */
	private $storageTool = null;

	/** @var DynamicImagesTool|null $dynamicImagesTool */
	private $dynamicImagesTool = null;

	private static $urlCache = null;

	private $useCache = true;

	public function __construct() {
		$this->storageTool = ToolsManager::instance()->tools['storage'];
		$this->dynamicImagesTool = DynamicImagesTool::currentDynamicImagesTool();

		add_filter('ngg_get_image_url', [$this, 'getImageUrl'], 1000, 3);

		if (static::$urlCache == null) {
			static::$urlCache = get_option('ilab-ngg-static-url-cache', []);
		}

		$this->useCache = Environment::Option('mcloud-ngg-use-url-cache', null, true);
	}

	public function getImageUrl($url, $image, $size) {
		$galleryPath = @\C_NextGen_Settings::get_instance()->gallerypath;
		if (empty($galleryPath)) {
			return $url;
		}

		if ($this->useCache && isset(static::$urlCache[$image->pid.$size])) {
			return static::$urlCache[$image->pid.$size];
		}

		$galleryRoot = (defined('NGG_GALLERY_ROOT_TYPE') && (NGG_GALLERY_ROOT_TYPE == 'content')) ? WP_CONTENT_DIR : ABSPATH;
		$webRoot = realpath(trailingslashit($galleryRoot).'..');

		$filePath = $webRoot.parse_url($url, PHP_URL_PATH);

		if (!file_exists($filePath)) {
			return $url;
		}

		$key = str_replace($galleryRoot, '', $filePath);

		if (!$this->storageTool->client()->exists($key)) {
			$this->storageTool->client()->upload($key, $filePath, StorageConstants::ACL_PUBLIC_READ);
		}

		if (empty($this->dynamicImagesTool)) {
			$url = $this->storageTool->client()->url($key);
			if (!empty(StorageSettings::cdn())) {
				$cdnScheme = parse_url(StorageSettings::cdn(), PHP_URL_SCHEME);
				$cdnHost = parse_url(StorageSettings::cdn(), PHP_URL_HOST);

				$urlScheme =  parse_url($url, PHP_URL_SCHEME);
				$urlHost = parse_url($url, PHP_URL_HOST);

				$url = str_replace("{$urlScheme}://{$urlHost}", "{$cdnScheme}://{$cdnHost}", $url);
			}
		} else {
			$url = $this->dynamicImagesTool->urlForStorageMedia($key);
		}

		static::$urlCache[$image->pid.$size] = $url;
		update_option('ilab-ngg-static-url-cache', static::$urlCache);

		return $url;
	}

}