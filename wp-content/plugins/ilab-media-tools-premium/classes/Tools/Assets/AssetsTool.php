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

namespace ILAB\MediaCloud\Tools\Assets;

use ILAB\MediaCloud\Tools\Storage\StorageTool;
use ILAB\MediaCloud\Tools\Tool;
use ILAB\MediaCloud\Tools\ToolsManager;
use ILAB\MediaCloud\Utilities\Environment;
use ILAB\MediaCloud\Utilities\Logging\Logger;
use ILAB\MediaCloud\Utilities\NoticeManager;
use function ILAB\MediaCloud\Utilities\json_response;

if (!defined( 'ABSPATH')) { header( 'Location: /'); die; }

class AssetsTool extends Tool {
    protected $processLive;
    protected $logWarnings;
    protected $cacheControl = false;
    protected $expires = false;
    protected $cdnBase = false;
	protected $useVersion = false;
    protected $version = false;
    protected $gzip = false;


    public function __construct($toolName, $toolInfo, $toolManager) {
        parent::__construct($toolName, $toolInfo, $toolManager);

        $this->processLive = Environment::Option('mcloud-assets-process-live', null, true);
	    $this->logWarnings = Environment::Option('mcloud-assets-log-warnings', null, true);
	    $this->gzip = Environment::Option('mcloud-assets-gzip', null, false);

	    $this->cacheControl = Environment::Option('mcloud-assets-cache-control', null, false);
	    $this->cdnBase = trim(Environment::Option('mcloud-assets-cdn-base', null, false),'/');

	    $this->useVersion = !empty(Environment::Option('mcloud-assets-use-build-version', null, false));

	    $this->version = get_option('ilab_asset_build_version');

	    if ($this->useVersion && empty($this->version)) {
	    	$this->version = time();
	    	update_option('ilab_asset_build_version', $this->version);
	    }

        $expires = Environment::Option('mcloud-assets-expires', null, false);
        if(!empty($expires)) {
            $this->expires = gmdate('D, d M Y H:i:s \G\M\T', time() + ($expires * 60));
        }

        $this->testForBadPlugins();
        $this->testForUselessPlugins();
    }

    public function setup() {
        parent::setup();

        if (get_transient('ilab-assets-display-cache-cleared')) {
            NoticeManager::instance()->displayAdminNotice('info', "Assets cache has been cleared.");
            delete_transient('ilab-assets-display-cache-cleared');
        }

	    if (get_transient('ilab-assets-build-version-updated')) {
		    NoticeManager::instance()->displayAdminNotice('info', "Assets build version has been update.");
		    delete_transient('ilab-assets-build-version-updated');
	    }


	    if ($this->enabled()) {
		    if ($this->gzip && !extension_loaded('zlib')) {
			    NoticeManager::instance()->displayAdminNotice('warning', 'You have CSS/Javascript gzip compression enabled but the missing php zlib extension is not installed.  You will need to install this extension before using this feature.', true, 'mcloud-assets-zlib-missing', 1);
			    $this->gzip = false;
		    }

		    if (!is_admin()) {
			    add_action('wp_print_styles', function() {
				    if(Environment::Option('mcloud-assets-store-css', null, true)) {
					    $time = microtime(true);
					    $this->handleUploadedStyles();
					    $total = microtime(true) - $time;
					    Logger::info("Asset styles processed in $total seconds.");
				    } else if(!empty($this->cdnBase)) {
					    $this->handlePullStyles();
				    }
			    });

			    add_action('wp_print_scripts', function() {
				    if(Environment::Option('mcloud-assets-store-js', null, true)) {
					    $time = microtime(true);
					    $this->handleUploadedScripts();
					    $total = microtime(true) - $time;
					    Logger::info("Asset scripts processed in $total seconds.");
				    } else if(!empty($this->cdnBase)) {
					    $this->handlePullScripts();
				    }
			    });
		    }
        }
    }

    public function enabled() {
	    $enabled = parent::enabled();
	    if (!$enabled) {
	    	return false;
	    }

	    $storeCss = Environment::Option('mcloud-assets-store-css', null, true);
	    $storeJS = Environment::Option('mcloud-assets-store-js', null, true);

	    if (empty($storeCss) && empty($storeJS) && !empty($this->cdnBase)) {
	    	return true;
	    }

	    $enabled = (!empty($storeCss) || !empty($storeJS));
	    if (!$enabled) {
		    NoticeManager::instance()->displayAdminNotice('error', "You have assets enabled but have disabled uploading of CSS and Javascript.  If you want to serve assets using origin pull (the CDN will pull the assets from your server), please specify a CDN host in the settings.  Otherwise, turn on <strong>Store CSS Assets</strong> and/or <strong>Store Javascript Assets</strong>.");
	    }

	    return $enabled;
    }

