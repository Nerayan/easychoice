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

namespace ILAB\MediaCloud\Tools\Browser\Batch;

use ILAB\MediaCloud\Tasks\BackgroundProcess;
use ILAB\MediaCloud\Tasks\BatchManager;
use ILAB\MediaCloud\Tools\Storage\ImportProgressDelegate;
use ILAB\MediaCloud\Tools\Storage\StorageTool;
use ILAB\MediaCloud\Tools\ToolsManager;
use function ILAB\MediaCloud\Utilities\arrayPath;
use ILAB\MediaCloud\Utilities\Logging\Logger;

if (!defined( 'ABSPATH')) { header( 'Location: /'); die; }

/**
 * Class ILABS3ImportProcess
 *
 * Background processing job for importing existing media to S3
 */
class ImportFromStorageBatchProcess extends BackgroundProcess implements ImportProgressDelegate {
	#region Variables
	protected $action = 'ilab_s3_export_process';
	#endregion

	#region Task Related
	protected function shouldHandle() {
	    return !BatchManager::instance()->shouldCancel(ImportFromStorageBatchTool::BatchIdentifier());
	}

	public function task($item) {
	    $startTime = microtime(true);

		Logger::info( 'Start Import Task', $item);
		if (!$this->shouldHandle()) {
			Logger::info( 'Task cancelled', $item);
			return false;
		}

		$index = $item['index'];
		$post = $item['post'];
		$options = (isset($item['options'])) ? $item['options'] : [];
		$importOnly = arrayPath($options, 'import-only', false);
		$preservePaths = arrayPath($options, 'preserve-paths', false);

		BatchManager::instance()->setCurrentID(ImportFromStorageBatchTool::BatchIdentifier(), null);
		BatchManager::instance()->setCurrentKey(ImportFromStorageBatchTool::BatchIdentifier(), $post['key']);
		BatchManager::instance()->setCurrentFile(ImportFromStorageBatchTool::BatchIdentifier(), $post['key']);
		BatchManager::instance()->setCurrentThumbUrl(ImportFromStorageBatchTool::BatchIdentifier(), arrayPath($post, 'thumb', null));
		BatchManager::instance()->setCurrentThumbIsIcon(ImportFromStorageBatchTool::BatchIdentifier(), arrayPath($post, 'icon', false));
		BatchManager::instance()->setCurrent(ImportFromStorageBatchTool::BatchIdentifier(), $index + 1);

		/** @var StorageTool $storageTool */
		$storageTool = ToolsManager::instance()->tools['storage'];
		$storageTool->importFileFromStorage($post['key'], arrayPath($post, 'thumbs', []), $importOnly, $preservePaths);

        $endTime = microtime(true) - $startTime;

        BatchManager::instance()->incrementTotalTime(ImportFromStorageBatchTool::BatchIdentifier(), $endTime);

		Logger::info( 'End Import Task', $item);

		return false;
	}

	public function dispatch() {
		Logger::info( 'Task dispatch');
		parent::dispatch();
	}

	protected function complete() {
		Logger::info( 'Task complete');
		BatchManager::instance()->reset(ImportFromStorageBatchTool::BatchIdentifier());
		parent::complete();
	}

	public function cancel_process() {
		Logger::info( 'Cancel process');

		parent::cancel_process();

        BatchManager::instance()->reset(ImportFromStorageBatchTool::BatchIdentifier());
	}

	public static function cancelAll() {
		Logger::info( 'Cancel all processes');

		wp_clear_scheduled_hook('wp_ilab_s3_export_process_cron');

		global $wpdb;

		$res = $wpdb->get_results("select * from {$wpdb->options} where option_name like 'wp_ilab_s3_export_process_batch_%'");
		foreach($res as $batch) {
			Logger::info( "Deleting batch {$batch->option_name}");
			delete_option($batch->option_name);
		}

		$res = $wpdb->get_results("select * from {$wpdb->options} where option_name like '__wp_ilab_s3_export_process_batch_%'");
		foreach($res as $batch) {
			delete_option($batch->option_name);
		}

        BatchManager::instance()->reset(ImportFromStorageBatchTool::BatchIdentifier());

		Logger::info( "Current cron", get_option( 'cron', []));
		Logger::info( 'End cancel all processes');
	}
	#endregion

	#region ImportProgressDelegate
	public function updateCurrentIndex($newIndex) {
        BatchManager::instance()->setCurrent(ImportFromStorageBatchTool::BatchIdentifier(), $newIndex);
	}

	public function updateCurrentFileName($newFile) {
        BatchManager::instance()->setCurrentFile(ImportFromStorageBatchTool::BatchIdentifier(), $newFile);
	}
	#endregion
}
