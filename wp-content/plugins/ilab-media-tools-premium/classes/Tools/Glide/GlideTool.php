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

namespace ILAB\MediaCloud\Tools\Glide;

use ILAB\MediaCloud\Tools\DynamicImages\DynamicImagesTool;
use ILAB\MediaCloud\Tools\DynamicImages\WordPressUploadsAdapter;
use ILAB\MediaCloud\Tools\Glide\Batch\ClearCacheBatchProcess;
use ILAB\MediaCloud\Tools\Glide\Server\GlideServerFactory;
use ILAB\MediaCloud\Tools\Glide\Server\WordpressResponseFactory;
use ILAB\MediaCloud\Utilities\Environment;
use ILAB\MediaCloud\Utilities\Logging\Logger;
use ILAB\MediaCloud\Utilities\NoticeManager;
use League\Flysystem\Adapter\Local;
use League\Flysystem\Filesystem;
use League\Glide\Signatures\SignatureException;
use League\Glide\Signatures\SignatureFactory;
use League\Glide\Urls\UrlBuilder;
use function ILAB\MediaCloud\Utilities\arrayPath;

if(!defined('ABSPATH')) {
    header('Location: /');
    die;
}


/**
 * Class GlideTool
 *
 * Glide tool.
 */
class GlideTool extends DynamicImagesTool {
    protected $basePath = null;
    protected $cdnHost = null;
    protected $convertPNG = false;
    protected $usePJPEG = true;
    protected $cacheMasterImages = true;
    protected $maxWidth = 4000;

