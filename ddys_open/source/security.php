<?php

defined('WEKIT_VERSION') or exit(403);

function ddys_open_defaults()
{
    return array(
        'api_base_url' => DDYS_OPEN_PHPWIND_API_DEFAULT,
        'site_base_url' => DDYS_OPEN_PHPWIND_SITE_DEFAULT,
        'api_key' => '',
        'timeout' => 12,
        'default_cache_ttl' => 300,
        'dictionary_cache_ttl' => 86400,
        'fresh_cache_ttl' => 300,
        'list_cache_ttl' => 600,
        'detail_cache_ttl' => 1800,
        'community_cache_ttl' => 120,
        'theme' => 'auto',
        'layout' => 'grid',
        'columns' => 4,
        'target' => '_blank',
        'show_source_link' => 1,
        'enable_styles' => 1,
        'enable_request_form' => 0,
        'show_nav' => 1,
        'default_limit' => 12,
        'request_interval' => 60,
        'debug' => 0
    );
}

function ddys_open_settings()
{
    $saved = array();
    $file = ddys_open_config_file();
    if (is_file($file)) {
        $value = @include $file;
        if (is_array($value)) $saved = $value;
    }
    return ddys_open_normalize_settings(array_merge(ddys_open_defaults(), $saved));
}

function ddys_open_normalize_settings($settings)
{
    $settings['api_base_url'] = ddys_open_normalize_base_url(isset($settings['api_base_url']) ? $settings['api_base_url'] : '', DDYS_OPEN_PHPWIND_API_DEFAULT);
    $settings['site_base_url'] = ddys_open_normalize_base_url(isset($settings['site_base_url']) ? $settings['site_base_url'] : '', DDYS_OPEN_PHPWIND_SITE_DEFAULT);
    $settings['api_key'] = trim((string)(isset($settings['api_key']) ? $settings['api_key'] : ''));
    $settings['timeout'] = ddys_open_int_range(isset($settings['timeout']) ? $settings['timeout'] : 12, 12, 1, 30);
    $settings['default_cache_ttl'] = ddys_open_int_range(isset($settings['default_cache_ttl']) ? $settings['default_cache_ttl'] : 300, 300, 0, 604800);
    $settings['dictionary_cache_ttl'] = ddys_open_int_range(isset($settings['dictionary_cache_ttl']) ? $settings['dictionary_cache_ttl'] : 86400, 86400, 0, 604800);
    $settings['fresh_cache_ttl'] = ddys_open_int_range(isset($settings['fresh_cache_ttl']) ? $settings['fresh_cache_ttl'] : 300, 300, 0, 604800);
    $settings['list_cache_ttl'] = ddys_open_int_range(isset($settings['list_cache_ttl']) ? $settings['list_cache_ttl'] : 600, 600, 0, 604800);
    $settings['detail_cache_ttl'] = ddys_open_int_range(isset($settings['detail_cache_ttl']) ? $settings['detail_cache_ttl'] : 1800, 1800, 0, 604800);
    $settings['community_cache_ttl'] = ddys_open_int_range(isset($settings['community_cache_ttl']) ? $settings['community_cache_ttl'] : 120, 120, 0, 604800);
    $settings['theme'] = ddys_open_choice(isset($settings['theme']) ? $settings['theme'] : 'auto', array('auto', 'light', 'dark'), 'auto');
    $settings['layout'] = ddys_open_choice(isset($settings['layout']) ? $settings['layout'] : 'grid', array('grid', 'list', 'compact'), 'grid');
    $settings['columns'] = ddys_open_int_range(isset($settings['columns']) ? $settings['columns'] : 4, 4, 1, 6);
    $settings['target'] = ddys_open_choice(isset($settings['target']) ? $settings['target'] : '_blank', array('_blank', '_self'), '_blank');
    $settings['default_limit'] = ddys_open_int_range(isset($settings['default_limit']) ? $settings['default_limit'] : 12, 12, 1, 50);
    $settings['request_interval'] = ddys_open_int_range(isset($settings['request_interval']) ? $settings['request_interval'] : 60, 60, 10, 3600);
    foreach (array('show_source_link', 'enable_styles', 'enable_request_form', 'show_nav', 'debug') as $key) {
        $settings[$key] = !empty($settings[$key]) && ddys_open_bool($settings[$key]) ? 1 : 0;
    }
    return $settings;
}

function ddys_open_save_settings($settings)
{
    $settings = ddys_open_normalize_settings($settings);
    $file = ddys_open_config_file();
    if (class_exists('WindFolder')) {
        WindFolder::mkRecur(dirname($file));
    } elseif (!is_dir(dirname($file))) {
        @mkdir(dirname($file), 0755, true);
    }
    if (class_exists('WindFile')) {
        return WindFile::savePhpData($file, $settings) !== false;
    }
    $data = "<?php\nreturn " . var_export($settings, true) . ";\n";
    return @file_put_contents($file, $data, LOCK_EX) !== false;
}

