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

namespace ILAB\MediaCloud\Tools\Integrations\PlugIns\Elementor\Tasks;


use Elementor\Plugin;
use ILAB\MediaCloud\Tasks\Task;
use ILAB\MediaCloud\Tools\ToolsManager;
use function ILAB\MediaCloud\Utilities\arrayPath;
use ILAB\MediaCloud\Utilities\Logging\Logger;
use function ILAB\MediaCloud\Utilities\isKeyedArray;

class UpdateElementorTask extends Task {
	//region Static Task Properties

	/**
	 * The identifier for the task.  Must be overridden.  Default implementation throws exception.
	 * @return string
	 * @throws \Exception
	 */
	public static function identifier() {
		return 'update-elementor';
	}

	/**
	 * The title for the task.  Must be overridden.  Default implementation throws exception.
	 * @return string
	 * @throws \Exception
	 */
	public static function title() {
		return 'Update Elementor';
	}

	/**
	 * View containing instructions for the task
	 * @return string|null
	 */
	public static function instructionView() {
		return 'tasks.batch.instructions.update-elementor';
	}

	/**
	 * The menu title for the task.
	 * @return string
	 * @throws \Exception
	 */
	public static function menuTitle() {
		return 'Update Elementor';
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
		return true;
	}

	/**
	 * The identifier for analytics
	 * @return string
	 */
	public static function analyticsId() {
		return '/batch/update-elementor';
	}



	/**
	 * The available options when running a task.
	 * @return array
	 */
	public static function taskOptions() {
		return [
		];
	}

	public static function warnOption() {
		return 'update-elementor-task-warning-seen';
	}

	public static function warnConfirmationAnswer() {
		return 'I UNDERSTAND';
	}

	public static function warnConfirmationText() {
		return "It is important that you backup your database prior to running this task.  To continue, please type 'I UNDERSTAND' to confirm that you have backed up your database.";
	}

	//endregion


	//region Block Parsing
	private function extractImage($data) {
		$id = arrayPath($data, "id", null);
		$url = arrayPath($data, "url", null);

		if (!empty($id) && !empty($url)) {
			return ['id' => $id, 'url' => $url];
		}

		return null;
	}

	private function parseImages($widgetType, $metaId, $block, &$images) {
		$settings = arrayPath($block, 'settings', null);
		$type = arrayPath($block, 'widgetType', $widgetType);
		Logger::info("Widget type: $type", [], __METHOD__, __LINE__);

		if (!empty($settings)) {

			$thumbSize = arrayPath($settings, 'thumbnail_size', 'full');
			$thumbCustomSize = arrayPath($settings, 'thumbnail_custom_dimension', null);

			$imageSize = arrayPath($settings, 'image_size', 'full');
			$imageCustomSize = arrayPath($settings, 'image_custom_dimension', null);

			$size = empty($thumbSize) ? $imageSize : $thumbSize;
			$customSize = empty($thumbSize) ? $imageCustomSize : $thumbCustomSize;

			foreach($settings as $setting => $settingData) {
				if (is_array($settingData)) {
					if (isKeyedArray($settingData)) {
						$image = $this->extractImage($settingData);
						if (!empty($image)) {
							$images[] = [
								'meta_id' => $metaId,
								'url' => $image['url'],
								'id' => $image['id'],
								'size' => ($size === 'custom') ? $customSize : $size
							];

							Logger::info("$type - Found image {$image['url']} size:".json_encode($size), [], __METHOD__, __LINE__);
						}
					} else {
						foreach($settingData as $settingDatum) {
							$image = $this->extractImage($settingDatum);
							if (!empty($image)) {
								$images[] = [
									'meta_id' => $metaId,
									'url' => $image['url'],
									'id' => $image['id'],
									'size' => ($size === 'custom') ? $customSize : $size
								];

								Logger::info("$type - Found image {$image['url']} size:".json_encode($size), [], __METHOD__, __LINE__);
							} else if (isKeyedArray($settingDatum)) {
								foreach($settingDatum as $key => $value) {
									if (is_array($value)) {
										$image = $this->extractImage($value);
										if (!empty($image)) {
											$images[] = [
												'meta_id' => $metaId,
												'url' => $image['url'],
												'id' => $image['id'],
												'size' => ($size === 'custom') ? $customSize : $size
											];

											Logger::info("$type - Found image {$image['url']} size:".json_encode($size), [], __METHOD__, __LINE__);
										}
									}
								}
							}
						}
					}
				}
			}
		}

		$elements = arrayPath($block, 'elements', null);
		if (!empty($elements)) {
			foreach($elements as $element) {
				$this->parseImages($widgetType, $metaId, $element, $images);
			}
		}
	}

