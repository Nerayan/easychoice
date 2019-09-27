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

use ILAB\MediaCloud\Storage\StorageException;
use ILAB\MediaCloud\Storage\StorageSettings;
use ILAB\MediaCloud\Tasks\BatchManager;
use ILAB\MediaCloud\Tools\BatchTool;
use ILAB\MediaCloud\Tools\Storage\StorageTool;
use ILAB\MediaCloud\Tools\ToolsManager;
use function ILAB\MediaCloud\Utilities\json_response;
use Mimey\MimeTypes;

class ImportFromStorageBatchTool extends BatchTool {
    //region Properties
	/**
	 * Name/ID of the batch
	 * @return string|null
	 */
	static public function BatchType() {
		return 'keys';
	}

    /**
     * Name/ID of the batch
     * @return string
     */
    public static function BatchIdentifier() {
        return 'import-storage';
    }

    /**
     * Title of the batch
     * @return string
     */
    public function title() {
        return "Import From Cloud";
    }

    /**
     * The prefix to use for action names
     * @return string
     */
    public function batchPrefix() {
        return 'ilab_storage_exporter';
    }

    /**
     * Fully qualified class name for the BatchProcess class
     * @return string
     */
    public static function BatchProcessClassName() {
        return "\\ILAB\\MediaCloud\\Tools\\Browser\\Batch\\ImportFromStorageBatchProcess";
    }

    /**
     * The view to render for instructions
     * @return string
     */
    public function instructionView() {
        return 'importer/instructions/storage-exporter';
    }

    /**
     * The menu slug for the tool
     * @return string
     */
    function menuSlug() {
        return 'media-tools-s3-exporter';
    }
    //endregion

	//region Batch Actions
	/**
	 * Gets the post data to process for this batch.  Data is paged to minimize memory usage.
	 * @param $page
	 * @param bool $forceImages
	 * @param bool $allInfo
	 *
	 * @return array
	 * @throws StorageException
	 */
	protected function getImportBatch($page, $forceImages = false, $allInfo = false) {
		/** @var StorageTool $storageTool */
		$storageTool = ToolsManager::instance()->tools['storage'];

		$key = (isset($_POST['import-path'])) ? $_POST['import-path'] : '';
		if ($key == '/') {
			$key = '';
		}

		if ($page < 2) {
			$files = $storageTool->getFileList([$key], (isset($_POST['skip-thumbnails']) && ($_POST['skip-thumbnails'] == "on")));

			$mimey = new MimeTypes();

			for($i = 0; $i < count($files); $i++) {
				if (count($files[$i]['thumbs']) == 0) {
					$base = basename($files[$i]['key']);
					$ext = pathinfo($base, PATHINFO_EXTENSION);
					$mimeType = $mimey->getMimeType($ext);

					if (in_array($mimeType, ['image/gif', 'image/jpg', 'image/jpeg', 'image/png'])) {
						$files[$i]['thumb'] = $storageTool->client()->url($files[$i]['key']);
						$files[$i]['icon'] = false;
					} else {
						$files[$i]['thumb'] = wp_mime_type_icon($mimeType);
						$files[$i]['icon'] = true;
					}
				} else {
					foreach($files[$i]['thumbs'] as $thumb) {
						if (strpos($thumb, '150x150.') !== false) {
							$files[$i]['thumb'] = $storageTool->client()->url($thumb);
							$files[$i]['icon'] = false;
							break;
						}
					}

					if (empty($files[$i]['thumb'])) {
						$base = basename($files[$i]['key']);
						$ext = pathinfo($base, PATHINFO_EXTENSION);
						$mimeType = $mimey->getMimeType($ext);

						if (in_array($mimeType, ['image/gif', 'image/jpg', 'image/jpeg', 'image/png'])) {
							$files[$i]['thumb'] = $storageTool->client()->url($files[$i]['key']);
							$files[$i]['icon'] = false;
						} else {
							$files[$i]['thumb'] = wp_mime_type_icon($mimeType);
							$files[$i]['icon'] = true;
						}
					}
				}
			}
		} else {
			$files = [];
		}

		$options = [
			'import-only' => (isset($_POST['skip-download']) && ($_POST['skip-download'] == 'on')),
			'preserve-paths' => (isset($_POST['preserve-paths']) && ($_POST['preserve-paths'] == 'on')),
		];

		return [
			'posts' => $files,
			'total' => count($files),
			'pages' => 1,
			'options' => $options,
			'shouldRun' => false,
			'fromSelection' => false
		];
	}
	//endregion

    //region Actions
	/**
     * Allows subclasses to filter the data used to render the tool
     * @param $data
     * @return array
     */
    protected function filterRenderData($data) {
        $data['disabledText'] = 'enable Storage';
//        $data['commandLine'] = 'wp mediacloud import [--limit=<number>] [--offset=<number>] [--page=<number>] [--paths=preserve|replace|prepend] [--skip-thumbnails] [--order-by=date|title|filename] [--order=asc|desc]';
//	    $data['commandLink'] = admin_url('admin.php?page=media-cloud-docs&doc-page=advanced/command-line#import');
	    $data['commandTitle'] = 'Import From Cloud Storage';
        $data['cancelCommandTitle'] = 'Cancel Import';

        $data['options'] = [
        	'import-path' => [
        	    "title" => "Import Path",
		        "description" => "The folder on cloud storage to import.",
		        "type" => "browser",
		        "default" => "/"
	        ],
	        'skip-download' => [
		        "title" => "Import Only",
		        "description" => "Don't download, import to database only.",
		        "type" => "checkbox",
		        "default" => false
	        ],
	        'preserve-paths' => [
		        "title" => "Preserve Paths",
		        "description" => "When downloading images, maintain the directory structure that is on cloud storage.",
		        "type" => "checkbox",
		        "default" => false
	        ],
	        'skip-thumbnails' => [
		        "title" => "Skip Thumbnails",
		        "description" => "Skips any images that look like they might be thumbnails. If this option is on, you may import images that are thumbnails but they will be treated as individual images.",
		        "type" => "checkbox",
		        "default" => true
	        ],
        ];


        return $data;
    }

    protected function extractPostIds($posts) {
	    return $posts;
    }

	/**
     * Process the import manually.  $_POST will contain a field `post_id` for the post to process
     */
    public function manualAction() {
        if (!isset($_POST['id'])) {
            BatchManager::instance()->setErrorMessage('storage', 'Missing required post data.');
            json_response(['status' => 'error']);
        }

	    $thumbs = (isset($_POST['thumbs'])) ? $_POST['thumbs'] : [];
	    $importOnly = (isset($_POST['skip-download']) && ($_POST['skip-download'] == 'on'));
	    $preservePaths = (isset($_POST['preserve-paths']) && ($_POST['preserve-paths'] == 'on'));

	    /** @var StorageTool $storageTool */
	    $storageTool = ToolsManager::instance()->tools['storage'];
	    $success = $storageTool->importFileFromStorage($_POST['id'], $thumbs, $importOnly, $preservePaths);

        json_response(["status" => 'ok']);
    }
    //endregion

    //region BatchToolInterface
    public function toolInfo() {
        return [
          'title' => 'Storage Importer',
          'link' => admin_url('admin.php?page='.$this->menuSlug()),
          'description' => 'Uploads your existing media library to Amazon S3, Google Cloud Storage or any other storage provider that you have configured.'
        ];
    }
    //endregion
}