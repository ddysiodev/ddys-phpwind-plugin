<?php

defined('WEKIT_VERSION') or exit(403);

function ddys_open_cache_dir()
{
    return ddys_open_plugin_path('cache');
}

function ddys_open_cache_key($method, $base, $path, $params)
{
    ksort($params);
    return sha1(strtoupper($method) . '|' . $base . '|' . $path . '|' . http_build_query($params, '', '&'));
}

function ddys_open_cache_file($key)
{
    return ddys_open_cache_dir() . '/' . preg_replace('/[^a-f0-9]/', '', strtolower($key)) . '.php';
}

function ddys_open_cache_get($key)
{
    $file = ddys_open_cache_file($key);
    if (!is_file($file)) {
        return false;
    }
    $payload = @include $file;
    if (!is_array($payload) || empty($payload['expires']) || !array_key_exists('value', $payload)) {
        @unlink($file);
        return false;
    }
    if ((int)$payload['expires'] < time()) {
        @unlink($file);
        return false;
    }
    return $payload['value'];
}

function ddys_open_cache_set($key, $value, $ttl)
{
    $ttl = (int)$ttl;
    if ($ttl <= 0) {
        return false;
    }
    ddys_open_ensure_runtime();
    $file = ddys_open_cache_file($key);
    $data = "<?php\nreturn " . var_export(array(
        'expires' => time() + $ttl,
        'created' => time(),
        'value' => $value
    ), true) . ";\n";
    return @file_put_contents($file, $data, LOCK_EX) !== false;
}

function ddys_open_cache_clear()
{
    $count = 0;
    foreach (glob(ddys_open_cache_dir() . '/*.php') ?: array() as $file) {
        if (@unlink($file)) {
            $count++;
        }
    }
    foreach (glob(ddys_open_cache_dir() . '/request_*.lock') ?: array() as $file) {
        if (@unlink($file)) {
            $count++;
        }
    }
    return $count;
}

function ddys_open_cache_stats()
{
    $files = glob(ddys_open_cache_dir() . '/*.php') ?: array();
    $size = 0;
    $expired = 0;
    foreach ($files as $file) {
        $size += is_file($file) ? filesize($file) : 0;
        $payload = @include $file;
        if (!is_array($payload) || empty($payload['expires']) || (int)$payload['expires'] < time()) {
            $expired++;
        }
    }
    return array(
        'files' => count($files),
        'expired' => $expired,
        'size' => $size,
        'writable' => is_writable(ddys_open_cache_dir())
    );
}

function ddys_open_check_rate_limit($scope, $identity, $interval)
{
    ddys_open_ensure_runtime();
    $interval = max(1, (int)$interval);
    $key = sha1($scope . '|' . $identity);
    $file = ddys_open_cache_dir() . '/request_' . $key . '.lock';
    if (is_file($file) && (time() - (int)filemtime($file)) < $interval) {
        return false;
    }
    @file_put_contents($file, (string)time(), LOCK_EX);
    ddys_open_prune_locks();
    return true;
}

function ddys_open_prune_locks()
{
    foreach (glob(ddys_open_cache_dir() . '/request_*.lock') ?: array() as $file) {
        if (is_file($file) && (time() - (int)filemtime($file)) > 86400) {
            @unlink($file);
        }
    }
}