    public function __construct($toolName, $toolInfo, $toolManager) {
        parent::__construct($toolName, $toolInfo, $toolManager);

        new ClearCacheBatchProcess();


	    add_filter('media-cloud/glide/enabled', function($enabled){
		    return $this->enabled();
	    });

        $this->signingKey = Environment::Option('mcloud-glide-signing-key', null, false);
        if (empty($this->signingKey)) {
            $this->signingKey = bin2hex(random_bytes(32));
            Environment::UpdateOption('mcloud-glide-signing-key', $this->signingKey);
        }

        $this->basePath = Environment::Option('mcloud-glide-image-path', null, false);
        if (empty($this->basePath) || empty(ltrim($this->basePath,'/'))) {
            $this->basePath = '/__images/';
            Environment::UpdateOption('mcloud-glide-image-path', $this->basePath);
        }
        $this->basePath = '/'.trim($this->basePath,'/').'/';

        $this->imageQuality = Environment::Option('mcloud-glide-default-quality');
        $this->keepThumbnails = Environment::Option('mcloud-glide-generate-thumbnails', null, true);
        $this->cdnHost = Environment::Option('mcloud-glide-cdn', null, null);
        $this->convertPNG = Environment::Option('mcloud-glide-convert-png', null, false);
        $this->usePJPEG = Environment::Option('mcloud-glide-progressive-jpeg', null, true);
        $this->maxWidth = Environment::Option('mcloud-glide-max-size', null, 4000);
        $this->cacheMasterImages = Environment::Option('mcloud-glide-cache-remote', null, true);

        add_filter('do_parse_request', function($do, \WP $wp) {
        	$file = null;
            if (strpos($_SERVER['REQUEST_URI'], $this->basePath) === 0) {
                $file = str_replace($this->basePath, '', parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
                $file = str_replace('%20', ' ', $file);
            } else if (strpos($_SERVER['REQUEST_URI'], '/wp'.$this->basePath) === 0) {
            	// fix for multisite in bedrock
	            $file = str_replace('/wp'.$this->basePath, '', parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
	            $file = str_replace('%20', ' ', $file);
            }

            if (!empty($file)) {
	            $this->renderImage($file);
	            die;
            }

            return $do;
        }, 100, 2);

        add_filter('media-cloud/glide/clean-path', function($path) {
	        if (strpos($path, $this->basePath) === 0) {
		        $path = ltrim(str_replace($this->basePath, '', $path), '/');
	        } else {
	        	if (empty(ltrim($this->basePath,'/'))) {
	        		return $path;
		        }

	        	if (strpos($path, ltrim($this->basePath,'/')) === 0) {
			        $path = ltrim(str_replace(ltrim($this->basePath,'/'), '', $path), '/');
		        }
	        }

        	return $path;
        });
    }

    public function setup() {
        parent::setup();

        if ($this->enabled()) {
            if (!class_exists("Imagick")) {
                NoticeManager::instance()->displayAdminNotice('warning', "Installing the <a target='_blank' href='http://php.net/manual/en/book.imagick.php'>ImageMagick PHP extension</a> will improve the quality and performance of the Glide server, as well as enable features that are otherwise not available.", true, 'ilab-glide-imagick-forever');
            }
        }
    }

    //region Render Image

    /**
     * Creates the Glide Server instance
     * @return \League\Glide\Server
     */
    protected function server() {
        $imagickInstalled = class_exists("Imagick");

        if ($this->toolManager->toolEnabled('storage')) {
            if (!$this->cacheMasterImages) {
                $source = new Filesystem($this->toolManager->tools['storage']->client()->adapter());
            } else {
                $source = new Filesystem(new WordPressUploadsAdapter($this->toolManager->tools['storage']->client()->adapter()));
            }
        } else {
            $source = new Filesystem(new Local(WP_CONTENT_DIR.DIRECTORY_SEPARATOR.'uploads'));
        }

        $server = GlideServerFactory::create([
            'source' => $source,
            'cache' => new Filesystem(new Local(WP_CONTENT_DIR.DIRECTORY_SEPARATOR.'uploads/glide-cache/')),
            'watermarks' => $source,
            'driver' => ($imagickInstalled) ? 'imagick' : 'gd',
            'max_image_size' => $this->maxWidth * $this->maxWidth,
            'base_url' => $this->basePath,
            'response' => new WordpressResponseFactory()
        ]);

        return $server;
    }

    protected function renderImage($file) {
        try {
            SignatureFactory::create($this->signingKey)->validateRequest(trim($this->basePath.$file,'/'), $_GET);
        } catch (SignatureException $e) {
            return false;
        }

        $this->server()->getImageResponse($file, $_GET);
    }

    //endregion

    //region Tool Override
    public function hasSettings() {
        return true;
    }

    public function enabled() {
        $enabled = parent::enabled();

        if ($enabled) {
            return !empty($this->signingKey);
        }

        return false;
    }
    //endregion

    //region URL Generation
    private function createAbsoluteURL($relativeURL) {
        if (!empty($this->cdnHost)) {
            return trim($this->cdnHost, '/').$relativeURL;
        }

        $url = home_url($relativeURL);
	    $url = preg_replace('/&lang=[aA-zZ0-9]+/m', '', $url);

        return $url;
    }

    /**
     * Builds the parameters for generating Imgix URLs
     *
     * @param array $params
     * @param string $mimetype
     *
     * @return array
     */
    private function buildGlideParams($params, $mimetype = '', $width = 0, $height = 0) {
        $format = null;
        if(empty($params['fm'])) {
            if ($mimetype == 'image/gif') {
                $format = 'gif';
            } else if ($mimetype == 'image/png') {
                if ($this->convertPNG) {
                    $format = ($this->usePJPEG) ? 'pjpg' : 'jpg';
                } else {
                    $format = 'png';
                }
            } else {
                $format = ($this->usePJPEG) ? 'pjpg' : 'jpg';
            }
        } else {
            $format = $params['fm'];
        }
        unset($params['fm']);

        if (!empty($format)) {
            $params['fm'] = $format;
        }


        if (isset($params['flip']) && (strpos($params['flip'], ',') > 0)) {
            $params['flip'] = 'both';
        }


        if($this->imageQuality) {
            $params['q'] = $this->imageQuality;
        }

        $markMeta = [];
        foreach($this->paramPropsByType['media-chooser'] as $key => $info) {
            if(isset($params[$key]) && !empty($params[$key])) {
                $media_id = $params[$key];
                unset($params[$key]);
                $markMeta = wp_get_attachment_metadata($media_id);
                if (isset($markMeta['s3'])) {
                    $params[$info['imgix-param']] = '/'.$markMeta['s3']['key'];
                } else {
                    $params[$info['imgix-param']] = '/'.$markMeta['file'];
                }
            } else {
                unset($params[$key]);
                if(isset($info['dependents'])) {
                    foreach($info['dependents'] as $depKey) {
                        unset($params[$depKey]);
                    }
                }
            }
        }

        if (!empty($params['mark'])) {
            if (empty($params['markalign'])) {
                $params['markpos'] = 'bottom-right';
            } else {
                $params['markpos'] = str_replace(',','-', $params['markalign']);
            }
            unset($params['markalign']);

            $posMap = [
                'top-center' => 'top',
                'middle-right' => 'right',
                'bottom-center' => 'bottom',
                'middle-left' => 'left',
                'middle-center' => 'center'
            ];

            if (isset($posMap[$params['markpos']])) {
                $params['markpos'] = $posMap[$params['markpos']];
            }

            if ($params['markpos'] == 'bottom-center') {
                $params['markpos'] = 'bottom';
            }

            if (!empty($params['markpad'])) {
                if ($width > $height) {
                    $params['markpad'] = $params['markpad'].'h';
                } else {
                    $params['markpad'] = $params['markpad'].'w';
                }
            }

            if (!empty($params['markscale'])) {
                if (!empty($markMeta['width']) && !empty($markMeta['height'])) {
                    $mw = floor($markMeta['width'] * ($params['markscale'] / 100.0));
                    $mh = floor($markMeta['height'] * ($params['markscale'] / 100.0));

                    $params['markw'] = $mw;
                    $params['markh'] = $mh;
                }

                unset($params['markscale']);
            }

        }

        if(isset($params['border-width']) && isset($params['border-color']) && ($params['border-width'] > 0)) {
            $color = $params['border-color'];
            if (strpos($color, '#') === 0) {
                $color = substr($color, 1);
            }

            if (strlen($color) == 8) {
                $color = substr($color, 2);
            }

            $params['border'] = $params['border-width'].','.$color.',overlay';
        }

        if(isset($params['padding-width']) && isset($params['padding-color']) && ($params['padding-width'] > 0)) {
            $color = $params['padding-color'];
            if (strpos($color, '#') === 0) {
                $color = substr($color, 1);
            }

            if (strlen($color) == 8) {
                $color = substr($color, 2);
            }

            $params['padding'] = $params['padding-width'].','.$color.',shrink';
        }

        if (!empty($params['px'])) {
            $params['pixel'] = $params['px'] * 5;
        }

        unset($params['px']);

        unset($params['border-width']);
        unset($params['border-color']);

        unset($params['padding-width']);
        unset($params['padding-color']);

        if (!empty($params['auto']) && ($params['auto'] != 'enhance')) {
            unset($params['auto']);
        }

        unset($params['enhance']);
        unset($params['redeye']);

        return $params;
    }

	public function buildSizedImage($id, $size) {
        $meta = wp_get_attachment_metadata($id);
        if(!$meta || empty($meta)) {
            return false;
        }

        if (empty($meta['s3']['key']) && empty($meta['file'])) {
        	return false;
        }

        $imageFile = (!empty($meta['s3']['key'])) ? $meta['s3']['key'] : $meta['file'];
        if (empty($imageFile)) {
            return false;
        }

        $builder = new UrlBuilder($this->basePath, SignatureFactory::create($this->signingKey));

        $is_crop = ((count($size) >= 3) && ($size[2] == 'crop'));
        if (!$is_crop && $this->shouldCrop) {
            $this->shouldCrop = false;
            $is_crop = true;
        }

        if ($is_crop && (($size[0] === 0) || ($size[1] === 0))) {
            if ($size[0] === 0) {
                $size[0] = 10000;
            } else {
                $size[1] = 10000;
            }

            $is_crop = false;
        }

        if(isset($size['width'])) {
            $size = [
                $size['width'],
                $size['height']
            ];
        }

        $params = [
            'fit' => ($is_crop) ? 'crop' : 'fit',
            'w' => $size[0],
            'h' => $size[1],
            'fm' => 'jpg'
        ];

		$params = apply_filters_deprecated('ilab-imgix-filter-parameters', [$params, $size, $id, $meta], '3.0.0', 'media-cloud/dynamic-images/filter-parameters');
		$params = apply_filters('media-cloud/dynamic-images/filter-parameters', $params, $size, $id, $meta);

        $result = [
            $this->createAbsoluteURL($builder->getUrl($imageFile, $params)),
            $size[0],
            $size[1]
        ];

        return $result;
    }

	public function buildImage($id, $size, $params = null, $skipParams = false, $mergeParams = null, $newSize = null, $newMeta=null) {
        if (!wp_attachment_is_image($id)) {
        	return false;
        }

    	if(is_array($size)) {
            return $this->buildSizedImage($id, $size);
        }

        $meta = wp_get_attachment_metadata($id);
        if(!$meta || empty($meta)) {
            if(!$meta || empty($meta)) {
                if (!empty($newMeta)) {
                    $meta = $newMeta;
                } else {
                    return false;
                }
            }
        }

        if (empty($meta['width']) && empty($meta['height'])) {
            return false;
        }

        $imageFile = (!empty($meta['s3']['key'])) ? $meta['s3']['key'] : arrayPath($meta, 'file', null);
        if (empty($imageFile)) {
            return false;
        }

        $mimetype = get_post_mime_type($id);

        $builder = new UrlBuilder($this->basePath, SignatureFactory::create($this->signingKey));

        if($size == 'full' && !$newSize) {
            if(!isset($meta['width']) || !isset($meta['height'])) {
                return false;
            }

            if(!$params) {
                if(isset($meta['imgix-params']) && !$this->skipSizeParams) {
                    $params = $meta['imgix-params'];
                } else {
                    $params = [];
                }
            }

            $params = $this->buildGlideParams($params, $mimetype, $meta['width'], $meta['height']);
	        $params = apply_filters_deprecated('ilab-imgix-filter-parameters', [$params, $size, $id, $meta], '3.0.0', 'media-cloud/dynamic-images/filter-parameters');
	        $params = apply_filters('media-cloud/dynamic-images/filter-parameters', $params, $size, $id, $meta);

            $result = [
                $this->createAbsoluteURL($builder->getUrl($imageFile, ($skipParams) ? [] : $params)),
                $meta['width'],
                $meta['height'],
                false
            ];

            return $result;
        }

        if($newSize) {
            $sizeInfo = $newSize;
        } else {
            $sizeInfo = ilab_get_image_sizes($size);
        }

        if(!$sizeInfo) {
            return false;
        }

        $metaSize = null;
        if(isset($meta['sizes'][$size])) {
            $metaSize = $meta['sizes'][$size];
        }

	    $doCrop = !empty($sizeInfo['crop']) || !empty($metaSize['crop']);

        if(empty($params)) {
            $sizeParams = (!empty($sizeInfo['imgix']) && is_array($sizeInfo['imgix'])) ? $sizeInfo['imgix'] : [];

            // get the settings for this image at this size
            if(isset($meta['imgix-size-params'][$size])) {
                $params = array_merge($sizeParams, $meta['imgix-size-params'][$size]);
            }


            if(empty($params)) // see if a preset has been globally assigned to a size and use that
            {
                $presets = get_option('ilab-imgix-presets');
                $sizePresets = get_option('ilab-imgix-size-presets');

                if($presets && $sizePresets && isset($sizePresets[$size]) && isset($presets[$sizePresets[$size]])) {
                    $params = array_merge($sizeParams, $presets[$sizePresets[$size]]['settings']);
                }
            }

            // still no parameters?  use any that may have been assigned to the full size image
            if(empty($params) && (isset($meta['imgix-params']))) {
                $params = array_merge($sizeParams, $meta['imgix-params']);
            } else if(empty($params)) // too bad so sad
            {
                $params = $sizeParams;
            }
        }

        if ($doCrop) {
	        if (empty($sizeInfo['crop']) && !empty($metaSize['crop'])) {
		        $sz = sizeToFitSize($metaSize['crop']['w'],$metaSize['crop']['h'], $sizeInfo['width'] ?: $sizeInfo['height'], $sizeInfo['height'] ?: $sizeInfo['width']);
		        $params['w'] = $sz[0];
		        $params['h'] = $sz[1];
	        } else {
		        $params['w'] = $sizeInfo['width'] ?: $sizeInfo['height'];
		        $params['h'] = $sizeInfo['height'] ?: $sizeInfo['width'];
	        }

	        $params['fit'] = 'crop';

            if($metaSize) {
                $metaSize = $meta['sizes'][$size];
                if(isset($metaSize['crop'])) {
                    $metaSize['crop']['x'] = round($metaSize['crop']['x']);
                    $metaSize['crop']['y'] = round($metaSize['crop']['y']);
                    $metaSize['crop']['w'] = round($metaSize['crop']['w']);
                    $metaSize['crop']['h'] = round($metaSize['crop']['h']);
                    $params['crop'] = "{$metaSize['crop']['w']},{$metaSize['crop']['h']},{$metaSize['crop']['x']},{$metaSize['crop']['y']}";
                }
            }

            if (isset($params['focalpoint'])) {
                unset($params['crop']);
                unset($params['fit']);

	            list($cropW, $cropH) = sizeToFitSize($sizeInfo['width'], $sizeInfo['height'], $meta['width'], $meta['height']);

                if (($params['focalpoint'] == 'usefaces') && isset($meta['faces']) && (count($meta['faces']) > 0)) {
	                $faceindex = arrayPath($params,'faceindex', 0);

	                $cx = 0;
	                $cy = 0;

	                if ((count($meta['faces'])>1) && ($faceindex == 0)) {
		                $left = 900000;
		                $top = 900000;
		                $right = 0;
		                $bottom = 0;

		                foreach($meta['faces'] as $face) {
			                $bb = $face['BoundingBox'];
			                $left = min($left, $bb['Left']);
			                $top = min($top, $bb['Top']);
			                $right = max($right, $bb['Left'] + $bb['Width']);
			                $bottom = max($bottom, $bb['Top'] + $bb['Height']);
		                }

		                $cx = ($left + (($right - $left) / 2.0)) * (float)$meta['width'];
		                $cy = ($top + (($bottom - $top) / 2.0)) * (float)$meta['height'];
	                } else {
		                $faceindex = min(1,count($meta['faces'])+1);
		                $bb = $meta['faces'][$faceindex - 1]['BoundingBox'];

		                $cx = ($bb['Left'] + ($bb['Width'] / 2.0)) * (float)$meta['width'];
		                $cy = ($bb['Top'] + ($bb['Height'] / 2.0)) * (float)$meta['height'];
	                }

	                $cz = arrayPath($params, 'fp-z', 1);
	                $cropW *= (10.0 - ($cz - 1)) / 10.0;
	                $cropH *= (10.0 - ($cz - 1)) / 10.0;

	                $cropW = floor($cropW);
	                $cropH = floor($cropH);

	                $cropX = min($meta['width'] - $cropW, max(0, floor($cx - ($cropW / 2.0))));
	                $cropY = min($meta['height'] - $cropH, max(0, floor($cy - ($cropH / 2.0))));

	                $params['crop'] = "$cropW,$cropH,$cropX,$cropY";
                } else {
	                $fpx = arrayPath($params, 'fp-x', 0.5);
	                $fpy = arrayPath($params, 'fp-y', 0.5);

	                $cropX = floor(min($meta['width'] - $cropW, max(0, floor(((float)$fpx * (float)$meta['width'])) - ($cropW / 2.0))));
	                $cropY = floor(min($meta['height'] - $cropH, max(0, floor(((float)$fpy * (float)$meta['height'])) - ($cropH / 2.0))));

	                $params['crop'] = "$cropW,$cropH,$cropX,$cropY";
                }
            } else {
                if (!empty($sizeInfo['crop']) && is_array($sizeInfo['crop'])) {
                    list($cropX, $cropY) = $sizeInfo['crop'];
                    if (!empty($cropX) && !empty($cropY)) {
                        unset($params['crop']);
                        $params['fit'] = "crop-{$cropX}-{$cropY}";
                    }
                }
            }
        } else {
            $mw = !empty($meta['width']) ? $meta['width'] : 10000;
            $mh = !empty($meta['height']) ? $meta['height'] : 10000;

            $w = !empty($sizeInfo['width']) ? $sizeInfo['width'] : 10000;
            $h = !empty($sizeInfo['height']) ? $sizeInfo['height'] : 10000;

            $newSize = sizeToFitSize($mw, $mh, $w, $h);
            $params['w'] = $newSize[0];
            $params['h'] = $newSize[1];
            $params['fit'] = 'contain';
        }

        unset($params['fp-x']);
        unset($params['fp-y']);
        unset($params['fp-z']);

        unset($params['focalpoint']);
        unset($params['faceindex']);

        if($mergeParams && is_array($mergeParams)) {
            $params = array_merge($params, $mergeParams);
        }

        if($size && !is_array($size)) {
            $params['wpsize'] = $size;
        }

        $params = $this->buildGlideParams($params, $mimetype, $meta['width'], $meta['height']);
		$params = apply_filters_deprecated('ilab-imgix-filter-parameters', [$params, $size, $id, $meta], '3.0.0', 'media-cloud/dynamic-images/filter-parameters');
		$params = apply_filters('media-cloud/dynamic-images/filter-parameters', $params, $size, $id, $meta);

        $imageFile = (isset($meta['s3'])) ? $meta['s3']['key'] : $meta['file'];

        $result = [
            $this->createAbsoluteURL($builder->getUrl($imageFile, $params)),
            $params['w'],
            $params['h'],
            true
        ];

        return $result;
    }

	public function urlForStorageMedia($key, $params = []) {
		$builder = new UrlBuilder($this->basePath, SignatureFactory::create($this->signingKey));
		return $this->createAbsoluteURL($builder->getUrl($key, $params));
	}

	public function fixCleanedUrls($good_protocol_url, $original_url, $context) {
    	if (empty($this->cdnHost)) {
    		return $good_protocol_url;
	    }

    	if (strpos($good_protocol_url, $this->cdnHost) !== false) {
    		return $original_url;
	    }

		return $good_protocol_url;
	}
    //endregion

    //region Cache
    public function clearCache($filename) {
        Logger::info("Deleting cache $filename");
        $this->server()->deleteCache($filename);
    }
    //endregion
}