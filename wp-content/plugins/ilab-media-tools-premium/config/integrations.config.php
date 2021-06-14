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
	"class" => "MediaCloud\\Plugin\\Tools\\Integrations\\IntegrationsTool",
	"exclude" => true,
	"dependencies" => [],
	"env" => "ILAB_MEDIA_INTEGRATIONS_ENABLED",  // this is always enabled btw
	"plugins" => [
		'master-slider/master-slider.php' => "\\MediaCloud\\Plugin\\Tools\\Integrations\\PlugIns\\MasterSliderIntegration",
		'ml-slider/ml-slider.php' => "\\MediaCloud\\Plugin\\Tools\\Integrations\\PlugIns\\MetaSliderIntegeration",
		'ml-slider-pro/ml-slider-pro.php' => "\\MediaCloud\\Plugin\\Tools\\Integrations\\PlugIns\\MetaSliderIntegeration",
		'smart-slider-3/smart-slider-3.php' => "\\MediaCloud\\Plugin\\Tools\\Integrations\\PlugIns\\SmartSliderIntegration",
		'ultimate-member/ultimate-member.php' => "\\MediaCloud\\Plugin\\Tools\\Integrations\\PlugIns\\UltimateMemberIntegration",
		'powerpress/powerpress.php' => "\\MediaCloud\\Plugin\\Tools\\Integrations\\PlugIns\\BlubrryIntegration",
		'profilegrid-user-profiles-groups-and-communities/profile-magic.php' => "\\MediaCloud\\Plugin\\Tools\\Integrations\\PlugIns\\ProfileGridIntegration",
	],
	"CLI" => [
		"\\MediaCloud\\Plugin\\Tools\\Integrations\\CLI\\IntegrationsCommand",
		"\\MediaCloud\\Plugin\\Tools\\Integrations\\PlugIns\\Elementor\\CLI\\ElementorCommand",
	],
	"classes" => [
		"\\BuddyPress" => "\\MediaCloud\\Plugin\\Tools\\Integrations\\PlugIns\\BuddyPress\\BuddyPressIntegration",
		"\\C_NextGEN_Bootstrap" => "\\MediaCloud\\Plugin\\Tools\\Integrations\\PlugIns\\NextGenGallery\\NextGenGalleryIntegration",
		"\\Elementor\\Plugin" => "\\MediaCloud\\Plugin\\Tools\\Integrations\\PlugIns\\Elementor\\ElementorIntegration",
		"\\FooGallery" => "\\MediaCloud\\Plugin\\Tools\\Integrations\\PlugIns\\FooGalleryIntegration",
		"\\SFWD_LMS" => "\\MediaCloud\\Plugin\\Tools\\Integrations\\PlugIns\\LearnDashIntegration",
		"\\Google\\Web_Stories\\Model\\Story" => "\\MediaCloud\\Plugin\\Tools\\Integrations\\PlugIns\\WebStories\\WebStoriesIntegration",
		"\\WooCommerce_Product_Search" => "\\MediaCloud\\Plugin\\Tools\\Integrations\\PlugIns\\WooCommerceProductSearchIntegration",
	],
	"settings" => [
		"options-page" => "media-tools-integrations",
		"options-group" => "ilab-media-integrations",
		"groups" => [
			"ilab-media-cloud-woo-commerce" => [
				"title" => "WooCommerce Settings",
				"doc_link" => 'https://support.mediacloud.press/articles/documentation/integrations/woocommerce-and-easy-digital-downloads',
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
				"doc_link" => 'https://support.mediacloud.press/articles/documentation/integrations/woocommerce-and-easy-digital-downloads',
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
					],
					"mcloud-edd-download-original-image" => [
						"title" => "Download Original Image",
						"description" => "If the download is an image, enabling this will download the original unscaled image.",
						"display-order" => 2,
						"type" => "checkbox",
						"default" => false,
					]
				]
			],
			"ilab-media-cloud-buddypress" => [
				"title" => "BuddyPress",
				"doc_link" => 'https://support.mediacloud.press/articles/documentation/integrations/buddypress',
				"dynamic" => true,
				"description" => "The following controls BuddyPress integration.",
				"options" => [
					"mcloud-buddypress-enabled" => [
						"title" => "Enable BuddyPress Integration",
						"description" => "Enabling this will allow user uploads such as avatars and cover images to be moved and served from the cloud.",
						"display-order" => 1,
						"type" => "checkbox",
						"default" => true,
					],
					"mcloud-buddypress-realtime" => [
						"title" => "Enable Real Time Processing",
						"description" => "Enabling this will move avatars and cover images the first time they are requested on the front-end of your site.  If disabled, you must use the <a href='".admin_url('admin.php?page=mcloud-task-buddypress-migrate')."'>BuddyPress Migrate Uploads</a> task to upload those things to cloud storage.  The best plan is to leave this enabled and run the task after installing Media Cloud for the first time.",
						"display-order" => 2,
						"type" => "checkbox",
						"default" => true,
					],
					"mcloud-buddypress-delete-uploads" => [
						"title" => "Delete Uploads",
						"description" => "When enabled, user uploads will be deleted after being transferred to the cloud.  You must have background tasks working to use this feature.",
						"display-order" => 3,
						"type" => "checkbox",
						"default" => false,
					],
				]
			],
			"ilab-media-cloud-foo-gallery" => [
				"title" => "FooGallery",
				"doc_link" => 'https://support.mediacloud.press/articles/documentation/integrations/foo-gallery',
				"dynamic" => true,
				"description" => "The following controls FooGallery integration.",
				"options" => [
					"mcloud-foogallery-storage-prefix" => [
						"title" => "Upload Path",
						"display-order" => 10,
						"description" => "This will set the upload path to store gallery images on cloud storage.  Leave blank to use the FooGallery's default.  For dynamically created paths, you can use the following variables: <code>@{date:format}</code>, <code>@{site-name}</code>, <code>@{site-host}</code>, <code>@{site-id}</code>, <code>@{user-name}</code>, <code>@{unique-id}</code>, <code>@{unique-path}</code>.  For the date token, format is any format string that you can use with php's <a href='http://php.net/manual/en/function.date.php' target='_blank'>date()</a> function.  WordPress's default upload path would look like: <code>@{date:Y/m}</code>.",
						"type" => "upload-path"
					],
					"mcloud-foogallery-live-migration" => [
						"title" => "Live Migrations",
						"description" => "When this is enabled, Media Cloud will migrate the generated thumbnails for your old galleries the first time they are viewed.  This can slow down page loads, but will only happen once.  This is currently the only method to migrate any galleries you created before you activated Media Cloud.",
						"display-order" => 1,
						"type" => "checkbox",
						"default" => true,
					],
				]
			],
			"ilab-media-cloud-master-slider" => [
				"title" => "Master Slider",
				"doc_link" => 'https://support.mediacloud.press/articles/documentation/integrations/master-slider',
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
				"doc_link" => 'https://support.mediacloud.press/articles/documentation/integrations/smart-slider-3',
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
				"doc_link" => 'https://support.mediacloud.press/articles/documentation/integrations/next-generation-gallery',
				"dynamic" => true,
				"description" => "The following controls Next Generation Gallery integration.",
				"options" => [
					"mcloud-ngg-delete-uploads" => [
						"title" => "Delete Uploaded Files",
						"description" => "When enabled, Media Cloud will delete gallery images that have been uploaded to cloud storage",
						"display-order" => 1,
						"type" => "checkbox",
						"default" => false,
					],
					"mcloud-ngg-queue-delete-uploads" => [
						"title" => "Queue Delete Uploaded Files",
						"description" => "When this option is enabled, uploads won't be deleted right away, they will be queued for deletion two to five minutes later.  If <strong>Delete From Storage</strong> is disabled, this setting is ignored.",
						"display-order" => 1,
						"type" => "checkbox",
						"default" => true,
					],
					"mcloud-ngg-storage-prefix" => [
						"title" => "Upload Path",
						"display-order" => 10,
						"description" => "This will set the upload path to store gallery images on cloud storage.  Leave blank to use the NextGen Gallery's default.  For dynamically created paths, you can use the following variables: <code>@{date:format}</code>, <code>@{site-name}</code>, <code>@{site-host}</code>, <code>@{site-id}</code>, <code>@{user-name}</code>, <code>@{unique-id}</code>, <code>@{unique-path}</code>.  For the date token, format is any format string that you can use with php's <a href='http://php.net/manual/en/function.date.php' target='_blank'>date()</a> function.  WordPress's default upload path would look like: <code>@{date:Y/m}</code>.",
						"type" => "upload-path"
					],
				]
			],
			"ilab-media-cloud-elementor" => [
				"title" => "Elementor",
				"doc_link" => 'https://support.mediacloud.press/articles/documentation/integrations/elementor',
				"dynamic" => true,
				"description" => "The following controls Elementor integration.",
				"options" => [
					"mcloud-elementor-auto-update" => [
						"title" => "Auto Update Elementor",
						"description" => "Enabling this will auto-update your Elementor page data to insure that any image reference is pointed to the right image URL.  This update is only triggered after performing a migration or import using one of the Media Cloud batch tools.",
						"display-order" => 1,
						"type" => "checkbox",
						"default" => false,
					],
					"mcloud-elementor-update-build" => [
						"title" => "Update Asset Build",
						"description" => "If you are using the assets feature, enabling this will auto-update the asset build version and clear the asset cache when you edit and update an Elementor page or post.",
						"display-order" => 1,
						"type" => "checkbox",
						"default" => false,
					],
				]
			],
		]
	]
];