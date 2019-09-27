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
	"id" => "assets",
	"name" => "Assets",
	"description" => "Store and serve your theme and WordPress assets from cloud storage.",
	"class" => "ILAB\\MediaCloud\\Tools\\Assets\\AssetsTool",
	"dependencies" => ['storage'],
	"env" => "ILAB_MEDIA_ASSETS_ENABLED",
	"actions" => [
		"update-build-version" => [
			"name" => "Update Build Version",
			"method" => "updateBuildVersion"
		],
		"reset-cache" => [
			"name" => "Reset Cache",
			"method" => "resetCache"
		],
	],
	"settings" => [
		"options-page" => "media-tools-assets",
		"options-group" => "ilab-media-assets-group",
		"groups" => [
			"ilab-media-asset-settings" => [
				"title" => "Push Settings",
				"description" => "These settings control pushing assets to cloud storage.  For pull mode, simply turn off these settings and enter a CDN url in the <a href='#cdn-settings'>CDN Settings</a>.",
				"options" => [
					"mcloud-assets-store-css" => [
						"title" => "Push CSS Assets",
						"description" => "Theme and WordPress related CSS files will be copied to cloud storage and served from there.",
						"type" => "checkbox",
						"default" => false
					],
					"mcloud-assets-store-js" => [
						"title" => "Push Javascript Assets",
						"description" => "Theme and WordPress related javascript files will be copied to cloud storage and served from there.",
						"type" => "checkbox",
						"default" => false
					],
					"mcloud-assets-process-live" => [
						"title" => "Live Push Processing",
						"description" => "Assets will be uploaded to cloud storage as pages are browsed on the live site, but only if they don't already exist on cloud storage or their version number has changed.",
						"type" => "checkbox",
						"default" => true
					],
					"mcloud-assets-cache-control" => [
						"title" => "Cache Control",
						"description" => "Sets the Cache-Control metadata for assets, e.g. <code>public,max-age=2592000</code>.",
						"type" => "text-field"
					],
					"mcloud-assets-expires" => [
						"title" => "Content Expiration",
						"description" => "Sets the Expire metadata for assets.  This is the number of minutes from the date of assets.",
						"type" => "text-field"
					],
					"mcloud-assets-gzip" => [
						"title" => "GZIP CSS and Javascript files",
						"description" => "Some storage providers don't serve CSS or javascript files using gzip compression, when this option is enabled it will gzip the files before pushing them to your cloud storage provider.  This works in push mode only.",
						"type" => "checkbox",
						"default" => false
					],
				],
			],
			"ilab-media-asset-performance" => [
				"title" => "CDN Settings",
				"options" => [
					"mcloud-assets-cdn-base" => [
						"title" => "CDN Base URL",
						"description" => "This is the base URL for your CDN for serving assets, including the scheme (meaning the http/https part).  If you don't have a CDN, you can simply leave blank to use the cloud storage URL.",
						"type" => "text-field"
					],
				],
			],
			"ilab-media-asset-versioning" => [
				"title" => "Versioning Settings",
				"options" => [
					"mcloud-assets-use-build-version" => [
						"title" => "Use Build Version",
						"description" => "Assets that have been enqueued in WordPress without a specified version will normally be skipped when pushing/pulling assets.  When this option is enabled, this tool will use a custom build number for the version.  You can update this version by clicking on <strong>Update Build Version</strong> button below.",
						"type" => "checkbox",
						"default" => false
					],
				],
			],
			"ilab-media-asset-debug" => [
				"title" => "Debug Settings",
				"options" => [
					"mcloud-assets-log-warnings" => [
						"title" => "Log Warnings",
						"description" => "When set to true and Media Cloud Debugging is enabled, this will log warnings about files that couldn't be uploaded to the error log.",
						"type" => "checkbox",
						"default" => true
					],
				],
			],
		]
	]
];
