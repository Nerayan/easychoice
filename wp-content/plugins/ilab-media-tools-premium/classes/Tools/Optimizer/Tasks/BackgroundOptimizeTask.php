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

namespace ILAB\MediaCloud\Tools\Optimizer\Tasks;

use ILAB\MediaCloud\Tasks\Task;
use ILAB\MediaCloud\Tools\Optimizer\Models\BackgroundOptimization;
use ILAB\MediaCloud\Tools\Optimizer\Models\PendingOptimization;
use ILAB\MediaCloud\Tools\Optimizer\OptimizerTool;
use ILAB\MediaCloud\Tools\Storage\StorageTool;
use ILAB\MediaCloud\Tools\ToolsManager;
use ILAB\MediaCloud\Utilities\Logging\Logger;

class BackgroundOptimizeTask extends Task {
	//region Static Task Properties

	/**
	 * The identifier for the task.  Must be overridden.  Default implementation throws exception.
	 * @return string
	 * @throws \Exception
	 */
	public static function identifier() {
		return 'background-optimize';
	}

	/**
	 * The title for the task.  Must be overridden.  Default implementation throws exception.
	 * @return string
	 * @throws \Exception
	 */
	public static function title() {
		return 'Background Optimize';
	}

	/**
	 * The menu title for the task.
	 * @return string
	 * @throws \Exception
	 */
	public static function menuTitle() {
		return null;
	}

	/**
	 * Controls if this task stops on an error.
	 *
	 * @return bool
	 */
	public static function stopOnError() {
		return false;
	}

	/**
	 * Bulk action title.
	 *
	 * @return string|null
	 */
	public static function bulkActionTitle() {
		return null;
	}

	/**
	 * Determines if a task is a user facing task.
	 * @return bool|false
	 */
	public static function userTask() {
		return false;
	}

	/**
	 * The identifier for analytics
	 * @return string
	 */
	public static function analyticsId() {
		return '/batch/background-optimize';
	}

	public static function runFromTaskManager() {
		return false;
	}


	/**
	 * The available options when running a task.
	 * @return array
	 */
	public static function taskOptions() {
		return [];
	}

	//endregion

	//region Execution

	public function prepare($options = [], $selectedItems = []) {
		foreach($selectedItems as $selectedItem) {
			$this->addItem(['pendingId' => $selectedItem]);
		}

		return true;
	}

	/**
	 * Performs the actual task
	 *
	 * @param $item
	 *
	 * @return bool|void
	 * @throws \Exception
	 */
	public function performTask($item) {
		$pendingId = $item['pendingId'];

		Logger::info("Processing background optimization $pendingId", [], __METHOD__, __LINE__);

		/** @var BackgroundOptimization $bg */
		$bg = BackgroundOptimization::instance($pendingId);
		if (empty($bg)) {
			Logger::info("Background optimization $pendingId does not exist", [], __METHOD__, __LINE__);
			return true;
		}

		/** @var OptimizerTool $optimizer */
		$optimizer = ToolsManager::instance()->tools['optimizer'];
		$optimizer->performBackgroundOptimization($bg);

		Logger::info("Finished performing background optimization.", [], __METHOD__, __LINE__);

		return true;
	}

	//endregion
}