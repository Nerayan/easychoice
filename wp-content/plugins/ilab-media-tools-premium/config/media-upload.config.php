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
	"id" => "media-upload",
	"name" => "Direct Uploads",
	"description" => "Provides an easy to use tool for uploading media directly to Amazon S3, Minio or Google Cloud Storage.",
	"class" => "ILAB\\MediaCloud\\Tools\\MediaUpload\\UploadTool",
	"dependencies" => ["storage"],
	"env" => "ILAB_MEDIA_UPLOAD_ENABLED",

	"settings" => [
		"options-page" => "media-tools-direct-upload",
		"options-group" => "ilab-media-direct-upload",
		"groups" => [
			"ilab-media-direct-upload-settings" => [
				"title" => "Upload Settings",
				"options" => [
					"mcloud-direct-uploads-integration" => [
						"title" => "Integrate with Media Library",
						"description" => "When uploading items through WordPress's media library, direct uploading is used.  When this option is turned off you will need to use the <strong>Cloud Upload</strong> page to perform direct uploads.",
						"display-order" => 10,
						"type" => "checkbox",
						"default" => true
					],
					"mcloud-direct-uploads-upload-images" => [
						"title" => "Direct Upload Images",
						"description" => "Enables direct uploads for image files.  You will need to have <a href='".admin_url('admin.php?page=media-cloud-settings&tab=imgix')."' target='_blank'>Imgix</a> or <a href='".admin_url('admin.php?page=media-cloud-settings&tab=glide')."' target='_blank'>Dynamic Images</a> enabled.  Also note that <strong>Upload Images</strong> must also be enabled in <a href='".admin_url('admin.php?page=media-cloud-settings&tab=storage')."' target='_blank'>Cloud Storage settings</a>.",
						"display-order" => 10,
						"type" => "checkbox",
						"default" => true
					],
					"mcloud-direct-uploads-upload-videos" => [
						"title" => "Direct Upload Video Files",
						"description" => "Enables direct uploads for video files.  <strong>Important!</strong>  WordPress will be unable to extract metadata about the video such as length and audio attributes without the <a href='#setting-mcloud-direct-uploads-use-ffprobe'>FFProbe option</a> enabled.",
						"display-order" => 10,
						"type" => "checkbox",
						"default" => true
					],
					"mcloud-direct-uploads-upload-audio" => [
						"title" => "Direct Upload Audio Files",
						"description" => "Enables direct uploads for audio files.  <strong>Upload Audio</strong> must also be enabled in <a href='".admin_url('admin.php?page=media-cloud-settings&tab=storage')."' target='_blank'>Cloud Storage settings</a> for audio direct uploads to work.",
						"display-order" => 10,
						"type" => "checkbox",
						"default" => false
					],
					"mcloud-direct-uploads-upload-documents" => [
						"title" => "Direct Upload Documents",
						"description" => "Enables direct uploads for non-image files such as Word documents, PDF files, zip files, etc.  Note that <strong>Upload Documents</strong> must also be enabled in <a href='".admin_url('admin.php?page=media-cloud-settings&tab=storage')."' target='_blank'>Cloud Storage settings</a>.",
						"display-order" => 10,
						"type" => "checkbox",
						"default" => true
					],
					"mcloud-direct-uploads-simultaneous-uploads" => [
						"title" => "Number of Simultaneous Uploads",
						"description" => "The maximum number of uploads to perform simultaneously.",
						"type" => "number",
						"default" => 4,
						"increment" => 1,
						"min" => 1,
						"max" => 8
					],
					"mcloud-direct-uploads-max-upload-size" => [
						"title" => "Maximum Upload Size",
						"description" => "The maximum upload size allowed for direct uploads in MB.  Set to 0 to use whatever PHP is set to, currently ".ini_get('upload_max_filesize').".",
						"type" => "number",
						"default" => 0,
						"increment" => 1,
						"min" => 0,
						"max" => 16000
					],
				]
			],
			"ilab-media-direct-upload-image-settings" => [
				"title" => "Direct Upload Image Settings",
				"options" => [
					"mcloud-direct-uploads-detect-faces" => [
						"title" => "Detect Faces",
						"description" => "Detects faces in the image.  Detected faces will be stored as additional metadata for the image.  If you are using Imgix or Dynamic Images, you can use this for cropping images centered on a face.  If you are relying on this functionality, the better option would be to use the <a href='admin.php?page=media-cloud-settings&tab=vision'>Vision</a> tool.  It is more accurate with less false positives.  If Vision is enabled, this setting is ignored in favor of Vision's results.",
						"display-order" => 8,
						"type" => "checkbox",
						"default" => false
					],
				]
			],
			"ilab-media-direct-upload-video-settings" => [
				"title" => "Direct Upload Video Settings",
				"options" => [
					"mcloud-direct-uploads-use-ffprobe" => [
						"title" => "Use FFProbe for Videos",
						"description" => "When enabled, uses FFProbe to extract video metadata such as length, size, codecs, etc.  You must have <a href='https://ffmpeg.org/ffprobe.html' target='_blank'>fffprobe</a> installed on your server and have the PHP function <a href='https://www.php.net/manual/en/function.shell-exec.php' target='_blank'><code>shell_exec</code></a> enabled.",
						"display-order" => 8,
						"type" => "checkbox",
						"default" => false
					],
				]
			]
		]
	]
];