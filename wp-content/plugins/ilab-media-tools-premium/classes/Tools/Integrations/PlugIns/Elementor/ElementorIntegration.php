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

namespace MediaCloud\Plugin\Tools\Integrations\PlugIns\Elementor;

use MediaCloud\Plugin\Tasks\TaskManager;
use MediaCloud\Plugin\Tools\Assets\AssetsTool;
use MediaCloud\Plugin\Tools\Integrations\PlugIns\Elementor\Tasks\UpdateElementorTask;
use MediaCloud\Plugin\Tools\ToolsManager;
use MediaCloud\Plugin\Utilities\Environment;
use function MediaCloud\Plugin\Utilities\arrayPath;

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

			add_action( 'wp_ajax_elementor_get_images_details', function() {
				if (ToolsManager::instance()->toolEnabled('storage')) {
					$this->getImageDetails();
				}
			}, 1, 0);
		}

		static::registerWidgetDefFilters();

		add_filter('elementor/image_size/get_attachment_image_html', function($html, $settings, $image_size_key, $image_key) {
			$id = arrayPath($settings, "$image_key/id", null);
			if (!empty($id)) {
				$size = arrayPath($settings, "{$image_key}_size", null);
				if (!empty($size) && ($size === 'custom')) {
					$sizeInfo = arrayPath($settings, "{$image_key}_custom_dimension", null);
					if (!empty($sizeInfo)) {
						$sizeName = "custom_".$sizeInfo['width'].'x'.$sizeInfo['height'];
						$newUrl = ElementorUpdater::instance()->getCustomSize($id, $sizeName)[0];
						if (!empty($newUrl)) {
							$re = '/((?:http|https):\/\/(?:[^\'"]+))/m';
							preg_match($re, $html, $matches);

							if (count($matches) > 0) {
								return str_replace($matches[0], $newUrl, $html);
							}
						}
					}
				}
			}

			return $html;
		}, PHP_INT_MAX, 4);

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

	//region Dynamic Image Sizes
	private function getImageDetails() {
		$items = $_POST['items'];
		$urls  = [];

		foreach ( $items as $item ) {
			$urls[ $item['id'] ] = $this->getDetails($item['id'], $item['size'], $item['is_first_time']);
		}

		wp_send_json_success($urls);
	}

	private function getDetails($id, $size, $firstTime) {
		if ('true' === $firstTime) {
			$sizes = ilab_get_image_sizes();
			$sizes[] = 'full';
		} else {
			$sizes = [];
		}

		$sizes[] = $size;
		$urls = [];
		foreach ( $sizes as $size ) {
			if ( 0 === strpos( $size, 'custom_' ) ) {
				$result = ElementorUpdater::instance()->getCustomSize($id, $size);
				if (!empty($result)) {
					$urls[$size] = $result[0];
				}
			} else {
				$urls[ $size ] = wp_get_attachment_image_src( $id, $size )[0];
			}
		}

		return $urls;
	}

	//endregion

	//region Widget Handlers
	protected static function registerWidgetDefFilters() {
		add_filter("media-cloud/elementor/def/all-defs", function($widgetInfo) {
			$elementNames = [];
			foreach($widgetInfo['elements'] as &$element) {
				$elementNames[] = $element['name'];
			}

			foreach($widgetInfo['elements'] as &$element) {
				if (strpos($element['name'], '_background_') === 0) {
					$cleaned = ltrim($element['name'], '_');
					if (!in_array($cleaned, $elementNames)) {
						$newElement = $element;
						$newElement['name'] = $cleaned;
						$widgetInfo['elements'][] = $newElement;
					}
				}
			}

			return $widgetInfo;
		});

		add_filter("media-cloud/elementor/def/eael-flip-box", function($widgetInfo) {
			foreach($widgetInfo['elements'] as &$element) {
				if (isset($element['imageSize'])) {
					if (strpos($element['name'], 'eael_flipbox_image') !== 0) {
						unset($element['imageSize']);
						unset($element['imageCustomSizeName']);
					} else if(strpos($element['name'], 'eael_flipbox_image_back') === 0) {
						$element['imageSize'] = 'thumbnail_back_size';
						$element['imageCustomSizeName'] = 'thumbnail_back_custom_dimension';
					}
				}
			}

			return $widgetInfo;
		});
	}
	//endregion
}
