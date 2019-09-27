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

namespace ILAB\MediaCloud\Tools\MediaUpload;

use ILAB\MediaCloud\Storage\StorageInterface;
use ILAB\MediaCloud\Storage\StorageManager;
use ILAB\MediaCloud\Storage\StorageSettings;
use ILAB\MediaCloud\Tools\Storage\StorageTool;
use ILAB\MediaCloud\Tools\Tool;
use ILAB\MediaCloud\Tools\ToolsManager;
use ILAB\MediaCloud\Utilities\Environment;
use function ILAB\MediaCloud\Utilities\gen_uuid;
use ILAB\MediaCloud\Utilities\NoticeManager;
use ILAB\MediaCloud\Utilities\VideoProbe;
use ILAB\MediaCloud\Utilities\View;
use function ILAB\MediaCloud\Utilities\arrayPath;
use function ILAB\MediaCloud\Utilities\json_response;

if(!defined('ABSPATH')) {
	header('Location: /');
	die;
}

/**
 * Class ILabMediaUploadTool
 *
 * Video Tool.
 */
class UploadTool extends Tool {
	//region Class Variables
	/** @var StorageTool */
	private $storageTool;

	/** @var bool  */
	private $integrationMode = true;

	/** @var int  */
	private $maxUploads = 4;

	/** @var bool */
	private $detectFaces;

	/** @var bool */
	private $uploadImages = true;

	/** @var bool */
	private $uploadAudio = true;

	/** @var bool */
	private $uploadVideo = true;

	/** @var bool */
	private $uploadDocuments = true;

	/** @var bool */
	private $useFFProbe = true;

	/** @var int */
	private $maxUploadSize = 0;

	//endregion

    public function __construct($toolName, $toolInfo, $toolManager) {
        parent::__construct($toolName, $toolInfo, $toolManager);

        $this->testForBadPlugins();
        $this->testForUselessPlugins();

	    $this->integrationMode = Environment::Option('mcloud-direct-uploads-integration', null, true);
	    $this->maxUploads = Environment::Option('mcloud-direct-uploads-simultaneous-uploads', null, 4);
	    $this->detectFaces = Environment::Option('mcloud-direct-uploads-detect-faces', null, false);

	    $this->uploadImages = Environment::Option('mcloud-direct-uploads-upload-images', null, true);
	    $this->uploadAudio = Environment::Option('mcloud-direct-uploads-upload-audio', null, true);
	    $this->uploadVideo = Environment::Option('mcloud-direct-uploads-upload-videos', null, true);
	    $this->uploadDocuments = Environment::Option('mcloud-direct-uploads-upload-documents', null, true);

	    $this->useFFProbe = Environment::Option('mcloud-direct-uploads-use-ffprobe', null, false);

	    $this->maxUploadSize = Environment::Option('mcloud-direct-uploads-max-upload-size', null, 0);


	    if (!function_exists('curl_multi_init')) {
		    NoticeManager::instance()->displayAdminNotice('error', "You are missing <a href='http://php.net/manual/en/curl.installation.php' target='_blank'>cURL support</a> which can cause issues with various Media Cloud features such as direct upload.  You should install cURL support before using this plugin.", true, 'media-cloud-curl-warning');
	    }
    }

    //region Tool Overrides
	public function setup() {
		parent::setup();

		if ($this->enabled()) {
			$this->storageTool = ToolsManager::instance()->tools['storage'];

            add_action('init', function() {
                if (current_user_can('upload_files')) {
	                $duUrl = admin_url('admin.php?page=media-cloud-settings&tab=media-upload');
	                $dynamicEnabled = apply_filters('media-cloud/dynamic-images/enabled', false);
	                if ($this->uploadImages && !$dynamicEnabled) {
		                $imgixUrl = admin_url('admin.php?page=media-cloud-settings&tab=imgix');
		                $diUrl = admin_url('admin.php?page=media-cloud-settings&tab=glide');
		                NoticeManager::instance()->displayAdminNotice('warning', "You have <a href='{$duUrl}'>direct uploads</a> enabled for images but you are not using <a href='{$imgixUrl}'>Imgix</a> or <a href='{$diUrl}'>Dynamic Images</a>.  In order to use <a href='{$duUrl}'>direct uploads</a> with images, you'll need either feature setup and enabled.  Until then, all image uploads will be uploaded to your WordPress server first.", true, 'media-cloud-direct-uploads-images-no-imgix-warning', 1);
	                }

	                if ($this->useFFProbe) {
	                    if (!VideoProbe::instance()->enabled()) {
		                    NoticeManager::instance()->displayAdminNotice('warning', "You have <a href='{$duUrl}'>Use FFProbe</a> enabled in Direct Upload settings, but FFProbe is not installed or configured properly.  The error message is: ".VideoProbe::instance()->error(), true, 'media-cloud-direct-uploads-images-no-imgix-warning', 1);
                        }
                    }


	                $this->setupAdmin();
                    $this->setupAdminAjax();
                    $this->hookupUploadUI();

	                add_filter('plupload_default_settings', function($defaults) use ($dynamicEnabled) {
		                if ($this->uploadImages && $dynamicEnabled && $this->uploadAudio && $this->uploadVideo && $this->uploadDocuments) {
			                unset($defaults['filters']['max_file_size']);
		                } else if ($this->maxUploadSize > 0) {
		                    $defaults['filters']['max_file_size'] = ($this->maxUploadSize * 1024 * 1024).'b';
                        }

		                return $defaults;
	                });
                }
            });
        }
	}

