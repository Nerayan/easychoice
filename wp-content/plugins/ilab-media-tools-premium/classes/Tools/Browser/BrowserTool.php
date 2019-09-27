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

namespace ILAB\MediaCloud\Tools\Browser;

use GuzzleHttp\Client;
use ILAB\MediaCloud\Storage\StorageFile;
use ILAB\MediaCloud\Tools\Browser\Batch\ImportFromStorageBatchProcess;
use ILAB\MediaCloud\Tools\Storage\StorageTool;
use ILAB\MediaCloud\Tools\Tool;
use ILAB\MediaCloud\Tools\ToolsManager;
use ILAB\MediaCloud\Utilities\Environment;
use ILAB\MediaCloud\Utilities\Logging\Logger;
use ILAB\MediaCloud\Utilities\Tracker;
use ILAB\MediaCloud\Utilities\View;
use function ILAB\MediaCloud\Utilities\json_response;
use Illuminate\Support\Facades\Storage;

if (!defined( 'ABSPATH')) { header( 'Location: /'); die; }

/**
 * Class ImageSizeTool
 *
 * Tool for managing image sizes
 */
class BrowserTool extends Tool {
	private $multisiteEnabled = true;
	private $multisiteRoot = '';
	private $multisiteAllowUploads = true;
	private $multisiteAllowDeleting = true;

	public function __construct( $toolName, $toolInfo, $toolManager ) {
		parent::__construct( $toolName, $toolInfo, $toolManager );

		$this->multisiteEnabled = empty(Environment::Option('mcloud-network-browser-hide',null, false));
		$this->multisiteAllowUploads = Environment::Option('mcloud-network-browser-allow-uploads',null, true);
		$this->multisiteAllowDeleting = Environment::Option('mcloud-network-browser-allow-deleting',null, true);

		$lockToRoot = Environment::Option('mcloud-network-browser-lock-to-root',null, false);
		if (is_multisite() && $lockToRoot) {
			$dir = wp_upload_dir(null, true);
			$uploadRoot = WP_CONTENT_DIR.DIRECTORY_SEPARATOR.'uploads';

			$this->multisiteRoot = trailingslashit(ltrim(str_replace($uploadRoot, '', $dir['basedir']),DIRECTORY_SEPARATOR));
		}


		new ImportFromStorageBatchProcess();

		add_action('admin_enqueue_scripts', function(){

			wp_enqueue_media();

			wp_enqueue_style( 'wp-pointer' );
			wp_enqueue_script( 'wp-pointer' );
			wp_enqueue_script ( 'ilab-modal-js', ILAB_PUB_JS_URL. '/ilab-modal.js', ['jquery'], false, true );
			wp_enqueue_script ( 'ilab-media-tools-js', ILAB_PUB_JS_URL. '/ilab-media-tools.js', ['jquery'], false, true );
			wp_enqueue_script ( 'ilab-storage-browser-js', ILAB_PUB_JS_URL. '/ilab-storage-browser.js', ['jquery'], false, true );

		});

		add_action('wp_ajax_ilab_browser_select_directory', [$this, 'selectDirectory']);
		add_action('wp_ajax_ilab_browser_browser_select_directory', [$this, 'browserSelectDirectory']);
		add_action('wp_ajax_ilab_browser_create_directory', [$this, 'createDirectory']);
		add_action('wp_ajax_ilab_browser_delete', [$this, 'deleteItems']);
		add_action('wp_ajax_ilab_browser_file_list', [$this, 'listFiles']);
		add_action('wp_ajax_ilab_browser_import_file', [$this, 'importFile']);
		add_action('wp_ajax_ilab_browser_track', [$this, 'trackAction']);

		add_action('admin_menu', function() {
			if ($this->enabled()) {
				add_media_page('Media Cloud Storage Browser', 'Storage Browser', 'manage_options', 'media-tools-storage-browser', [
					$this,
					'renderBrowser'
				]);
			}
		});

	}

	public function registerMenu($top_menu_slug, $networkMode = false, $networkAdminMenu = false) {
		parent::registerMenu($top_menu_slug);

		if ($this->enabled()) {
			ToolsManager::instance()->addMultisiteTool($this);

			if (is_multisite() && !empty(Environment::Option('mcloud-network-browser-hide', null, false))) {
				return;
			}

			ToolsManager::instance()->insertToolSeparator();
			$this->options_page = 'media-tools-storage-browser';
			add_submenu_page($top_menu_slug, 'Media Cloud Storage Browser', 'Storage Browser', 'manage_options', 'media-tools-storage-browser', [
				$this,
				'renderBrowser'
			]);


		}
	}

