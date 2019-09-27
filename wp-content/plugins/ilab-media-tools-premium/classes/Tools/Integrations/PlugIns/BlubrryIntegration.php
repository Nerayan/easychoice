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
use ILAB\MediaCloud\Utilities\View;

if (!defined( 'ABSPATH')) { header( 'Location: /'); die; }

class BlubrryIntegration {
	public function __construct() {
		add_action('current_screen', function(\WP_Screen $screen) {
			if (!empty($screen)) {
				if (($screen->action == 'add') && ($screen->base == 'post')) {
					$this->hookUI();
				}
			}
		});
	}

	private function hookUI() {
		add_action('wp_enqueue_media', function() {
			add_action('admin_footer', function() {
				echo View::render_view('integrations/blubrry', []);
			});
		});
	}
}


