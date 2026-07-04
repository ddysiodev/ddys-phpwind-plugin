<?php

defined('WEKIT_VERSION') or exit(403);

defined('DDYS_OPEN_PHPWIND_ID') || define('DDYS_OPEN_PHPWIND_ID', 'ddys_open');
defined('DDYS_OPEN_PHPWIND_VERSION') || define('DDYS_OPEN_PHPWIND_VERSION', '0.1.0');
defined('DDYS_OPEN_PHPWIND_API_DEFAULT') || define('DDYS_OPEN_PHPWIND_API_DEFAULT', 'https://ddys.io/api/v1');
defined('DDYS_OPEN_PHPWIND_SITE_DEFAULT') || define('DDYS_OPEN_PHPWIND_SITE_DEFAULT', 'https://ddys.io');

function ddys_open_bootstrap()
{
    static $loaded = false;
    if ($loaded) {
        return;
    }
    $loaded = true;
    $base = dirname(__FILE__);
    require_once $base . '/security.php';
    require_once $base . '/cache.php';
    require_once $base . '/client.php';
    require_once $base . '/render.php';
    require_once $base . '/shortcode.php';
    ddys_open_ensure_runtime();
}

function ddys_open_plugin_path($path = '')
{
    $base = dirname(dirname(__FILE__));
    return $path === '' ? $base : $base . '/' . ltrim($path, '/');
}

function ddys_open_ensure_runtime()
{
    $cache = ddys_open_plugin_path('cache');
    if (!is_dir($cache)) {
        @mkdir($cache, 0755, true);
    }
    $index = $cache . '/index.html';
    if (!file_exists($index)) {
        @file_put_contents($index, '');
    }
    $htaccess = $cache . '/.htaccess';
    if (!file_exists($htaccess)) {
        @file_put_contents($htaccess, "Deny from all\n");
    }
}


