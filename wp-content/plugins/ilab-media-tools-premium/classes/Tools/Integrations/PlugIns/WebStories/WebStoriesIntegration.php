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

namespace MediaCloud\Plugin\Tools\Integrations\PlugIns\WebStories;

use MediaCloud\Plugin\Tasks\TaskManager;
use MediaCloud\Plugin\Tools\Integrations\PlugIns\WebStories\Tasks\UpdateWebStoriesTask;
use function MediaCloud\Plugin\Utilities\arrayPath;
use function MediaCloud\Plugin\Utilities\postIdExists;

if (!defined( 'ABSPATH')) { header( 'Location: /'); die; }

class WebStoriesIntegration {
	public function __construct() {
		if (is_admin()) {
			TaskManager::registerTask(UpdateWebStoriesTask::class);
		}

		add_action('rest_insert_web-story', function(\WP_Post $post, \WP_REST_Request $request, bool $creating) {
			if ($post->post_type === 'web-story') {
				static::updatePost($post->ID, $post);
			}
		}, PHP_INT_MAX, 3);
	}

	private static function generateCache(\WP_Post $post) {
		$storyJson = $post->post_content_filtered;
		if (empty($storyJson)) {
			return null;
		}

		$storyData = json_decode($storyJson, true);
		if (empty($storyData)) {
			return null;
		}

		$cache = [
			'images' => [],
			'videos' => []
		];

		$keys = [];
		$pages = arrayPath($storyData, 'pages', []);
		foreach($pages as $page) {
			$elements = arrayPath($page, 'elements', []);
			foreach($elements as $element) {
				$resource = arrayPath($element, 'resource', null);
				if (empty($resource)) {
					continue;
				}

				$id = arrayPath($resource, 'id', null);
				if (empty($id) || !postIdExists($id)) {
					continue;
				}

				$mimeType = get_post_mime_type($id);
				if ((strpos($mimeType, 'image') !== 0) && (strpos($mimeType, 'video') !== 0)) {
					continue;
				}

				$type = arrayPath($resource, 'type', null);
				$src = arrayPath($resource, 'src', null);
				$title = arrayPath($resource, 'title', null);
				$alt = arrayPath($resource, 'alt', $title);
				$posterId = arrayPath($resource, 'posterId', null);

				if (empty($alt) || empty($id)) {
					continue;
				}

				$key = "$src,$alt,$id";
				if (isset($keys[$key])) {
					continue;
				}

				$keys[$key] = true;

				if ($type === 'image') {
					$cache['images'][] = [
						'src' => $src,
						'alt' => $alt,
						'id' => $id
					];
				} else if ($type === 'video') {
					$cache['videos'][] = [
						'src' => $src,
						'alt' => $alt,
						'id' => $id,
						'posterId' => $posterId
					];
				}
			}
		}

		return $cache;
	}

	public static function updatePost(int $post_id, \WP_Post $post) {
		$content = static::filterWebStory($post);
		if ($content != $post->post_content) {
			$post->post_content = $content;
			wp_update_post($post);
		}
	}