	public function enabled() {
		$enabled = ToolsManager::instance()->toolEnabled('storage');

		if ($enabled && is_multisite() && !is_network_admin()) {
			$hide = Environment::Option('mcloud-network-browser-hide', null, false);
			$enabled = !$hide;
		}

		return $enabled;
	}

	public function renderBrowser() {
		$currentPath = (empty($_REQUEST['path'])) ? '' : $_REQUEST['path'];

		if (is_multisite() && !empty($this->multisiteRoot)) {
			if (strpos($currentPath, $this->multisiteRoot) === false) {
				$currentPath = $this->multisiteRoot;
			}
		}

		if ($currentPath == '/') {
			$currentPath = '';
		}

		/** @var StorageTool $storageTool */
		$storageTool = ToolsManager::instance()->tools['storage'];
		$files = $storageTool->client()->dir($currentPath);

		$directUploads = ToolsManager::instance()->toolEnabled('media-upload');

		if (!empty($currentPath)) {
			$pathParts = explode('/', $currentPath);
			array_pop($pathParts);
			array_pop($pathParts);
			$parentPath = implode('/', $pathParts);
			if (!empty($parentPath)) {
				$parentPath .= '/';
			}

			$parentDirectory = new StorageFile('DIR', $parentPath, '..');
			array_unshift($files, $parentDirectory);
		}

		$mtypes = array_values(get_allowed_mime_types(get_current_user_id()));
		$mtypes[] = 'image/psd';

		Tracker::trackView('Storage Browser', '/browser');

		echo View::render_view('storage/browser', [
			'title' => 'Cloud Storage Browser',
			'bucketName' => $storageTool->client()->bucket(),
			"path" => $currentPath,
			"directUploads" => $directUploads,
			'files' => $files,
			'allowUploads' => $this->multisiteAllowUploads,
			'allowDeleting' => $this->multisiteAllowDeleting,
			'allowedMimes' => $mtypes
		]);

	}

	public function selectDirectory() {
		if (!check_ajax_referer('storage-browser', 'nonce')) {
			json_response([
				'status' => 'error',
				'message' => 'Invalid nonce'
			]);
		}

		$currentPath = (empty($_POST['key'])) ? '' : $_POST['key'];
		$this->renderDirectory($currentPath);
	}

	public function browserSelectDirectory() {
		if (!check_ajax_referer('storage-browser', 'nonce')) {
			json_response([
				'status' => 'error',
				'message' => 'Invalid nonce'
			]);
		}

		$currentPath = (empty($_POST['key'])) ? '' : $_POST['key'];
		$this->renderDirectory($currentPath, true, true, false, false);
	}

	protected function renderDirectory($currentPath, $tableOnly = false, $hideCheckBoxes = false, $hideActions = false, $allowDeleting = true) {
		if (is_multisite() && !empty($this->multisiteRoot)) {
			if (strpos($currentPath, $this->multisiteRoot) === false) {
				$currentPath = $this->multisiteRoot;
			}
		}

		/** @var StorageTool $storageTool */
		$storageTool = ToolsManager::instance()->tools['storage'];
		$files = $storageTool->client()->dir($currentPath);

		if (!empty($currentPath)) {
			$pathParts = explode('/', $currentPath);
			array_pop($pathParts);
			array_pop($pathParts);
			$parentPath = implode('/', $pathParts);
			if (!empty($parentPath)) {
				$parentPath .= '/';
			}

			$parentDirectory = new StorageFile('DIR', $parentPath, '..');
			array_unshift($files, $parentDirectory);
		}

		$table = View::render_view('storage/browser-table', [
			'hideCheckBoxes' => $hideCheckBoxes,
			'hideActions' => $hideActions,
			'files' => $files,
			'allowUploads' => $this->multisiteAllowUploads,
			'allowDeleting' => ($allowDeleting && $this->multisiteAllowDeleting)
		]);

		$data = [
			'status' => 'ok',
			'table' => $table,
			'nextNonce' => wp_create_nonce('storage-browser')
		];

		if (empty($tableOnly)) {
			$header = View::render_view('storage/browser-header', [
				'bucketName' => $storageTool->client()->bucket(),
				'path' => $currentPath
			]);

			$data['header'] = $header;
		}

		json_response($data);
	}

