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

require_once 'LearnDash/functions.php';


class LearnDashIntegration {
	public function __construct() {
		if (!defined('K_PATH_FONTS')) {
			define('K_PATH_FONTS', WP_PLUGIN_DIR.'/sfwd-lms/includes/vendor/tcpdf/fonts/');
		}
	}
}


