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

namespace ILAB\MediaCloud\Tools\Optimizer\Driver\Imagify;

use ILAB\MediaCloud\Tools\Optimizer\Models\OptimizerResultsInterface;
use ILAB\MediaCloud\Tools\Optimizer\OptimizerInterface;
use Imagify\Optimizer;
use wpCloud\StatelessMedia\Imagify;
use function ILAB\MediaCloud\Utilities\anyEmpty;

class ImagifyDriver implements OptimizerInterface {
	/** @var ImagifySettings  */
	protected $settings = null;

	public function __construct() {
		$this->settings = ImagifySettings::instance();
	}

	/**
	 * @inheritDoc
	 */
	public function enabled() {
		return (!empty($this->settings->apiKey));
	}


	/**
	 * @inheritDoc
	 */
	public function supportsWebhooks() {
		return false;
	}

	/**
	 * @inheritDoc
	 */
	public function supportsUploads() {
		return true;
	}

	/**
	 * @inheritDoc
	 */
	public function shouldUpload() {
		return true;
	}

	/**
	 * @inheritDoc
	 */
	public function shouldUseWebhook() {
		return false;
	}

	/**
	 * @inheritDoc
	 */
	public function supportsCloudStorage($provider) {
		return false;
	}

	/**
	 * Generates parameters
	 * @param $sizeName
	 *
	 * @return array [
	 *   'lossy' => int,
	 *   'keep_exif' => bool
	 * ]
	 */
	private function params($sizeName) {
		$params = [
			'level' => $this->settings->lossy,
			'keep_exif' => !empty($this->settings->preserveExif)
		];

		if (!empty($sizeName)) {
			$params = apply_filters('media-cloud/optimizer/params/imagify', $params, $sizeName);
		}

		return $params;
	}

	/**
	 * @inheritDoc
	 */
	public function optimizeFile($filepath, $cloudInfo = null, $sizeName = null) {
		$params = $this->params($sizeName);

		$imagify = new Optimizer($this->settings->apiKey);
		$result = $imagify->optimize($filepath, $params);

		return new ImagifyResults($result, $filepath);
	}

	/**
	 * @inheritDoc
	 */
	public function optimizeUrl($url, $filepath, $cloudInfo = null, $sizeName = null) {
		return null;
	}

	/**
	 * @inheritDoc
	 */
	public function handleWebhook($data) {
	}

	public function accountStats() {
		return null;
	}
}