	public function createDirectory() {
		if (!check_ajax_referer('storage-browser', 'nonce')) {
			json_response([
				'status' => 'error',
				'message' => 'Invalid nonce'
			]);
		}

		$currentPath = (empty($_POST['key'])) ? '' : $_POST['key'];
		if (!empty($currentPath)) {
			$currentPath = rtrim($currentPath, '/').'/';
		}

		$newDirectory = (empty($_POST['directory'])) ? '' : $_POST['directory'];

		Tracker::trackView('Storage Browser - Create Directory', '/browser/create');

		/** @var StorageTool $storageTool */
		$storageTool = ToolsManager::instance()->tools['storage'];
		if (!$storageTool->client()->createDirectory($currentPath.$newDirectory)) {
			json_response([
				'status' => 'error',
				'nextNonce' => wp_create_nonce('storage-browser')
			]);
		} else {
			$this->renderDirectory($currentPath);
		}
	}

	public function deleteItems() {
		if (!check_ajax_referer('storage-browser', 'nonce')) {
			json_response([
				'status' => 'error',
				'message' => 'Invalid nonce'
			]);
		}

		$currentPath = (empty($_POST['key'])) ? '' : $_POST['key'];

		if (empty($_POST['keys']) || !is_array($_POST['keys'])) {
			json_response([
				'status' => 'error',
				'message' => 'Missing keys'
			]);
		}

		Tracker::trackView('Storage Browser - Delete', '/browser/delete');

		/** @var StorageTool $storageTool */
		$storageTool = ToolsManager::instance()->tools['storage'];
		foreach($_REQUEST['keys'] as $key) {
			if (is_multisite() && !empty($this->multisiteRoot)) {
				if (strpos($key, $this->multisiteRoot) === false) {
					continue;
				}
			}

			if (strpos(strrev($key), '/') === 0) {
				$storageTool->client()->deleteDirectory($key);
			} else {
				$storageTool->client()->delete($key);
			}
		}

		$this->renderDirectory($currentPath);
	}

	public function listFiles() {
		if (!check_ajax_referer('storage-browser', 'nonce')) {
			json_response([
				'status' => 'error',
				'message' => 'Invalid nonce'
			]);
		}

		if (empty($_POST['keys']) || !is_array($_POST['keys'])) {
			json_response([
				'status' => 'error',
				'message' => 'Missing keys'
			]);
		}


		/** @var StorageTool $storageTool */
		$storageTool = ToolsManager::instance()->tools['storage'];

		$fileList = $storageTool->getFileList($_REQUEST['keys'],  (isset($_POST['skipThumbnails']) && ($_POST['skipThumbnails'] != "false")));

		json_response([
			'status' => 'ok',
			'files' => $fileList,
			'nextNonce' => wp_create_nonce('storage-browser')
		]);
	}

	public function importFile() {
		if (!check_ajax_referer('storage-browser', 'nonce')) {
			json_response([
				'status' => 'error',
				'message' => 'Invalid nonce'
			]);
		}

		$key = (empty($_POST['key'])) ? '' : $_POST['key'];
		if (is_multisite() && !empty($this->multisiteRoot)) {
			if (strpos($key, $this->multisiteRoot) === false) {
				json_response([
					'status' => 'error',
					'message' => 'Invalid path'
				]);
			}
		}

		if (empty($key)) {
			json_response([
				'status' => 'error',
				'message' => 'Missing key'
			]);
		}

		$thumbs = (isset($_POST['thumbs'])) ? $_POST['thumbs'] : [];
		$importOnly = (isset($_POST['importOnly']) && ($_POST['importOnly'] == 'true'));
		$preservePaths = (isset($_POST['preservePaths']) && ($_POST['preservePaths'] == 'true'));

		/** @var StorageTool $storageTool */
		$storageTool = ToolsManager::instance()->tools['storage'];
		$success = $storageTool->importFileFromStorage($key, $thumbs, $importOnly, $preservePaths);

		json_response([
			'status' => ($success) ? 'ok' : 'error',
			'nextNonce' => wp_create_nonce('storage-browser')
		]);
	}

	public function trackAction() {
		if (!check_ajax_referer('storage-browser', 'nonce')) {
			json_response([
				'status' => 'error',
				'message' => 'Invalid nonce'
			]);
		}

		if (!isset($_POST['track'])) {
			json_response([
				'status' => 'error',
				'message' => 'Missing track variable'
			]);
		}

		$track = $_POST['track'];
		if (!in_array($track, ['upload', 'import'])) {
			json_response([
				'status' => 'error',
				'message' => 'Invalid track'
			]);
		}

		$title = ($track == 'upload') ? 'Storage Browser - Upload' : 'Storage Browser - Import';
		Tracker::trackView($title, "/browser/{$track}");

		json_response([
			'status' => 'ok'
		]);
	}



}