	public function enabled() {
		if(!parent::enabled()) {
			return false;
		}

		if (!function_exists('curl_multi_init')) {
			return false;
		}

		$client = StorageManager::storageInstance();
		if (!$client->supportsDirectUploads()) {
			NoticeManager::instance()->displayAdminNotice('error', "Your cloud storage provider does not support direct uploads.", true, 'media-cloud-'.StorageManager::driver().'-no-direct-uploads', 7);
		    return false;
        }

		return true;
	}

	public function hasSettings() {
		return true;
	}
	//endregion

	//region Admin Setup

    /**
     * Register Menus
     * @param $top_menu_slug
     */
    public function registerMenu($top_menu_slug, $networkMode = false, $networkAdminMenu = false) {
        parent::registerMenu($top_menu_slug);

        if($this->enabled() && ((!$networkMode && !$networkAdminMenu) || ($networkMode && !$networkAdminMenu)) && (!$this->integrationMode)) {
            ToolsManager::instance()->insertToolSeparator();
            ToolsManager::instance()->addMultisiteTool($this);
            $this->options_page = 'media-cloud-upload-admin';
            add_submenu_page($top_menu_slug, 'Media Cloud Upload', 'Cloud Upload', 'upload_files', 'media-cloud-upload-admin', [
                $this,
                'renderSettings'
            ]);
        }
    }

	/**
	 * Setup upload UI
	 */
	public function setupAdmin() {

		add_action('admin_footer', function() {
			$imgixDetectFaces = apply_filters('media-cloud/imgix/detect-faces', false);
			$visionDetectFaces =  apply_filters('media-cloud/vision/detect-faces', false);

			if(current_user_can('upload_files') && $this->enabled()) {
				if (($this->detectFaces) && (!$imgixDetectFaces && !$visionDetectFaces)) {
					?>
					<script>
                        var LocalVisionMediaURL = '<?php echo ILAB_PUB_URL.'/models/'; ?>';
					</script>
					<?php
				}
			}
		});

		add_action('admin_enqueue_scripts', function() {
			$imgixDetectFaces = apply_filters('media-cloud/imgix/detect-faces', false);
			$visionDetectFaces =  apply_filters('media-cloud/vision/detect-faces', false);
			if(current_user_can('upload_files') && $this->enabled()) {
				wp_enqueue_script('wp-util');

				if (($this->detectFaces) && (!$imgixDetectFaces && !$visionDetectFaces)) {
					wp_enqueue_script('face-api-js', ILAB_PUB_JS_URL . '/face-api.js', ['jquery', 'wp-util'], false, true);
					wp_enqueue_script('ilab-face-detect-js', ILAB_PUB_JS_URL . '/ilab-face-detect.js', ['face-api-js'], false, true);
				}

				if ($this->integrationMode) {
					wp_enqueue_script('ilab-media-direct-upload-js', ILAB_PUB_JS_URL . '/ilab-media-direct-upload.js', ['jquery', 'wp-util'], false, true);
				}

				wp_enqueue_script('ilab-media-upload-js', ILAB_PUB_JS_URL . '/ilab-media-upload.js', ['jquery', 'wp-util'], false, true);

				$this->storageTool->client()->enqueueUploaderScripts();
			}
		});

		add_action('admin_menu', function() {
			if(current_user_can('upload_files')) {
				if($this->enabled()) {
					if ($this->integrationMode) {
						remove_submenu_page('upload.php', 'media-new.php');

						add_media_page('Cloud Upload', 'Add New', 'upload_files', 'media-cloud-upload', [
							$this,
							'renderSettings'
						]);
					} else {
						add_media_page('Cloud Upload', 'Cloud Upload', 'upload_files', 'media-cloud-upload', [
							$this,
							'renderSettings'
						]);
					}
				}
			}
		});
	}

