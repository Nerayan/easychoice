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
    "id" => "glide",
    "name" => "Dynamic Images",
    "description" => "Serves on-demand dynamic images, similar to Imgix, but happening locally on your WordPress server.  Works with any cloud storage provider, or even without one.",
    "class" => "ILAB\\MediaCloud\\Tools\\Glide\\GlideTool",
    "env" => "ILAB_MEDIA_GLIDE_ENABLED",
    "dependencies" => [
        "crop",
        "!imgix"
    ],
    "related" => ["media-upload", "crop"],
    "helpers" => [
        "ilab-imgix-helpers.php"
    ],
	"imageOptimizers" => include 'image-optimizers.config.php',
    "incompatiblePlugins" => [
	    "Smush" => [
		    "plugin" => "wp-smushit/wp-smush.php",
		    "description" => "The free version of this plugin does not optimize the main image, only thumbnails.  When the Imgix tool is enabled, thumbnails are not generated - therefore this plugin isn't any use.  The Pro (paid) version of this plugin DOES optimize the main image though."
	    ],
    ],
    "badPlugins" => [
        "BuddyPress" => [
            "plugin" => "buddypress/bp-loader.php",
            "description" => "Uploading profile or cover images results in broken images."
        ],
	    "EDD Free Downloads" => [
		    "plugin" => "edd-free-downloads/edd-free-downloads.php",
		    "description" => "EDD Free Downloads does not work with Dynamic Image URLs.  Dynamic Images work fine with Easy Digital Downloads, just not this particular extension."
	    ],
    ],
	"batchTools" => [
		"\\ILAB\\MediaCloud\\Tools\\Glide\\Batch\\ClearCacheBatchTool"
	],
	"CLI" => [
		"\\ILAB\\MediaCloud\\Tools\\Glide\\CLI\\GlideCommands"
	],
    "settings" => [
        "options-page" => "media-tools-glide",
        "options-group" => "ilab-media-glide",
        "groups" => [
            "ilab-media-glide-general-settings" => [
                "title" => "General Settings",
                "options" => [
                    "mcloud-glide-image-path" => [
                        "title" => "Image Path",
                        "description" => "The base path for the generated image URLs.  This cannot be blank or empty.  This path is prepended to the URL for the generated images and when the URL is requested, the plugin will intercept them, render the dynamic image (if needed), or pull it from the cache (if previously rendered) and send the image to the requester.",
                        "type" => "text-field",
                        "default" => "/__images/"
                    ],
                    "mcloud-glide-signing-key" => [
                        "title" => "Signing Key",
                        "description" => "The signing key used to create secure URLs.  This is generated for you, though you can override the auto-generated one here.",
                        "type" => "text-field"
                    ],

                ]
            ],
            "ilab-media-glide-performance-settings" => [
                "title" => "Performance Settings",
                "options" => [
                    "mcloud-glide-cache-remote" => [
                        "title" => "Cache Master Images",
                        "description" => "This option will cache any master images that are fetched from remote storage (S3, Google Cloud Storage, etc) locally.  If this option is turned off, everytime you request a dynamic size for an image, that image will be pulled from storage.  It is very much recommended that you keep this option <strong>turned on</strong>.",
                        "type" => "checkbox",
                        "default" => "true"
                    ],
                    "mcloud-glide-cdn" => [
                        "title" => "CDN",
                        "description" => "The base path for the generated image URLs.  This cannot be blank or empty.  This path is prepended to the URL for the generated images and when the URL is requested, the plugin will intercept them, render the dynamic image (if needed) and send the image to the requester.",
                        "type" => "text-field",
                        "default" => "/__images/"
                    ],
                    "mcloud-glide-cache-ttl" => [
                        "title" => "Cache TTL",
                        "description" => "The number of minutes to cache the rendered image in the user's browser.  Use <code>0</code> to disable client side caching.  Default is <code>525600</code> minutes (1 year).",
                        "type" => "number",
                        "default" => 525600,
                        "max" => 525600,
                        "min" => 0
                    ],
                ]
            ],
            "ilab-media-glide-image-settings" => [
                "title" => "Image Settings",
                "options" => [
                    "mcloud-glide-default-quality" => [
                        "title" => "Lossy Image Quality",
                        "type" => "number",
                        "max" => 100,
                        "min" => 1,
                        "default" => 85
                    ],
                    "mcloud-glide-max-size" => [
                        "title" => "Max. Image Width/Height",
                        "description" => "The maximum image width or height for a generated image.",
                        "type" => "number",
                        "max" => 10000,
                        "min" => 1,
                        "default" => 4000
                    ],
                    "mcloud-glide-convert-png" => [
                        "title" => "Convert PNG to JPEG",
                        "description" => "Selecting this will convert all uploaded PNG files to JPEG files when rendering.",
                        "type" => "checkbox",
                        "default" => false
                    ],
                    "mcloud-glide-progressive-jpeg" => [
                        "title" => "Use Progressive JPEG",
                        "description" => "When rendering an image and the output is JPEG, turning this on will generate a progressive JPEG file.",
                        "type" => "checkbox",
                        "default" => true
                    ],
                    "mcloud-glide-generate-thumbnails" => [
                        "title" => "Keep WordPress Thumbnails",
                        "description" => "Because Glide can dynamically create new sizes for existing images, having WordPress create thumbnails is potentially pointless, a probable waste of space and definitely slows down uploads.  However, if you plan to stop using Glide, having those thumbnails on S3 or locally will save you having to regenerate thumbnails later.  <strong>IMPORTANT:</strong> Thumbnails will not be generated when you perform a direct upload because those uploads are sent directly to S3 without going through your WordPress server.",
                        "type" => "checkbox",
                        "default" => true
                    ]
                ]
            ],
	        "ilab-media-glide-gutenberg-settings" => [
		        "title" => "Gutenberg Integration",
		        "description" => "Controls integration of Dynamic Images with Gutenberg",
		        "options" => [
			        "mcloud-imgix-disable-srcset" => [
				        "title" => "Disable srcset on image tags",
				        "description" => "Gutenberg's image block has a lot of issues and problems.  For example, it omits the width and height attributes which is a really bad practice.  And it's also because of this that it's impossible to calculate a srcset that is realistic when using Imgix.  Until they fix this, we recommend disabling srcset on image tags - <strong>but only if you use Gutenberg</strong>.  If you are not using Gutenberg, carry on with your bad self!",
				        "type" => "checkbox",
				        "default" => false
			        ]
		        ]
	        ],
        ],
        "params" => [
            "adjust" => [
                "Orientation" => [
                    "or" => [
                        "type" => "pillbox",
                        "radio" => true,
                        "no-icon" => true,
                        "options" => [
                            "90" => [
                                "title" => "90°",
                                "default" => 0
                            ],
                            "180" => [
                                "title" => "180°",
                                "default" => 0
                            ],
                            "270" => [
                                "title" => "270°",
                                "default" => 0
                            ],
                        ],
                        "selected" => function($settings, $currentValue, $selectedOutput, $unselectedOutput){
                            if (isset($settings['or']) && ($settings['or'] == $currentValue)) {
                                return $selectedOutput;
                            }

                            return $unselectedOutput;
                        }
                    ]
                ],
                "Flip" => [
                    "flip" => [
                        "type" => "pillbox",
                        "options" => [
                            "h" => [
                                "title" => "Horizontal",
                                "default" => 0
                            ],
                            "v" => [
                                "title" => "Vertical",
                                "default" => 0
                            ]
                        ],
                        "selected" => function($settings, $currentValue, $selectedOutput, $unselectedOutput){
                            if (isset($settings['flip'])) {
                                $parts=explode(',',$settings['flip']);
                                foreach($parts as $part) {
                                    if ($part==$currentValue) {
                                        return $selectedOutput;
                                    }
                                }
                            }

                            return $unselectedOutput;
                        }
                    ]
                ],
                "Transform" => [
                    "rot" => [
                        "title" => "Rotation",
                        "type" => "slider",
                        "min" => -359,
                        "max" => 359,
                        "default" => 0
                    ]
                ],
                "Enhance" => [
                    "auto" => [
                        "type" => "pillbox",
                        "options" => [
                            "enhance" => [
                                "title" => "Auto Enhance",
                                "default" => 0
                            ]
                        ],
                        "hidden" => (!class_exists("Imagick")),
                        "selected" => function($settings, $currentValue, $selectedOutput, $unselectedOutput){
                            if (isset($settings['auto'])) {
                                $parts=explode(',',$settings['auto']);
                                foreach($parts as $part) {
                                    if ($part==$currentValue) {
                                        return $selectedOutput;
                                    }
                                }
                            }

                            return $unselectedOutput;
                        }
                    ]
                ],
                "Luminosity Controls" => [
                    "bri" => [
                        "title" => "Brightness",
                        "type" => "slider",
                        "min" => -100,
                        "max" => 100,
                        "default" => 0
                    ],
                    "con" => [
                        "title" => "Contrast",
                        "type" => "slider",
                        "min" => -100,
                        "max" => 100,
                        "default" => 0
                    ],
                    "gam" => [
                        "title" => "Gamma",
                        "type" => "slider",
                        "min" => 0.1,
                        "max" => 9.99,
                        "inc" => 0.01,
                        "default" => 1
                    ]
                ],
                "Color Controls" => [
                    "hue" => [
                        "title" => "Hue",
                        "type" => "slider",
                        "min" => -359,
                        "max" => 359,
                        "default" => 0,
                        "hidden" => (!class_exists("Imagick"))
                    ],
                    "sat" => [
                        "title" => "Saturation",
                        "type" => "slider",
                        "min" => -100,
                        "max" => 100,
                        "default" => 0,
                        "hidden" => (!class_exists("Imagick"))
                    ]
                ],
                "Sharpen/Blur" => [
                    "sharp" => [
                        "title" => "Sharpen",
                        "type" => "slider",
                        "min" => 0,
                        "max" => 100,
                        "default" => 0
                    ],
                    "usm" => [
                        "title" => "Unsharp Mask",
                        "type" => "slider",
                        "min" => 0,
                        "max" => 100,
                        "default" => 0,
                        "hidden" => (!class_exists("Imagick"))
                    ],
                    "usmrad" => [
                        "title" => "Unsharp Mask Radius",
                        "type" => "slider",
                        "min" => 0,
                        "max" => 500,
                        "default" => 0,
                        "hidden" => (!class_exists("Imagick"))
                    ],
                    "blur" => [
                        "title" => "Blur",
                        "type" => "slider",
                        "min" => 0,
                        "max" => 100,
                        "default" => 0
                    ]
                ]
            ],
            "stylize" => [
                "Stylize" => [
                    "blend" => [
                        "title" => "Tint",
                        "type" => "color",
                        "hidden" => (!class_exists("Imagick"))
                    ],
                    "px" => [
                        "title" => "Pixellate",
                        "type" => "slider",
                        "min" => 0,
                        "max" => 100,
                        "default" => 0
                    ],
                    "mono" => [
                        "title" => "Monochrome",
                        "type" => "color",
                        "hidden" => (!class_exists("Imagick"))
                    ],
                    "sepia" => [
                        "title" => "Sepia",
                        "type" => "slider",
                        "min" => 0,
                        "max" => 100,
                        "default" => 0,
                        "hidden" => (!class_exists("Imagick"))
                    ]
                ],
                "Border" => [
                    "border-color" => [
                        "title" => "Border Color",
                        "type" => "color",
                        "no-alpha" => true
                    ],
                    "border-width" => [
                        "title" => "Border Width",
                        "type" => "slider",
                        "min" => 0,
                        "max" => 500,
                        "default" => 0
                    ]
                ],
                "Padding" => [
                    "padding-color" => [
                        "title" => "Padding Color",
                        "type" => "color",
                        "no-alpha" => true
                    ],
                    "padding-width" => [
                        "title" => "Padding Width",
                        "type" => "slider",
                        "min" => 0,
                        "max" => 500,
                        "default" => 0
                    ]
                ]
            ],
            "watermark" => [
                "Watermark Media" => [
                    "media" => [
                        "title" => "Watermark Image",
                        "type" => "media-chooser",
                        "imgix-param" => "mark",
                        "dependents" => [
                            "markalign",
                            "markalpha",
                            "markpad",
                            "markscale"
                        ]
                    ]
                ],
                "Watermark Settings" => [
                    "markalign" => [
                        "title" => "Watermark Alignment",
                        "type" => "alignment"
                    ],
                    "markalpha" => [
                        "title" => "Watermark Alpha",
                        "type" => "slider",
                        "min" => 0,
                        "max" => 100,
                        "default" => 100
                    ],
                    "markpad" => [
                        "title" => "Watermark Padding",
                        "type" => "slider",
                        "min" => 0,
                        "max" => 100,
                        "default" => 0
                    ],
                    "markscale" => [
                        "title" => "Watermark Scale",
                        "type" => "slider",
                        "min" => 0,
                        "max" => 200,
                        "default" => 100
                    ]
                ]
            ],
	        "focus-crop" => [
		        "Focus" => [
			        "focalpoint" => [
				        "type" => "pillbox",
				        "exclusive" => true,
				        "options" => [
					        "focalpoint" => [
						        "title" => "Focal Point",
						        "default" => 0
					        ],
					        "usefaces" => [
						        "title" => "Use Faces",
						        "default" => 0
					        ]
				        ],
				        "selected" => function($settings, $currentValue, $selectedOutput, $unselectedOutput){
					        if (isset($settings['focalpoint']) && ($settings['focalpoint'] == $currentValue)) {
						        return $selectedOutput;
					        }

					        return $unselectedOutput;
				        }
			        ]
		        ],
		        "Focal Point" => [
			        "fp-z" => [
				        "title" => "Focal Point Zoom",
				        "type" => "slider",
				        "min" => 0,
				        "max" => 10,
				        "default" => 1
			        ]
		        ],
		        "Faces" => [
			        "faceindex" => [
				        "title" => "Face Index",
				        "type" => "slider",
				        "min" => 0,
				        "max" => 10,
				        "default" => 0
			        ]
		        ]
	        ]
        ]
    ]
];