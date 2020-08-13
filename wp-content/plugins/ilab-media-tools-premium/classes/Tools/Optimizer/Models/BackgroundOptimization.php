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

namespace ILAB\MediaCloud\Tools\Optimizer\Models;
use ILAB\MediaCloud\Model\Model;
use ILAB\MediaCloud\Storage\StorageGlobals;
use ILAB\MediaCloud\Tools\Mux\MuxAPI;
use ILAB\MediaCloud\Tools\Mux\MuxToolSettings;
use ILAB\MediaCloud\Tools\Storage\StorageTool;
use ILAB\MediaCloud\Tools\ToolsManager;
use ILAB\MediaCloud\Utilities\Logging\Logger;
use function ILAB\MediaCloud\Utilities\arrayPath;

/**
 * Class MuxAsset
 * @package ILAB\MediaCloud\Tools\Mux\Models
 *
 * @property int $postId
 * @property int $createdAt
 * @property string $uploadPath
 * @property string $filename
 * @property string $prefix
 * @property string $bucketFilename
 * @property string $privacy
 * @property string $provider
 * @property string $currentSize
 */
class BackgroundOptimization extends Model {
	//region Fields

	/**
	 * The post ID, if any
	 * @var int
	 */
	protected $postId = 0;

	/**
	 * Epoch time created
	 * @var int
	 */
	protected $createdAt = 0;

	/**
	 * @var string
	 */
	protected $uploadPath = null;

	/**
	 * @var string
	 */
	protected $filename = null;

	/**
	 * @var string
	 */
	protected $prefix = null;

	/**
	 * @var string
	 */
	protected $bucketFilename = null;

	/**
	 * @var string
	 */
	protected $privacy = null;

	/**
	 * @var string
	 */
	protected $provider = null;

	/**
	 * @var string
	 */
	protected $currentSize = null;

	/**
	 * @var array
	 * @property int $postId
	 * @property int $createdAt
	 * @property string $uploadPath
	 * @property string $filename
	 * @property string $prefix
	 * @property string $bucketFilename
	 * @property string $privacy
	 * @property string $provider
	 * @property string $currentSize
	 */

	protected $modelProperties = [
		'postId' => '%d',
		'createdAt' => '%d',
		'uploadPath' => '%s',
		'filename' => '%s',
		'prefix' => '%s',
		'bucketFilename' => '%s',
		'privacy' => '%s',
		'provider' => '%s',
		'currentSize' => '%s',
	];

	protected $columnMap = [
		'postId' => 'post_id',
		'createdAt' => 'created_at',
		'uploadPath' => 'upload_path',
		'filename' => 'filename',
		'prefix' => 'prefix',
		'bucketFilename' => 'bucket_filename',
		'privacy' => 'privacy',
		'provider' => 'provider',
		'currentSize' => 'current_size',
	];

	//endregion


	//region Static

	public static function table() {
		global $wpdb;
		return "{$wpdb->base_prefix}mcloud_bg_optimizations";
	}

	//endregion


	//region Constructor

	public function __construct($data = null) {
		parent::__construct($data);
	}

	//endregion
}