	public static function filterWebStory(\WP_Post $post) {
		$content = $post->post_content;

		$cache = static::generateCache($post);
		if (empty($cache)) {
			return $content;
		}

		$imgRegex = '/<amp-img\s*([^>]+)\s*>/ms';
		$attributeRegex = '/([aA-zZ]+)\s*\=\s*\"([^"]+)\"/ms';
		preg_match_all($imgRegex, $content, $imgMatches, PREG_SET_ORDER, 0);
		foreach($imgMatches as $match) {
			$imgTag = $match[0];
			$attrStr = trim($match[1]);
			preg_match_all($attributeRegex, $attrStr, $attrMatches, PREG_SET_ORDER, 0);

			$attrs = [];
			foreach($attrMatches as $attrMatch) {
				$attrs[$attrMatch[1]] = $attrMatch[2];
			}

			$src = arrayPath($attrs, 'src', null);
			$srcSet = arrayPath($attrs, 'srcSet', null);
			$alt = arrayPath($attrs, 'alt', null);

			$foundId = null;
			foreach($cache['images'] as $image) {
				if ($image['src'] === $src) {
					$foundId = $image['id'];
				}
			}

			if (empty($foundId)) {
				foreach($cache['images'] as $image) {
					if ($image['alt'] === $alt) {
						$foundId = $image['id'];
					}
				}
			}

			if (empty($foundId)) {
				continue;
			}

			$attrs['src'] = wp_get_attachment_image_src($foundId, 'full')[0];

			if (!empty($srcSet)) {
				$sources = [];

				$srcSetLines = explode(',', $srcSet);
				foreach($srcSetLines as $srcSetLine) {
					$srcLineParts = explode(' ', $srcSetLine);
					if (count($srcLineParts) > 0) {
						$width = intval($srcLineParts[1]);
						$url = image_downsize($foundId, [$width, 0, false]);
						if (!empty($url)) {
							$sources[] = "{$url[0]} {$width}w";
						}
					}
				}

				$attrs['srcSet'] = empty($sources) ? '' : implode(', ', $sources);
			}

			$attributes = [];
			foreach ($attrs as $name => $value) {
				$attributes[] = "$name=\"{$value}\"";
			}

			$attributeString = implode(" ", $attributes);
			$newTag = "<amp-img {$attributeString}>";
			$content = str_replace($imgTag, $newTag, $content);
		}

		$videoRegex = '/<amp-video\s*([^>]+)\s*>\s*<source\s*([^>]+)>\s*<\s*\/\s*amp-video\s*>/ms';
		preg_match_all($videoRegex, $content, $videoMatches, PREG_SET_ORDER, 0);
		foreach($videoMatches as $match) {
			$videoTag = $match[0];
			$attrStr = trim($match[1]);
			$sourceAttrStr = trim($match[2]);

			preg_match_all($attributeRegex, $attrStr, $attrMatches, PREG_SET_ORDER, 0);
			preg_match_all($attributeRegex, $sourceAttrStr, $sourceAttrMatches, PREG_SET_ORDER, 0);

			$attrs = [];
			foreach($attrMatches as $attrMatch) {
				$attrs[$attrMatch[1]] = $attrMatch[2];
			}

			$sourceAttrs = [];
			foreach($sourceAttrMatches as $attrMatch) {
				$sourceAttrs[$attrMatch[1]] = $attrMatch[2];
			}

			$src = arrayPath($sourceAttrs, 'src', null);
			$alt = arrayPath($attrs, 'alt', null);

			$foundId = null;
			$foundPosterId = null;
			foreach($cache['videos'] as $video) {
				if ($video['src'] === $src) {
					$foundId = $video['id'];
					$foundPosterId = $video['posterId'];
				}
			}

			if (empty($foundId)) {
				foreach($cache['images'] as $video) {
					if ($video['alt'] === $alt) {
						$foundId = $video['id'];
						$foundPosterId = $video['posterId'];
					}
				}
			}

			if (empty($foundId)) {
				continue;
			}

			$srcUrl = wp_get_attachment_url($foundId);
			if (empty($srcUrl)) {
				continue;
			}

			$sourceAttrs['src'] = $srcUrl;
			if (!empty($foundPosterId)) {
				$posterUrl = wp_get_attachment_image_src($foundPosterId, 'full');
				if (!empty($posterUrl)) {
					$attrs['poster'] = $attrs['artwork'] = $posterUrl[0];
				}
			}

			$attributes = [];
			foreach ($attrs as $name => $value) {
				$attributes[] = "$name=\"{$value}\"";
			}


			$sourceAttributes = [];
			foreach ($sourceAttrs as $name => $value) {
				$sourceAttributes[] = "$name=\"{$value}\"";
			}

			$attributeString = implode(" ", $attributes);
			$sourceAttributeString = implode(" ", $sourceAttributes);
			$newTag = "<amp-video {$attributeString}><source {$sourceAttributeString}/></amp-video>";
			$content = str_replace($videoTag, $newTag, $content);
		}


		return $content;
	}
}