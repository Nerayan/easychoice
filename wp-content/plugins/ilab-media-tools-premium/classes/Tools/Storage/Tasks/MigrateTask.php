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

namespace MediaCloud\Plugin\Tools\Storage\Tasks;

use MediaCloud\Plugin\Tools\Storage\StorageToolSettings;
use MediaCloud\Plugin\Tasks\AttachmentTask;
use MediaCloud\Plugin\Tools\Storage\StorageTool;
use MediaCloud\Plugin\Tools\ToolsManager;
use MediaCloud\Plugin\Utilities\Logging\Logger;
use function MediaCloud\Plugin\Utilities\arrayPath;
use function MediaCloud\Plugin\Utilities\postIdExists;

class MigrateTask extends AttachmentTask {

	//region Static Task Properties

	/**
	 * The identifier for the task.  Must be overridden.  Default implementation throws exception.
	 * @return string
	 * @throws \Exception
	 */
	public static function identifier() {
		return 'migrate-task';
	}

	/**
	 * The title for the task.  Must be overridden.  Default implementation throws exception.
	 * @return string
	 * @throws \Exception
	 */
	public static function title() {
		return 'Migrate To Storage';
	}

	/**
	 * View containing instructions for the task
	 * @return string|null
	 */
	public static function instructionView() {
		return 'tasks.batch.instructions.migrate-task';
	}

	/**
	 * The menu title for the task.
	 * @return string
	 * @throws \Exception
	 */
	public static function menuTitle() {
		return 'Migrate To Cloud';
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
		return "Migrate to Cloud Storage";
	}

	/**
	 * Determines if a task is a user facing task.
	 * @return bool|false
	 */
	public static function userTask() {
		return true;
	}

	/**
	 * The identifier for analytics
	 * @return string
	 */
	public static function analyticsId() {
		return '/batch/storage';
	}

	public static function warnOption() {
		return 'migrate-task-warning-seen';
	}

	public static function warnConfirmationAnswer() {
		return 'I UNDERSTAND';
	}

	public static function warnConfirmationText() {
		return "It is important that you backup your database prior to running this import task.  To continue, please type 'I UNDERSTAND' to confirm that you have backed up your database.";
	}

	/**
	 * The available options when running a task.
	 * @return array
	 */
	public static function taskOptions() {
		$options = [
			'selected-items' => [
				"title" => "Selected Media",
				"description" => "If you want to process just a small subset of items, click on 'Select Media'",
				"type" => "media-select"
			],
			'skip-imported' => [
				"title" => "Skip Imported",
				"description" => "Skip items that have already been imported.",
				"type" => "checkbox",
				"default" => true
			]
		];

		if (StorageToolSettings::deleteOnUpload()) {
			$options['delete-migrated'] = [
				"title" => "Delete Migrated Media",
				"description" => "Deletes migrated media from your local WordPress server.  <strong>Note:</strong> You must have Delete Uploads enabled in Cloud Storage for this setting to have any effect.  If you have Delete Uploads disabled, turning this on will have <strong>zero effect</strong>.",
				"type" => "checkbox",
				"default" => false
			];
		}

		$imgix = apply_filters('media-cloud/dynamic-images/enabled', false);
		if ($imgix) {
			$options['skip-thumbnails'] = [
				"title" => "Skip Thumbnails",
				"description" => "This will skip uploading thumbnails and other images sizes, only uploading the original master image.  This requires Imgix.",
				"type" => "checkbox",
				"default" => false
			];
		}

		if (!empty(StorageToolSettings::prefixFormat())) {
			$warning = '';
			if (strpos(StorageToolSettings::prefixFormat(), '@{date:') !== false) {
				$warning = "<p><strong>WARNING:</strong> Your custom upload prefix has a date in it, it will use today's date.  This means that all of your images will be placed in a folder for today's date.  It is recommended to remove the dynamic date from the prefix until after import.</p>";
			}

			$prefix = StorageToolSettings::prefix();

			$options['path-handling'] = [
				"title" => "Upload Paths",
				"description" => "Controls where in cloud storage imported files are placed.  <p>Current custom prefix: <code>$prefix</code>.</p>$warning",
				"type" => "select",
				"options" => [
					'preserve' => 'Keep original upload path',
					'replace' => "Replace upload path with custom prefix",
					'prepend' => "Prepend upload path with custom prefix",
				],
				"default" => 'preserve',
			];
		}

		$options['sort-order'] = [
			"title" => "Sort Order",
			"description" => "Controls the order that items from your media library are migrated to cloud storage.",
			"type" => "select",
			"options" => [
				'default' => 'Default',
				'date-asc' => "Oldest first",
				'date-desc' => "Newest first",
				'title-asc' => "Title, A-Z",
				'title-desc' => "Title, Z-A",
				'filename-asc' => "File name, A-Z",
				'filename-desc' => "File name, Z-A",
			],
			"default" => 'default',
		];

		return $options;
	}

	//endregion

	//region Data

	protected function filterPostArgs($args) {
		if (isset($this->options['skip-imported'])) {
			$args['meta_query'] = [
				'relation' => 'OR',
				[
					'relation' => 'AND',
					[
						'key'     => '_wp_attachment_metadata',
						'value'   => '"s3"',
						'compare' => 'NOT LIKE',
						'type'    => 'CHAR',
					],
					[
						'key'     => 'ilab_s3_info',
						'compare' => 'NOT EXISTS',
					],
				],
				[
					'relation' => 'AND',
					[
						'key'     => '_wp_attachment_metadata',
						'compare' => 'NOT EXISTS',
					],
					[
						'key'     => 'ilab_s3_info',
						'compare' => 'NOT EXISTS',
					],
				]
			];
		}

		return $args;
	}

	//endregion

	//region Execution

	/**
	 * @return array|bool
	 */
	public function prepare($options = [], $selectedItems = []) {
		if (!isset($options['path-handling'])) {
			$options['path-handling'] = 'preserve';
		}

		return parent::prepare($options, $selectedItems); // TODO: Change the autogenerated stub
	}

	public function willStart() {
		parent::willStart();

		if(empty(arrayPath($this->options, 'delete-migrated', false))) {
			add_filter('media-cloud/storage/delete_uploads', '__return_false');
		} else {
			add_filter('media-cloud/storage/queue-deletes', '__return_false');
		}
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
		$post_id = $item['id'];
		if (!postIdExists($post_id)) {
			return true;
		}

		$this->updateCurrentPost($post_id);

		Logger::info("Processing $post_id", [], __METHOD__, __LINE__);

		/** @var StorageTool $storageTool */
		$storageTool = ToolsManager::instance()->tools['storage'];
		$storageTool->processImport($this->currentItem, $post_id, null, $this->options);

		Logger::info("Finished processing $post_id", [], __METHOD__, __LINE__);

		return true;
	}


	public function complete() {
		do_action('media-cloud/storage/migration/complete');

		if (function_exists('rocket_clean_domain')) {
			rocket_clean_domain();
		}
	}

	//endregion
}
