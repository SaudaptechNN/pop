<?php
// Edit these values when moving the site to live hosting.
return [
    'db' => [
        'host'     => 'localhost',
        'port'     => 3306,
        'name'     => 'thepopsybar',
        'user'     => 'root',
        'password' => '',
    ],

    // Leave empty to auto-detect (works for /thepopsybar/ on XAMPP and for the
    // domain root on hosting). Set it only if links break, e.g. '/thepopsybar/'.
    'base_url' => '',

    // Max upload size for images in the admin panel, in megabytes.
    'max_image_mb' => 8,
    'max_video_mb' => 60,

    // Show PHP errors on screen. Set to false on the live site.
    'debug' => true,
];