	public function hasSettings() {
        return true;
    }

    //region Asset Pull Handling
	private function handlePullStyles() {
		/** @var \WP_Styles $wp_styles */
		$wp_styles = wp_styles();
		$this->handlePullDeps($wp_styles);
	}

	private function handlePullScripts() {
		/** @var \WP_Scripts $wp_scripts */
		$wp_scripts = wp_scripts();
		$this->handlePullDeps($wp_scripts);
	}

	private function handlePullDeps(\WP_Dependencies $deps) {
		$baseUrl = home_url();
		$wpVer = get_bloginfo('version');

		/** @var \_WP_Dependency $registered */
		foreach($deps->registered as $key => $registered) {
			if (($registered->src === true) || ($registered->src === false)) {
				continue;
			}

			$ver = $registered->ver;

			if (empty($ver)) {
				if (strpos($registered->src, '/wp-includes/') !== false) {
					$ver = $wpVer;
				} else {
					if ($this->useVersion && !empty($this->version)) {
						$ver = $this->version;
					} else {
						$this->logWarning("CSS/JS file '{$registered->src}' registered without version, skipping.");
						continue;
					}
				}
			}

			$isUrl = filter_var($registered->src, FILTER_VALIDATE_URL);
			if (empty($isUrl)) {
				$src = parse_url(site_url($registered->src), PHP_URL_PATH);
			} else {
				if (strpos($registered->src, $baseUrl) !== 0) {
					continue;
				}

				$src = parse_url($registered->src, PHP_URL_PATH);
			}

			$deps->registered[$key]->ver = $ver;
			$deps->registered[$key]->src = $this->cdnBase . '/' . ltrim($src, '/');
		}
	}

	//endregion

    //region Asset Upload Handling

    private function rootPath() {
        $home    = set_url_scheme( get_option( 'home' ), 'http' );
        $siteurl = set_url_scheme( get_option( 'siteurl' ), 'http' );
        if ( ! empty( $home ) && 0 !== strcasecmp( $home, $siteurl ) ) {
            $wp_path_rel_to_home = str_ireplace( $home, '', $siteurl );
            $pos = strpos(ABSPATH, $wp_path_rel_to_home);
            if ($pos > 0) {
                $home_path = substr(ABSPATH, 0, $pos);
            }
        } else {
            $home_path = ABSPATH;
        }

        return str_replace( '\\', '/', $home_path );
    }

    private function logWarning($message) {
        if (!$this->logWarnings) {
            return;
        }

        Logger::warning($message);
    }

    private function handleUploadedStyles() {
        $processed = get_option('ilab_processed_styles', []);

        /** @var \WP_Styles $wp_styles */
        $wp_styles = wp_styles();

        $baseUrl = home_url();
        $basePath = rtrim($this->rootPath(), '/');
        $wpVer = get_bloginfo('version');

        $fileList = [];

        /** @var \_WP_Dependency $registered */
        foreach($wp_styles->registered as $key => $registered) {
            if (($registered->src === true) || ($registered->src === false)) {
                continue;
            }

            $isWp = false;
            $ver = $registered->ver;

            if (empty($ver)) {
                if (strpos($registered->src, '/wp-includes/') !== false) {
                    $isWp = true;
                    $ver = $wpVer;
                } else {
	                if ($this->useVersion && !empty($this->version)) {
		                $ver = $this->version;
	                } else {
		                $this->logWarning("CSS file '{$registered->src}' registered without version, skipping.");
		                continue;
	                }
                }
            }

            $hash = sha1($registered->src.'?'.$ver);
            if (!empty($processed[$hash])) {
                $entry = $processed[$hash];
                $cssKey = $entry['cssKey'];

                if (!empty($this->cdnBase)) {
					$parsedUrl = parse_url($entry['url']);
	                $wp_styles->registered[$cssKey]->src = $this->cdnBase . ltrim($parsedUrl['path']);
                } else {
	                $wp_styles->registered[$cssKey]->src = $entry['url'];
                }

                continue;
            }

            if (!$this->processLive) {
                continue;
            }

            $scheme = parse_url($registered->src, PHP_URL_SCHEME);
            if (empty($scheme)) {
                if ($isWp) {
                    $srcPath = rtrim(ABSPATH, '/').$registered->src;
                } else {
                    $srcPath = $basePath . $registered->src;
                }
            } else {
                if (strpos($registered->src, $baseUrl) !== 0) {
                    continue;
                }

                $srcPath = $basePath.str_replace($baseUrl, '', $registered->src);
            }

            if (!file_exists($srcPath)) {
                continue;
            }

             $cssEntry = [
                 'cssKey' => $key,
                 'src' =>  $srcPath
            ];

            $imgFiles = [];
            $this->parseCSSUrls($srcPath, $isWp, $ver, $imgFiles);
            if (!empty($imgFiles)) {
                $cssEntry['img'] = $imgFiles;
            }

            $fileList[$hash] = $cssEntry;
        }

        $this->uploadCSSFiles($fileList);

        foreach($fileList as $hash => $entry) {
            $key = $entry['cssKey'];

	        if (!empty($this->cdnBase)) {
		        $parsedUrl = parse_url($entry['url']);
		        $wp_styles->registered[$key]->src = $this->cdnBase . ltrim($parsedUrl['path']);
	        } else {
		        $wp_styles->registered[$key]->src = $entry['url'];
	        }
        }

        $processed = array_merge($processed, $fileList);
        update_option('ilab_processed_styles', $processed);
    }

