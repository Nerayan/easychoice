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

namespace ILAB\MediaCloud\Tools\Integrations\PlugIns\Elementor;

use ILAB\MediaCloud\Tasks\TaskManager;
use ILAB\MediaCloud\Tools\Assets\AssetsTool;
use ILAB\MediaCloud\Tools\Integrations\PlugIns\Elementor\Tasks\UpdateElementorTask;
use ILAB\MediaCloud\Tools\ToolsManager;
use ILAB\MediaCloud\Utilities\Environment;

if (!defined( 'ABSPATH')) { header( 'Location: /'); die; }

class ElementorIntegration {
	public function __construct() {
		if (is_admin()) {
			TaskManager::registerTask(UpdateElementorTask::class);

			add_action('media-cloud/storage/migration/complete', function() {
				if (!empty(Environment::Option('mcloud-elementor-auto-update', null, false))) {
					$task = new UpdateElementorTask();
					$task->prepare();
					TaskManager::instance()->queueTask($task);
				}
			});

			add_action('media-cloud/storage/import/complete', function() {
				if (!empty(Environment::Option('mcloud-elementor-auto-update', null, false))) {
					$task = new UpdateElementorTask();
					$task->prepare();
					TaskManager::instance()->queueTask($task);
				}
			});
		}

		if (ToolsManager::instance()->toolEnabled('assets')) {
			add_action('elementor/document/after_save', function($document, $data) {
				if (!empty(Environment::Option('mcloud-elementor-update-build', null, false))) {
					/** @var AssetsTool $assetTool */
					$assetTool = ToolsManager::instance()->tools['assets'];
					$assetTool->updateBuildVersion(false);
				}
			}, 10, 2);

			add_action('elementor/core/files/clear_cache', function() {
				if (!empty(Environment::Option('mcloud-elementor-update-build', null, false))) {
					/** @var AssetsTool $assetTool */
					$assetTool = ToolsManager::instance()->tools['assets'];
					$assetTool->updateBuildVersion(false);
				}
			}, 10);
		}
	}
}