	/**
	 * Install Ajax Endpoints
	 */
	public function setupAdminAjax() {
		add_action('wp_ajax_ilab_upload_prepare', function() {
			$this->prepareUpload();
		});

		add_action('wp_ajax_ilab_upload_import_cloud_file', function() {
			$this->importUploadedFile();
		});

		add_action('wp_ajax_ilab_add_face_data', function() {
			$this->addFaceData();
		});

		add_action('wp_ajax_ilab_upload_process_batch', function() {
			$this->processUploadBatch();
		});

		add_action('wp_ajax_ilab_upload_attachment_info', function() {
			$postId = $_POST['postId'];

			json_response(wp_prepare_attachment_for_js($postId));
		});
	}

	public function hookupUploadUI() {
		add_action('admin_footer', function() {
			if(current_user_can('upload_files')) {
				$this->doRenderDirectUploadSettings();
				include ILAB_VIEW_DIR . '/upload/ilab-media-direct-upload.php';
			}
		});

		add_action('customize_controls_enqueue_scripts', function() {
			if(current_user_can('upload_files')) {
				$this->doRenderDirectUploadSettings();
				include ILAB_VIEW_DIR . '/upload/ilab-media-direct-upload.php';
			}
		});
	}
	//endregion

	//region Ajax Endpoints
    private function processUploadBatch() {
	    if(!current_user_can('upload_files')) {
		    json_response(["status" => "error", "message" => "Current user can't upload."]);
	    }

	    if (!isset($_POST['batch'])) {
		    json_response(["status" => "error", "message" => "Invalid batch."]);
        }

	    if (!is_array($_POST['batch'])) {
		    json_response(["status" => "error", "message" => "Invalid batch."]);
        }

	    do_action('media-cloud/direct-uploads/process-batch', $_POST['batch']);

	    json_response(["status" => "ok"]);
    }

    private function addFaceData() {
	    if(!current_user_can('upload_files')) {
		    json_response(["status" => "error", "message" => "Current user can't upload."]);
	    }

	    if(empty($_POST['post_id'])) {
		    json_response(['status' => 'error', 'message' => 'Missing post.']);
	    }

	    if(empty($_POST['faces'])) {
		    json_response(['status' => 'error', 'message' => 'Missing faces data.']);
	    }

	    $postId = $_POST['post_id'];
	    $faces = arrayPath($_POST, 'faces', null);

	    /** @var StorageTool $storageTool */
	    $storageTool = ToolsManager::instance()->tools['storage'];
	    if (!$storageTool->enabled()) {
		    json_response(['status' => 'error', 'message' => 'Storage not enabled.']);
	    }

        if (!empty($faces)) {
            $meta = wp_get_attachment_metadata($postId);

            if (empty($meta)) {
	            json_response(['status' => 'error', 'message' => 'Invalid post ID.']);
            }

            $meta['faces'] = $faces;
            update_post_meta($postId, '_wp_attachment_metadata', $meta);

            json_response(['status' => 'success']);
	    } else {
		    json_response(['status' => 'error', 'message' => 'Faces is empty.']);
	    }
    }