function ddys_open_config_file()
{
    if (class_exists('Wind')) {
        return Wind::getRealPath('EXT:' . DDYS_OPEN_PHPWIND_ID . '.conf', false);
    }
    return ddys_open_plugin_path('conf');
}

function ddys_open_h($value)
{
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

function ddys_open_attr($value)
{
    return ddys_open_h($value);
}

function ddys_open_get($key, $default = '')
{
    if (class_exists('Wind')) {
        $request = Wind::getApp()->getRequest();
        return ddys_open_scalar($request->getGet($key, $default), $default);
    }
    return isset($_GET[$key]) ? ddys_open_scalar($_GET[$key], $default) : $default;
}

function ddys_open_post($key, $default = '')
{
    if (class_exists('Wind')) {
        $request = Wind::getApp()->getRequest();
        return ddys_open_scalar($request->getPost($key, $default), $default);
    }
    return isset($_POST[$key]) ? ddys_open_scalar($_POST[$key], $default) : $default;
}

function ddys_open_post_array($key)
{
    if (isset($_POST[$key]) && is_array($_POST[$key])) {
        return $_POST[$key];
    }
    return array();
}

function ddys_open_scalar($value, $default = '')
{
    if (is_array($value) || is_object($value)) {
        return $default;
    }
    return trim(str_replace("\0", '', (string)$value));
}

function ddys_open_bool($value)
{
    if (is_bool($value)) {
        return $value;
    }
    return in_array(strtolower(trim((string)$value)), array('1', 'true', 'yes', 'on'), true);
}

function ddys_open_int_range($value, $fallback, $min, $max)
{
    if (!is_numeric($value)) {
        return $fallback;
    }
    $value = (int)$value;
    if ($value < $min) {
        return $min;
    }
    if ($value > $max) {
        return $max;
    }
    return $value;
}

function ddys_open_choice($value, $allowed, $fallback)
{
    $value = strtolower(trim((string)$value));
    return in_array($value, $allowed, true) ? $value : $fallback;
}

function ddys_open_normalize_base_url($value, $fallback)
{
    $value = trim((string)$value);
    if ($value === '' || !preg_match('#^https?://#i', $value)) {
        return $fallback;
    }
    $parts = parse_url($value);
    if (!is_array($parts) || empty($parts['scheme']) || empty($parts['host']) || !empty($parts['user']) || !empty($parts['pass'])) {
        return $fallback;
    }
    return rtrim($value, '/');
}

function ddys_open_normalize_query_value($key, $value)
{
    $value = ddys_open_scalar($value);
    if ($value === '') {
        return '';
    }
    if ($key === 'limit' || $key === 'per_page') {
        return ddys_open_int_range($value, 12, 1, 50);
    }
    if ($key === 'page') {
        return ddys_open_int_range($value, 1, 1, 999);
    }
    if ($key === 'year') {
        return ddys_open_int_range($value, 0, 0, 2099);
    }
    if ($key === 'month') {
        return ddys_open_int_range($value, 0, 0, 12);
    }
    return $value;
}

function ddys_open_build_query($source, $keys)
{
    $query = array();
    foreach ($keys as $key) {
        if (isset($source[$key]) && ddys_open_scalar($source[$key]) !== '') {
            $query[$key] = ddys_open_normalize_query_value($key, $source[$key]);
        }
    }
    return $query;
}

function ddys_open_site_root()
{
    if (class_exists('Wekit')) {
        $url = Wekit::url();
        if (!empty($url->base)) {
            return rtrim($url->base, '/') . '/';
        }
    }
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : '';
    $script = isset($_SERVER['SCRIPT_NAME']) ? dirname($_SERVER['SCRIPT_NAME']) : '';
    return $host === '' ? './' : $scheme . '://' . $host . rtrim(str_replace('\\', '/', $script), '/') . '/';
}

function ddys_open_plugin_url($path = '')
{
    if (class_exists('Wekit')) {
        $url = Wekit::url();
        if (!empty($url->extres)) {
            return rtrim($url->extres, '/') . '/' . DDYS_OPEN_PHPWIND_ID . '/' . ltrim($path, '/');
        }
    }
    return ddys_open_site_root() . 'themes/extres/' . DDYS_OPEN_PHPWIND_ID . '/' . ltrim($path, '/');
}

function ddys_open_page_url($view = 'latest', $params = array())
{
    $view = ddys_open_choice($view, ddys_open_page_views(), 'latest');
    $query = array_merge(array('view' => $view), (array)$params);
    if ($view === 'latest') {
        unset($query['view']);
    }
    return ddys_open_append_query(ddys_open_plugin_page_url(), $query);
}

function ddys_open_page_views()
{
    return array(
        'movies',
        'latest',
        'hot',
        'search',
        'suggest',
        'calendar',
        'movie',
        'sources',
        'related',
        'comments',
        'collections',
        'collection',
        'shares',
        'share',
        'requests',
        'activities',
        'user',
        'types',
        'genres',
        'regions'
    );
}

function ddys_open_endpoint_url($endpoint)
{
    return ddys_open_route_url('index', $endpoint === 'request' ? 'request' : 'api');
}

function ddys_open_admin_url($params = array())
{
    return ddys_open_route_url('manage', 'run', $params, true);
}

function ddys_open_plugin_page_url()
{
    return ddys_open_route_url('index', 'run');
}

function ddys_open_route_url($controller = 'index', $action = 'run', $params = array(), $admin = false)
{
    $params = array_merge(array('app' => DDYS_OPEN_PHPWIND_ID), (array)$params);
    if (class_exists('WindUrlHelper')) {
        return WindUrlHelper::createUrl('app/' . $controller . '/' . $action, $params);
    }
    $query = array_merge(array(
        'm' => 'app',
        'app' => DDYS_OPEN_PHPWIND_ID,
        'c' => $controller,
        'a' => $action
    ), (array)$params);
    return ddys_open_append_query($admin ? 'admin.php' : 'index.php', $query);
}

function ddys_open_append_query($url, $params)
{
    $clean = array();
    foreach ((array)$params as $key => $value) {
        $value = ddys_open_scalar($value);
        if ($value !== '') {
            $clean[$key] = $value;
        }
    }
    if (empty($clean)) {
        return $url;
    }
    return $url . (strpos($url, '?') === false ? '?' : '&') . http_build_query($clean, '', '&');
}

function ddys_open_nonce($context = 'front')
{
    $seed = ddys_open_nonce_seed($context);
    $slot = floor(time() / 3600);
    return ddys_open_hash($seed . '|' . $slot);
}

function ddys_open_verify_nonce($nonce, $context = 'front')
{
    $seed = ddys_open_nonce_seed($context);
    $slot = floor(time() / 3600);
    return ddys_open_hash_equals(ddys_open_hash($seed . '|' . $slot), $nonce)
        || ddys_open_hash_equals(ddys_open_hash($seed . '|' . ($slot - 1)), $nonce);
}

function ddys_open_nonce_seed($context)
{
    $key = ddys_open_site_secret();
    $uid = 0;
    if (class_exists('Wekit')) {
        $user = Wekit::getLoginUser();
        if ($user && isset($user->uid)) $uid = (int)$user->uid;
    }
    return $key . '|' . $context . '|' . $uid . '|' . ddys_open_user_ip();
}

function ddys_open_hash($value)
{
    $key = ddys_open_site_secret();
    if (function_exists('hash_hmac')) {
        return hash_hmac('sha256', $value, $key);
    }
    return sha1($key . '|' . $value);
}

function ddys_open_site_secret()
{
    if (class_exists('Wekit')) {
        $parts = array(
            Wekit::C('site', 'cookie.pre'),
            Wekit::C('site', 'info.url'),
            Wekit::C('site', 'charset')
        );
        $secret = trim(implode('|', $parts), '|');
        if ($secret !== '') return $secret;
    }
    return defined('WEKIT_VERSION') ? 'ddys-open-phpwind|' . WEKIT_VERSION : 'ddys-open-phpwind';
}

function ddys_open_hash_equals($known, $user)
{
    if (function_exists('hash_equals')) {
        return hash_equals((string)$known, (string)$user);
    }
    $known = (string)$known;
    $user = (string)$user;
    if (strlen($known) !== strlen($user)) {
        return false;
    }
    $result = 0;
    for ($i = 0; $i < strlen($known); $i++) {
        $result |= ord($known[$i]) ^ ord($user[$i]);
    }
    return $result === 0;
}

function ddys_open_user_ip()
{
    return isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '0.0.0.0';
}

function ddys_open_json_response($payload, $status = 200)
{
    if ($status === 200 && ddys_open_is_error($payload) && !empty($payload['status'])) {
        $status = ddys_open_int_range($payload['status'], 500, 400, 599);
    }
    if (!headers_sent()) {
        if (function_exists('http_response_code')) {
            http_response_code($status);
        }
        header('Content-Type: application/json; charset=utf-8', true, $status);
    }
    echo json_encode($payload, defined('JSON_UNESCAPED_UNICODE') ? JSON_UNESCAPED_UNICODE : 0);
    exit;
}

function ddys_open_error($message, $status = 0, $payload = array())
{
    return array(
        'ddys_error' => true,
        'success' => false,
        'message' => (string)$message,
        'status' => (int)$status,
        'payload' => $payload
    );
}

function ddys_open_is_error($value)
{
    return is_array($value) && !empty($value['ddys_error']);
}

function ddys_open_safe_media_url($value)
{
    $value = trim((string)$value);
    return preg_match('#^https?://#i', $value) ? $value : '';
}

function ddys_open_substr($value, $start, $length)
{
    $value = (string)$value;
    if (function_exists('mb_substr')) {
        return mb_substr($value, $start, $length, 'UTF-8');
    }
    return substr($value, $start, $length);
}

function ddys_open_strlen($value)
{
    $value = (string)$value;
    if (function_exists('mb_strlen')) {
        return mb_strlen($value, 'UTF-8');
    }
    return strlen($value);
}

