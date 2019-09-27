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

namespace ILAB\MediaCloud\Tools\Storage\Batch;

use ILAB\MediaCloud\Tasks\BatchManager;
use ILAB\MediaCloud\Tools\BatchTool;
use function ILAB\MediaCloud\Utilities\json_response;

class RegenerateThumbnailBatchTool extends BatchTool {
    //region Properties
    /**
     * Name/ID of the batch
     * @return string
     */
    public static function BatchIdentifier() {
        return 'thumbnails';
    }

    /**
     * Title of the batch
     * @return string
     */
    public function title() {
        return "Rebuild Thumbnails";
    }

    public function menuTitle() {
        return "Rebuild Thumbnails";
    }

    /**
     * The prefix to use for action names
     * @return string
     */
    public function batchPrefix() {
        return 'ilab_regenerate_thumbnails';
    }

    /**
     * Fully qualified class name for the BatchProcess class
     * @return string
     */
    public static function BatchProcessClassName() {
        return "\\ILAB\\MediaCloud\\Tools\\Storage\\Batch\\RegenerateThumbnailBatchProcess";
    }

    /**
     * The view to render for instructions
     * @return string
     */
    public function instructionView() {
        return 'importer/instructions/regeneration';
    }

    /**
     * The menu slug for the tool
     * @return string
     */
    function menuSlug() {
        return 'media-tools-cloud-regeneration';
    }
    //endregion

    //region Bulk Actions
    /**
     * Registers any bulk actions for integeration into the media list
     * @param $actions array
     * @return array
     */
    public function registerBulkActions($actions) {
        $actions['ilab_regenerate_thumbnails'] = 'Regenerate Thumbnails';
        return $actions;
    }

    /**
     * Called to handle a bulk action
     *
     * @param $redirect_to
     * @param $action_name
     * @param $post_ids
     * @return string
     */
    public function handleBulkActions($redirect_to, $action_name, $post_ids) {
        if('ilab_regenerate_thumbnails' === $action_name) {
	        $result = $this->processBulkSelection($post_ids, false);
	        if (!empty($result)) {
		        return $result;
	        }
        }

        return $redirect_to;
    }
    //endregion

	//region Batch Actions
	/**
	 * Gets the post data to process for this batch.  Data is paged to minimize memory usage.
	 * @param $page
	 * @param bool $forceImages
	 * @param bool $allInfo
	 * @return array
	 */
	protected function getImportBatch($page, $forceImages = false, $allInfo = false) {
		$result = parent::getImportBatch($page, $forceImages, $allInfo);
		return $result;
	}
	//endregion

    //region Actions
    protected function filterPostArgs($args) {
        $args['post_mime_type'] ='image';
        return $args;
    }

    /**
     * Allows subclasses to filter the data used to render the tool
     * @param $data
     * @return array
     */
    protected function filterRenderData($data) {
        $data['disabledText'] = 'enable Storage';
        $data['commandLine'] = 'wp mediacloud regenerate';
        $data['commandTitle'] = 'Regenerate Thumbnails';
        $data['cancelCommandTitle'] = 'Cancel Regeneration';

	    $dynamicEnabled = apply_filters('media-cloud/dynamic-images/enabled', false);
	    if ($dynamicEnabled) {
	    	$data['warning'] = "You have Imgix or Dynamic Images enabled which makes using this tool fairly meaningless.  Only use this tool if you are planning to downgrade from Imgix or just want to make sure all the thumbnails have been generated.";
	    }

        return $data;
    }

    /**
     * Process the import manually.  $_POST will contain a field `post_id` for the post to process
     */
    public function manualAction() {
        if (!isset($_POST['id'])) {
            BatchManager::instance()->setErrorMessage('storage', 'Missing required post data.');
            json_response(['status' => 'error']);
        }

        $pid = $_POST['id'];
        $this->owner->regenerateFile($pid);

        json_response(["status" => 'ok']);
    }
    //endregion

    //region BatchToolInterface
    public function toolInfo() {
        return [
            'title' => 'Rebuild Thumbnails',
            'link' => admin_url('admin.php?page='.$this->menuSlug()),
            'description' => 'Rebuilds the thumbnails and various theme specified image sizes for the media in your media library.'
        ];
    }
    //endregion
}