	/**
	 * Called after a file has been uploaded and needs to be imported into the system.
	 */
	private function importUploadedFile() {
		if(!current_user_can('upload_files')) {
			json_response(["status" => "error", "message" => "Current user can't upload."]);
		}

		if(empty($_POST['key'])) {
			json_response(['status' => 'error', 'message' => 'Missing key.']);
		}

		$key = $_POST['key'];
		$faces = arrayPath($_POST, 'faces', null);

		$info = $this->storageTool->client()->info($key);

		$unknownMimes = [
			'application/octet-stream',
			'application/binary',
			'unknown/unknown'
		];

		/** @var StorageTool $storageTool */
		$storageTool = ToolsManager::instance()->tools['storage'];
		if (!$storageTool->enabled()) {
			json_response(['status' => 'error', 'message' => 'Storage not enabled.']);
        }

		if(!empty($info->mimeType()) && !in_array($info->mimeType(), $unknownMimes)) {
			$result = $storageTool->importAttachmentFromStorage($info);
			if($result) {
				$doUpdateMeta = false;
				$meta = wp_get_attachment_metadata($result['id']);

			    if (isset($_POST['metadata'])) {
			        $uploadMeta = arrayPath($_POST, 'metadata', []);

			        if (!empty($uploadMeta)) {
				        $thumbMeta = arrayPath($uploadMeta, 'thumbnail', null);
				        if (!empty($thumbMeta)) {
					        unset($uploadMeta['thumbnail']);

					        $mime = strtolower($thumbMeta['mimeType']);
					        $mimeParts = explode('/', $mime);

					        if (in_array($mimeParts[1], ['jpeg', 'jpg', 'png'])) {
					            $basename = basename($meta['file']);
					            $filename = pathinfo($basename, PATHINFO_FILENAME);
						        $ext = in_array($mimeParts[1], ['jpeg', 'jpg']) ? 'jpg' : 'png';
						        $subdir = str_replace($basename, '', $meta['file']);

						        $uploadDir = trailingslashit(WP_CONTENT_DIR).'uploads/'.trailingslashit($subdir);
						        @mkdir($uploadDir, 0777, true);

						        $filePath = $uploadDir.$filename.'.'.$ext;
						        file_put_contents($filePath, base64_decode($thumbMeta['data']));

						        $thumbId = wp_insert_attachment([
                                    'post_mime_type' => $thumbMeta['mimeType'],
                                    'post_title' => $filename. ' Thumb',
                                    'post_content' => '',
                                    'post_status' => 'inherit'
                                ]);

						        require_once( ABSPATH . 'wp-admin/includes/image.php' );
						        $thumbAttachmentMeta = wp_generate_attachment_metadata($thumbId, $filePath);
						        wp_update_attachment_metadata($thumbId, $thumbAttachmentMeta);

						        update_post_meta($result['id'], '_thumbnail_id', $thumbId);
					        }
				        }

				        $meta = array_merge($meta, $uploadMeta);
				        $doUpdateMeta = true;
                    }
                }

			    if (strpos($info->mimeType(), 'video') === 0) {
                    if ($this->useFFProbe && VideoProbe::instance()->enabled()) {
                        $probeResult = VideoProbe::instance()->probe($info->signedUrl());
                        if (!empty($result)) {
                            $meta = array_merge($meta, $probeResult);

                            $doUpdateMeta = true;
                        }
                    }
                } else if (!empty($faces)) {
					$meta['faces'] = $faces;

					$doUpdateMeta = true;
				}

			    if ($doUpdateMeta) {
				    add_filter('media-cloud/storage/ignore-metadata-update', [$this, 'ignoreMetadataUpdate'], 10, 2);
				    wp_update_attachment_metadata($result['id'], $meta);
				    remove_filter('media-cloud/storage/ignore-metadata-update', [$this, 'ignoreMetadataUpdate'], 10);
			    }

				json_response(['status' => 'success', 'data' => $result, 'attachment' => wp_prepare_attachment_for_js($result['id'])]);
			} else {
				json_response(['status' => 'error', 'message' => 'Error importing S3 file into WordPress.']);
			}
		} else {
			json_response(['status' => 'error', 'message' => 'Unknown type.', 'type' => $info->mimeType()]);
		}
	}

	public function ignoreMetadataUpdate($shouldIgnore, $id) {
		return true;
	}