    private function parseCSSUrls($cssFile, $isWp, $version, &$fileList) {
        $re = '/[:,\s]\s*url\s*\(\s*(?:\'(\S*?)\'|"(\S*?)"|((?:\\\\\s|\\\\\)|\\\\\"|\\\\\'|\S)*?))\s*\)/m';

        $basePath = pathinfo($cssFile, PATHINFO_DIRNAME);
        $css = file_get_contents($cssFile);
        preg_match_all($re, $css, $matches, PREG_SET_ORDER, 0);

        if (!empty($matches)) {
            foreach($matches as $match) {
                $imgFile = array_pop($match);
                if (empty($imgFile)) {
                    continue;
                }

                $imgFile = realpath($basePath.DIRECTORY_SEPARATOR.$imgFile);
                if (file_exists($imgFile)) {
                    $hash = sha1($imgFile.'?'.$version);
                    $fileList[$hash] = $imgFile;
                } else {
                    $this->logWarning("CSS file '{$cssFile}' contains a missing image '{$imgFile}'");
                }
            }
        }
    }

    private function uploadCSSFiles(&$files) {
        /** @var StorageTool $storageTool */
        $storageTool = ToolsManager::instance()->tools['storage'];

        $basePath = rtrim($this->rootPath(), '/');


        foreach($files as $hash => $cssEntry) {
            $key = str_replace($basePath, '', $cssEntry['src']);
            $key = ltrim($key, '/');

	        $srcFile = $cssEntry['src'];

	        $contentType = null;
	        $contentLength = null;
	        $contentEncoding = null;
	        if ($this->gzip) {
		        if (!function_exists('wp_tempnam')) {
			        require_once(ABSPATH . 'wp-admin/includes/file.php');
		        }

		        $tmpFile = wp_tempnam();
		        $contents = file_get_contents($srcFile);
		        $compressedContents = gzcompress($contents, 6, ZLIB_ENCODING_GZIP);

		        if ($contents != $compressedContents) {
			        file_put_contents($tmpFile, $compressedContents);
			        $srcFile = $tmpFile;

			        $contentType = 'text/css';
			        $contentEncoding = 'gzip';
			        $contentLength = strlen($compressedContents);
		        }
	        }

	        $url = $storageTool->client()->upload($key, $srcFile, 'public-read', $this->cacheControl, $this->expires, $contentType, $contentEncoding, $contentLength);
            if (!empty($url)) {
                $cssEntry['url'] = $url;
            }

            if (!empty($cssEntry['img'])) {
                foreach($cssEntry['img'] as $imageHash => $image) {
                    $imgKey = str_replace($basePath, '', $image);
                    $imgKey = ltrim($imgKey, '/');

                    $storageTool->client()->upload($imgKey, $image, 'public-read', $this->cacheControl, $this->expires);
                }
            }

            $files[$hash] = $cssEntry;
        }
    }

