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

if (!defined('ABSPATH')) { header('Location: /'); die; }

return [
	"id" => "integrations",
	"name" => "Integrations",
	"description" => "Manage integrations for WooCommerce, Easy Digital Downloads and others.",
	"class" => "ILAB\\MediaCloud\\Tools\\Integrations\\IntegrationsTool",
	"exclude" => true,
	"dependencies" => [],
	"env" => "ILAB_MEDIA_INTEGRATIONS_ENABLED",  // this is always enabled btw
	"plugins" => [
		'master-slider/master-slider.php' => "\\ILAB\\MediaCloud\\Tools\\Integrations\\PlugIns\\MasterSliderIntegration",
		'ml-slider/ml-slider.php' => "\\ILAB\\MediaCloud\\Tools\\Integrations\\PlugIns\\MetaSliderIntegeration",
		'ml-slider-pro/ml-slider-pro.php' => "\\ILAB\\MediaCloud\\Tools\\Integrations\\PlugIns\\MetaSliderIntegeration",
		'smart-slider-3/smart-slider-3.php' => "\\ILAB\\MediaCloud\\Tools\\Integrations\\PlugIns\\SmartSliderIntegration",
		'nextgen-gallery/nggallery.php' => "\\ILAB\\MediaCloud\\Tools\\Integrations\\PlugIns\\NextGenGalleryIntegeration",
		'ultimate-member/ultimate-member.php' => "\\ILAB\\MediaCloud\\Tools\\Integrations\\PlugIns\\UltimateMemberIntegration",
		'sfwd-lms/sfwd_lms.php' => "\\ILAB\\MediaCloud\\Tools\\Integrations\\PlugIns\\LearnDashIntegration",
		'powerpress/powerpress.php' => "\\ILAB\\MediaCloud\\Tools\\Integrations\\PlugIns\\BlubrryIntegration",
		'profilegrid-user-profiles-groups-and-communities/profile-magic.php' => "\\ILAB\\MediaCloud\\Tools\\Integrations\\PlugIns\\ProfileGridIntegration",
	],
	"settings" => [
		"options-page" => "media-tools-integrations",
		"options-group" => "ilab-media-integrations",
		"groups" => [
			"ilab-media-cloud-woo-commerce" => [
				"title" => "WooCommerce Settings",
				"dynamic" => true,
				"description" => "The following options control WooCommerce integration.",
				"options" => [
					"mcloud-woo-commerce-use-presigned-urls" => [
						"title" => "Use Pre-Signed URLs",
						"description" => "Set to true to generate signed URLs for downloadable products that will expire within a specified time period.  <strong>Note:</strong> If you have pre-signed URLs enabled in storage settings, but this is disabled, pre-signed URLs will still be used.",
						"display-order" => 0,
						"type" => "checkbox",
						"default" => true,
					],
					"mcloud-woo-commerce-presigned-expiration" => [
						"title" => "Pre-Signed URL Expiration",
						"description" => "The number of minutes the signed URL is valid for.",
						"display-order" => 1,
						"type" => "number",
						"default" => 1,
					]
				]
			],
			"ilab-media-cloud-edd" => [
				"title" => "Easy Digital Downloads Settings",
				"dynamic" => true,
				"description" => "The following options control EDD integration.",
				"options" => [
					"mcloud-edd-use-presigned-urls" => [
						"title" => "Use Pre-Signed URLs",
						"description" => "Set to true to generate signed URLs for downloadable products that will expire within a specified time period.  <strong>Note:</strong> If you have pre-signed URLs enabled in storage settings, but this is disabled, pre-signed URLs will still be used.",
						"display-order" => 0,
						"type" => "checkbox",
						"default" => true,
					],
					"mcloud-edd-presigned-expiration" => [
						"title" => "Pre-Signed URL Expiration",
						"description" => "The number of minutes the signed URL is valid for.",
						"display-order" => 1,
						"type" => "number",
						"default" => 1,
					]
				]
			],
			"ilab-media-cloud-master-slider" => [
				"title" => "Master Slider",
				"dynamic" => true,
				"description" => "The following controls Master Slider integration.  If you are using Imgix or Dynamic Images, the images will be resized and cropped to exact sizes.  If you aren't using either feature, than the closest image size will be used.",
				"options" => [
					"mcloud-master-slider-image-resize" => [
						"title" => "Resize Image",
						"description" => "Determines if the images should be resized or not.",
						"display-order" => 1,
						"type" => "checkbox",
						"default" => true,
					],
					"ilab-mc-master-slider-image-width" => [
						"title" => "Override Image Width",
						"description" => "Override the slider's specified image width.  Set to 0 to use the slider's default width.",
						"display-order" => 1,
						"type" => "number",
						"min" => 0,
						"max" => 100000,
						"default" => 0,
					],
					"mcloud-master-slider-image-height" => [
						"title" => "Override Image Height",
						"description" => "Override the slider's specified image height.  Set to 0 to use the slider's default height.",
						"display-order" => 1,
						"type" => "number",
						"min" => 0,
						"max" => 100000,
						"default" => 0,
					],
					"ilab-mc-master-slider-thumb-crop" => [
						"title" => "Crop Thumbnail",
						"description" => "Determines if the thumbnail should be cropped or not.",
						"display-order" => 1,
						"type" => "checkbox",
						"default" => true,
					],
					"mcloud-master-slider-thumb-width" => [
						"title" => "Override Thumb Width",
						"description" => "Override the slider's specified thumb width.  Set to 0 to use the slider's default width.",
						"display-order" => 1,
						"type" => "number",
						"min" => 0,
						"max" => 100000,
						"default" => 0,
					],
					"mcloud-master-slider-thumb-height" => [
						"title" => "Override Thumb Height",
						"description" => "Override the slider's specified thumb height.  Set to 0 to use the slider's default height.",
						"display-order" => 1,
						"type" => "number",
						"min" => 0,
						"max" => 100000,
						"default" => 0,
					],
				]
			],
			"ilab-media-cloud-smart-slider-3" => [
				"title" => "Smart Slider 3",
				"dynamic" => true,
				"description" => "The following controls Smart Slider 3 integration.",
				"options" => [
					"mcloud-smart-slider-path-prefix" => [
						"title" => "Upload Prefix",
						"description" => "When Smart Slider 3 resizes an image, Media Cloud will upload it to cloud storage.  This will prepend a prefix to any file uploaded to cloud storage.  For dynamically created prefixes, you can use the following variables: <code>@{site-name}</code>, <code>@{site-host}</code>, <code>@{site-id}</code>.",
						"display-order" => 1,
						"type" => "text-field"
					],
				]
			],
			"ilab-media-cloud-next-gen-gallery" => [
				"title" => "Next Generation Gallery",
				"dynamic" => true,
				"description" => "The following controls Next Generation Gallery integration.",
				"options" => [
					"mcloud-ngg-use-url-cache" => [
						"title" => "Use URL Cache",
						"description" => "Due to the way NGG works, to speed up things Media Cloud will cache the URLs for next gen gallery images.  If you are seeing issues where images aren't updating, turn this off.",
						"display-order" => 1,
						"type" => "checkbox",
						"default" => true,
					],
				]
			],
		]
	]
];