	/**
	 * Called when ready to upload to the storage service
	 */
	private function prepareUpload() {
		if(!current_user_can('upload_files')) {
			json_response(["status" => "error", "message" => "Current user can't upload."]);
		}

		if (!$this->storageTool->client()->enabled()) {
			json_response(["status" => "error", "message" => "Storage settings are invalid."]);
		}

		$filename = $_POST['filename'];
		$filename = sanitize_file_name($filename);
		$type = $_POST['type'];
		$prefix = (!isset($_POST['directory'])) ? null : $_POST['directory'];

		if (empty($filename) || empty($type)) {
			json_response(["status" => "error", "message" => "Invalid file name or missing type."]);
		}

		if ($prefix === null) {
			$sitePrefix = '';

			if (is_multisite() && !is_main_site()) {
				$root = WP_CONTENT_DIR . DIRECTORY_SEPARATOR . 'uploads';
				$uploadDir = wp_get_upload_dir();
				$sitePrefix = ltrim(str_replace($root, '', $uploadDir['basedir']), DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR;
			}

			$prefix = $sitePrefix.StorageSettings::prefix(null);
		} else if (!empty($prefix)) {
			$prefix = trailingslashit($prefix);
		}

		$parts = explode('/', $filename);
		$bucketFilename = array_pop($parts);

		if ($this->storageTool->client()->exists($prefix.$bucketFilename)) {
		    $bucketFilename = gen_uuid(8).'-'.$bucketFilename;
        }

		$uploadInfo = $this->storageTool->client()->uploadUrl($prefix.$bucketFilename,StorageSettings::privacy(), $type,StorageSettings::cacheControl(), StorageSettings::expires());
		$res = $uploadInfo->toArray();
		$res['status'] = 'ready';
		json_response($res);
	}

	protected function allMimeTypes() {
		$altFormatsEnabled = apply_filters('media-cloud/imgix/alternative-formats/enabled', false);
		$mtypes = array_values(get_allowed_mime_types(get_current_user_id()));

		if ($altFormatsEnabled) {
			$mtypes = array_merge($mtypes, StorageSettings::alternativeFormatTypes());
		}

		return $mtypes;
    }

	protected function allowedMimeTypes() {
	    $additionalIgnored = [];
	    $dynamicEnabled = apply_filters('media-cloud/dynamic-images/enabled', false);

	    if (empty($dynamicEnabled) || !$this->uploadImages || !StorageSettings::uploadImages()) {
	        $additionalIgnored[] = "image/*";
        }

		if (!$this->uploadAudio || !StorageSettings::uploadAudio()) {
			$additionalIgnored[] = "audio/*";
		}

		if (!$this->uploadVideo || !StorageSettings::uploadVideo()) {
			$additionalIgnored[] = "video/*";
		}

		if (!$this->uploadDocuments || !StorageSettings::uploadDocuments()) {
			$additionalIgnored[] = "application/*";
			$additionalIgnored[] = "text/*";
		}


		$mtypes = $this->allMimeTypes();

		$allowed = [];
		foreach($mtypes as $mtype) {
		    if (StorageSettings::mimeTypeIsIgnored($mtype, $additionalIgnored)) {
		        continue;
            }

		    $allowed[] = $mtype;
        }

		return $allowed;
	}

	/**
	 * Render settings.
	 *
	 * @param bool $insertMode
	 */
	protected function doRenderSettings($insertMode) {
	    $mtypes = $this->allowedMimeTypes();

		$imgixEnabled = apply_filters('media-cloud/dynamic-images/enabled', false);

		$videoEnabled = apply_filters('media-cloud/transcoder/enabled', false);
		$altFormatsEnabled = apply_filters('media-cloud/imgix/alternative-formats/enabled', false);
		$docUploadsEnabled = StorageSettings::uploadDocuments();

		$maxUploads = apply_filters('media-cloud/direct-uploads/max-uploads', $this->maxUploads);

		$result = View::render_view('upload/ilab-media-upload.php', [
			'title' => $this->toolInfo['name'],
			'driver' => StorageManager::driver(),
			'maxUploads' => $maxUploads,
			'group' => $this->options_group,
			'page' => $this->options_page,
			'imgixEnabled' => $imgixEnabled,
			'videoEnabled' => $videoEnabled,
			'altFormats' => ($imgixEnabled && $altFormatsEnabled),
			'docUploads' => $docUploadsEnabled,
			'insertMode' => $insertMode,
			'allowedMimes' => $mtypes
		]);

		echo $result;
	}

	/**
	 * Render settings.
	 */
	protected function doRenderDirectUploadSettings() {
		$mtypes = $this->allowedMimeTypes();

		$imgixEnabled = apply_filters('media-cloud/dynamic-images/enabled', false);

		$videoEnabled = apply_filters('media-cloud/transcoder/enabled', false);
		$altFormatsEnabled = apply_filters('media-cloud/imgix/alternative-formats/enabled', false);
		$docUploadsEnabled = StorageSettings::uploadDocuments();

		$maxUploads = apply_filters('media-cloud/direct-uploads/max-uploads', $this->maxUploads);

		$result = View::render_view('upload/direct-upload-settings', [
			'driver' => StorageManager::driver(),
			'maxUploads' => ($maxUploads > 0) ? $maxUploads : 4,
			'imgixEnabled' => $imgixEnabled,
			'videoEnabled' => $videoEnabled,
			'altFormats' => ($imgixEnabled && $altFormatsEnabled),
			'docUploads' => $docUploadsEnabled,
			'allowedMimes' => $mtypes
		]);

		echo $result;
	}

	/**
	 * Render settings.
	 */
	public function renderSettings() {
		$this->doRenderSettings(false);
	}

	/**
	 * Render settings.
	 */
	public function renderInsertSettings() {
		$this->doRenderSettings(true);
	}
	//endregion
}