    private function handleUploadedScripts() {
        $processed = get_option('ilab_processed_scripts', []);

        /** @var \WP_Scripts $wp_scripts */
        $wp_scripts = wp_scripts();

        $baseUrl = home_url();
        $basePath = rtrim($this->rootPath(), '/');
        $wpVer = get_bloginfo('version');

        $fileList = [];

        /** @var \_WP_Dependency $registered */
        foreach($wp_scripts->registered as $key => $registered) {
            if (($registered->src === true) || ($registered->src === false)) {
                continue;
            }

            $isWp = (strpos($registered->src, '/wp-includes/') !== false);
            $ver = $registered->ver;

            if (empty($ver)) {
                if ($isWp) {
                    $ver = $wpVer;
                } else {
	                if ($this->useVersion && !empty($this->version)) {
		                $ver = $this->version;
	                } else {
		                $this->logWarning("JS file '{$registered->src}' registered without version, skipping.");
		                continue;
	                }
                }
            }

            $hash = sha1($registered->src.'?'.$ver);
            if (!empty($processed[$hash])) {
                $entry = $processed[$hash];
                $jsKey = $entry['jsKey'];

	            if (!empty($this->cdnBase)) {
		            $parsedUrl = parse_url($entry['url']);
		            $wp_scripts->registered[$jsKey]->src = $this->cdnBase . ltrim($parsedUrl['path']);
	            } else {
		            $wp_scripts->registered[$jsKey]->src = $entry['url'];
	            }

                continue;
            }

            if (!$this->processLive) {
                continue;
            }

            $scheme = parse_url($registered->src, PHP_URL_SCHEME);
            if (empty($scheme)) {
                if ($isWp) {
                    $srcPath = $basePath . $registered->src;
                    if (!file_exists($srcPath)) {
                        $srcPath = rtrim(ABSPATH, '/').$registered->src;
                    }
                } else {
                    $srcPath = $basePath . $registered->src;
                }
            } else {
                if (strpos($registered->src, $baseUrl) !== 0) {
                    continue;
                }

                $srcPath = $basePath.str_replace($baseUrl, '', $registered->src);
            }

            if (!file_exists($srcPath)) {
                continue;
            }

            $jsEntry = [
                'jsKey' => $key,
                'src' =>  $srcPath
            ];

            $fileList[$hash] = $jsEntry;
        }

        $this->uploadJSFiles($fileList);

        foreach($fileList as $hash => $entry) {
            $key = $entry['jsKey'];

	        if (!empty($this->cdnBase)) {
		        $parsedUrl = parse_url($entry['url']);
		        $wp_scripts->registered[$key]->src = $this->cdnBase . ltrim($parsedUrl['path']);
	        } else {
		        $wp_scripts->registered[$key]->src = $entry['url'];
	        }
        }

        $processed = array_merge($processed, $fileList);
        update_option('ilab_processed_scripts', $processed);
    }

    private function uploadJSFiles(&$files) {
        /** @var StorageTool $storageTool */
        $storageTool = ToolsManager::instance()->tools['storage'];

        $basePath = rtrim($this->rootPath(), '/');

        foreach($files as $hash => $jsEntry) {
            $key = str_replace($basePath, '', $jsEntry['src']);
            $key = ltrim($key, '/');

	        $srcFile = $jsEntry['src'];

	        $contentType = null;
	        $contentLength = null;
	        $contentEncoding = null;
	        if ($this->gzip) {
		        if (!function_exists('wp_tempnam')) {
			        require_once(ABSPATH . 'wp-admin/includes/file.php');
		        }

		        $tmpFile = wp_tempnam();
		        $contents = file_get_contents($srcFile);
		        $compressedContents = gzcompress($contents, 6, ZLIB_ENCODING_GZIP);

		        if ($contents != $compressedContents) {
			        file_put_contents($tmpFile, $compressedContents);
			        $srcFile = $tmpFile;

			        $contentType = 'application/javascript';
			        $contentEncoding = 'gzip';
			        $contentLength = strlen($compressedContents);
		        }
	        }

            $url = $storageTool->client()->upload($key, $srcFile, 'public-read', $this->cacheControl, $this->expires, $contentType, $contentEncoding, $contentLength);
            if (!empty($url)) {
                $jsEntry['url'] = $url;
            }

            $files[$hash] = $jsEntry;
        }
    }

    //endregion

    //region Actions

    public function resetCache() {
        delete_option('ilab_processed_scripts');
        delete_option('ilab_processed_styles');
        set_transient('ilab-assets-display-cache-cleared', true);

        json_response(['status' => 'ok']);
    }

    public function updateBuildVersion() {
	    delete_option('ilab_processed_scripts');
	    delete_option('ilab_processed_styles');
	    set_transient('ilab-assets-display-cache-cleared', true);
	    
    	$this->version = time();
	    update_option('ilab_asset_build_version', $this->version);
	    set_transient('ilab-assets-build-version-updated', true);

	    json_response(['status' => 'ok']);
    }

    //endregion
}