	//endregion


	//region Execution

	public function prepare($options = [], $selectedItems = []) {
		global $wpdb;
		$results = $wpdb->get_results("select post_id, meta_id from {$wpdb->postmeta} where meta_key = '_elementor_data' and meta_value LIKE '[%'", ARRAY_A);
		foreach($results as $result) {
			$this->addItem($result);
		}

		$this->addItem(['meta_id' => -1]);

		return true;
	}

	private function debugInput($metaId, $data) {
		if (!ToolsManager::instance()->toolEnabled('debugging')) {
			return;
		}

		$uploadDirInfo = wp_upload_dir();

		$debugPath = trailingslashit($uploadDirInfo['basedir']).'mediacloud/elementor/';
		if (!file_exists($debugPath)) {
			mkdir($debugPath, 0777, true);
		}

		file_put_contents($debugPath.$metaId.'-input.json', json_encode($data, JSON_PRETTY_PRINT));
	}

	private function debugOutput($metaId) {
		if (!ToolsManager::instance()->toolEnabled('debugging')) {
			return;
		}

		$uploadDirInfo = wp_upload_dir();

		$debugPath = trailingslashit($uploadDirInfo['basedir']).'mediacloud/elementor/';
		if (!file_exists($debugPath)) {
			mkdir($debugPath, 0777, true);
		}

		$jsonData = get_post_meta_by_id($metaId);
		if (!empty($jsonData)) {
			$data = json_decode($jsonData->meta_value, true);
			if(!empty($data)) {
				$jsonOutput = json_encode($data, JSON_PRETTY_PRINT);
				file_put_contents($debugPath.$metaId.'-output.json', $jsonOutput);
			} else {
				file_put_contents($debugPath.$metaId.'-output.json', $jsonData->meta_value);
			}
		} else {
			file_put_contents($debugPath.$metaId.'-output.json', '');
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
		$metaId = $item['meta_id'];

		if ($metaId == -1) {
			Plugin::instance()->files_manager->clear_cache();
			return true;
		}

		$jsonData = get_post_meta_by_id($metaId);
		if (empty($jsonData)) {
			Logger::info("No data for $metaId", [], __METHOD__, __LINE__);
			return true;
		}

		$data = json_decode($jsonData->meta_value, true);
		if (empty($data)) {
			Logger::info("Could not decode JSON value for $metaId: ".$jsonData->meta_value, [], __METHOD__, __LINE__);
			return true;
		}

		Logger::info("Processing $metaId", [], __METHOD__, __LINE__);

		$this->debugInput($metaId, $data);

		$images = [];
		for($i = 0; $i < count($data); $i++) {
			$block = $data[$i];
			$this->parseImages(null, $metaId, $block, $images);
		}

		global $wpdb;

		foreach($images as $imageData) {
			$url = $imageData['url'];

			if (is_array($imageData['size'])) {
				$width = isset($imageData['size']['width']) ? $imageData['size']['width'] : $imageData['size'][0];
				$height = isset($imageData['size']['height']) ? $imageData['size']['height'] : $imageData['size'][1];

				$image = image_downsize($imageData['id'], [$width, $height]);
				$newUrl = empty($image) ? null : $image[0];
			} else {
				$image = wp_get_attachment_image_src($imageData['id'], $imageData['size']);
				$newUrl = empty($image) ? null : $image[0];
			}

			if (empty($newUrl)) {
				Logger::info("New URL is empty, skipping.", [], __METHOD__, __LINE__);
				continue;
			}

			if ($newUrl === $url) {
				Logger::info("URL $url is the same as $newUrl, skipping.", [], __METHOD__, __LINE__);
				continue;
			}

			Logger::info("Replacing $url with $newUrl", [], __METHOD__, __LINE__);

			$oldUrl = str_replace('/', '\\\/', $url);
			$newUrl = str_replace('/', '\\\/', $newUrl);

			$rows_affected = $wpdb->query(
				"UPDATE {$wpdb->postmeta} " .
				"SET `meta_value` = INSERT(`meta_value`, LOCATE('$oldUrl', `meta_value`), CHAR_LENGTH('$oldUrl'), '$newUrl') ".
				"WHERE `meta_id` = $metaId");

			Logger::info("Replaced {$rows_affected} instances of $url", [], __METHOD__, __LINE__);
		}

		$this->debugOutput($metaId);

		Logger::info("Finished processing $metaId", [], __METHOD__, __LINE__);

		return true;
	}

	//endregion
}
