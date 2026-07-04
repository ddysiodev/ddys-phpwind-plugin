<?php

defined('WEKIT_VERSION') or exit(403);

function ddys_open_payload_data($payload)
{
    if (is_array($payload) && array_key_exists('data', $payload)) {
        return $payload['data'];
    }
    return $payload;
}

function ddys_open_payload_meta($payload)
{
    return is_array($payload) && isset($payload['meta']) && is_array($payload['meta']) ? $payload['meta'] : array();
}

function ddys_open_is_assoc($array)
{
    return is_array($array) && !empty($array) && array_keys($array) !== range(0, count($array) - 1);
}

function ddys_open_to_list($data)
{
    if (!is_array($data)) {
        return array();
    }
    foreach (array('items', 'movies', 'results', 'records', 'list', 'shares', 'requests', 'activities', 'comments') as $key) {
        if (isset($data[$key]) && is_array($data[$key])) {
            return $data[$key];
        }
    }
    if (ddys_open_is_assoc($data) && (isset($data['slug']) || isset($data['id']) || isset($data['title']) || isset($data['name']))) {
        return array($data);
    }
    return ddys_open_is_assoc($data) ? array() : $data;
}

function ddys_open_value($item, $keys, $fallback = '')
{
    if (!is_array($item)) {
        return $fallback;
    }
    foreach ($keys as $key) {
        if (isset($item[$key]) && $item[$key] !== '') {
            return $item[$key];
        }
    }
    return $fallback;
}

function ddys_open_wrap($html, $args = array())
{
    $settings = ddys_open_settings();
    $layout = isset($args['layout']) && $args['layout'] !== '' ? $args['layout'] : $settings['layout'];
    $theme = isset($args['theme']) && $args['theme'] !== '' ? $args['theme'] : $settings['theme'];
    $columns = isset($args['columns']) && $args['columns'] !== '' ? (int)$args['columns'] : (int)$settings['columns'];
    $layout = ddys_open_choice($layout, array('grid', 'list', 'compact'), $settings['layout']);
    $theme = ddys_open_choice($theme, array('auto', 'light', 'dark'), $settings['theme']);
    $columns = ddys_open_int_range($columns, 4, 1, 6);
    return ddys_open_frontend_assets() . '<div class="ddys-phpwind ddys-phpwind-theme-' . ddys_open_attr($theme) . ' ddys-phpwind-layout-' . ddys_open_attr($layout) . '" style="--ddys-phpwind-columns:' . $columns . '">' . $html . '</div>';
}

function ddys_open_render_error($payload, $args = array())
{
    $message = is_array($payload) && isset($payload['message']) ? $payload['message'] : '低端影视内容加载失败。';
    return ddys_open_wrap('<div class="ddys-phpwind-alert ddys-phpwind-alert-error">' . ddys_open_h($message) . '</div>', $args);
}

function ddys_open_render_empty($message, $args = array())
{
    return ddys_open_wrap('<div class="ddys-phpwind-empty">' . ddys_open_h($message) . '</div>', $args);
}

function ddys_open_item_url($item)
{
    $settings = ddys_open_settings();
    $url = ddys_open_value($item, array('url', 'link', 'href'), '');
    if ($url !== '' && preg_match('#^https?://#i', $url)) {
        return $url;
    }
    if ($url !== '' && substr($url, 0, 1) === '/') {
        return rtrim($settings['site_base_url'], '/') . $url;
    }
    $slug = ddys_open_value($item, array('slug'), '');
    if ($slug !== '') {
        return rtrim($settings['site_base_url'], '/') . '/movie/' . rawurlencode($slug);
    }
    return '';
}

function ddys_open_render_card($item, $settings)
{
    if (!is_array($item)) {
        return '';
    }
    $title = ddys_open_value($item, array('title', 'name', 'cn_name', 'username'), 'Untitled');
    $poster = ddys_open_safe_media_url(ddys_open_value($item, array('poster', 'cover', 'image', 'avatar'), ''));
    $url = ddys_open_item_url($item);
    $meta = array();
    foreach (array('year', 'type', 'type_code', 'region', 'quality', 'episode') as $key) {
        if (!empty($item[$key])) {
            $meta[] = $item[$key];
        }
    }
    if (!empty($item['rating'])) {
        $meta[] = '评分 ' . $item['rating'];
    }
    $summary = ddys_open_value($item, array('description', 'intro', 'summary', 'note', 'content'), '');

    $html = '<article class="ddys-phpwind-card">';
    if ($poster !== '') {
        $html .= '<div class="ddys-phpwind-poster"><img src="' . ddys_open_attr($poster) . '" alt="' . ddys_open_attr($title) . '" loading="lazy"></div>';
    }
    $html .= '<div class="ddys-phpwind-card-body">';
    $html .= '<h3 class="ddys-phpwind-card-title">';
    if ($url !== '' && !empty($settings['show_source_link'])) {
        $html .= '<a href="' . ddys_open_attr($url) . '" target="' . ddys_open_attr($settings['target']) . '" rel="noopener">' . ddys_open_h($title) . '</a>';
    } else {
        $html .= ddys_open_h($title);
    }
    $html .= '</h3>';
    if (!empty($meta)) {
        $html .= '<div class="ddys-phpwind-meta">' . ddys_open_h(implode(' / ', $meta)) . '</div>';
    }
    if ($summary !== '') {
        $html .= '<div class="ddys-phpwind-summary">' . ddys_open_h(ddys_open_substr(strip_tags((string)$summary), 0, 150)) . '</div>';
    }
    $html .= '</div></article>';
    return $html;
}

function ddys_open_render_list($payload, $args = array())
{
    if (ddys_open_is_error($payload)) {
        return ddys_open_render_error($payload, $args);
    }
    $items = ddys_open_to_list(ddys_open_payload_data($payload));
    if (empty($items)) {
        return ddys_open_render_empty('暂无低端影视内容。', $args);
    }
    $settings = ddys_open_settings();
    $html = '<div class="ddys-phpwind-items">';
    foreach ($items as $item) {
        $html .= ddys_open_render_card($item, $settings);
    }
    $html .= '</div>' . ddys_open_render_pagination_meta(ddys_open_payload_meta($payload));
    return ddys_open_wrap($html, $args);
}

function ddys_open_render_detail($payload, $args = array())
{
    if (ddys_open_is_error($payload)) {
        return ddys_open_render_error($payload, $args);
    }
    $data = ddys_open_payload_data($payload);
    if (!is_array($data)) {
        return ddys_open_render_empty('暂无详情。', $args);
    }
    $settings = ddys_open_settings();
    $html = '<div class="ddys-phpwind-detail">';
    $html .= ddys_open_render_card($data, $settings);
    $intro = ddys_open_value($data, array('intro', 'description', 'summary', 'note', 'content'), '');
    if ($intro !== '') {
        $html .= '<div class="ddys-phpwind-description">' . nl2br(ddys_open_h($intro)) . '</div>';
    }
    if (!empty($data['movies']) && is_array($data['movies'])) {
        $html .= '<h3>影片</h3><div class="ddys-phpwind-items">';
        foreach ($data['movies'] as $item) {
            $html .= ddys_open_render_card($item, $settings);
        }
        $html .= '</div>';
    }
    if (!empty($data['resources']) || !empty($data['sources']) || !empty($data['online']) || !empty($data['download'])) {
        $html .= ddys_open_render_sources(array('data' => $data), $args, true);
    }
    $html .= '</div>';
    return ddys_open_wrap($html, $args);
}

function ddys_open_render_sources($payload, $args = array(), $inner = false)
{
    if (ddys_open_is_error($payload)) {
        return ddys_open_render_error($payload, $args);
    }
    $data = ddys_open_payload_data($payload);
    if (!is_array($data)) {
        return ddys_open_render_empty('暂无资源。', $args);
    }
    $groups = array();
    if (isset($data['online']) || isset($data['download'])) {
        if (!empty($data['online'])) {
            $groups['在线播放'] = $data['online'];
        }
        if (!empty($data['download'])) {
            $groups['下载资源'] = $data['download'];
        }
    } elseif (isset($data['resources'])) {
        $groups['资源'] = $data['resources'];
    } elseif (isset($data['sources'])) {
        $groups['资源'] = $data['sources'];
    } else {
        $groups = ddys_open_is_assoc($data) ? $data : array('资源' => $data);
    }

    $html = '<div class="ddys-phpwind-sources">';
    foreach ($groups as $name => $resources) {
        if (!is_array($resources)) {
            continue;
        }
        $html .= '<section class="ddys-phpwind-source-group"><h3>' . ddys_open_h($name) . '</h3>';
        foreach ($resources as $resource) {
            if (is_string($resource)) {
                $html .= '<p class="ddys-phpwind-resource">' . ddys_open_render_resource_links('资源', $resource) . '</p>';
                continue;
            }
            if (!is_array($resource)) {
                continue;
            }
            $title = ddys_open_value($resource, array('title', 'name', 'label', 'download_type', 'type'), '资源');
            $url = ddys_open_value($resource, array('url', 'link', 'href'), '');
            $html .= '<p class="ddys-phpwind-resource">' . ddys_open_render_resource_links($title, $url) . '</p>';
        }
        $html .= '</section>';
    }
    $html .= '</div>';
    return $inner ? $html : ddys_open_wrap($html, $args);
}

function ddys_open_render_resource_links($title, $url)
{
    if ($url === '') {
        return ddys_open_h($title);
    }
    $parts = explode('#', $url);
    $links = array();
    foreach ($parts as $index => $part) {
        $part = trim($part);
        if ($part === '') {
            continue;
        }
        $label = $title;
        $href = $part;
        if (strpos($part, '$') !== false) {
            $pair = explode('$', $part, 2);
            $label = $pair[0] !== '' ? $pair[0] : $title;
            $href = isset($pair[1]) ? $pair[1] : '';
        } elseif (count($parts) > 1) {
            $label = $title . ' ' . ($index + 1);
        }
        if (preg_match('#^(https?:|magnet:|ed2k:|thunder:)#i', $href)) {
            $links[] = '<a href="' . ddys_open_attr($href) . '" target="_blank" rel="noopener">' . ddys_open_h($label) . '</a>';
        }
    }
    return empty($links) ? ddys_open_h($title) : implode(' ', $links);
}

function ddys_open_render_calendar($payload, $args = array())
{
    if (ddys_open_is_error($payload)) {
        return ddys_open_render_error($payload, $args);
    }
    $data = ddys_open_payload_data($payload);
    $days = isset($data['days']) && is_array($data['days']) ? $data['days'] : $data;
    if (!is_array($days)) {
        return ddys_open_render_list($payload, $args);
    }
    $settings = ddys_open_settings();
    $html = '<div class="ddys-phpwind-calendar">';
    foreach ($days as $day => $items) {
        if (is_array($items) && isset($items['shows']) && is_array($items['shows'])) {
            $items = $items['shows'];
        }
        $html .= '<section class="ddys-phpwind-calendar-day"><h3>' . ddys_open_h($day) . '</h3><div class="ddys-phpwind-items">';
        if (is_array($items)) {
            foreach ($items as $item) {
                $html .= ddys_open_render_card($item, $settings);
            }
        }
        $html .= '</div></section>';
    }
    $html .= '</div>';
    return ddys_open_wrap($html, $args);
}

function ddys_open_render_dictionary($payload, $args = array())
{
    if (ddys_open_is_error($payload)) {
        return ddys_open_render_error($payload, $args);
    }
    $data = ddys_open_payload_data($payload);
    if (ddys_open_is_assoc($data)) {
        $items = array();
        foreach ($data as $code => $label) {
            $items[] = is_array($label) ? $label : array('code' => $code, 'name' => $label);
        }
    } else {
        $items = ddys_open_to_list($data);
    }
    if (empty($items)) {
        return ddys_open_render_empty('暂无字典数据。', $args);
    }
    $html = '<div class="ddys-phpwind-tags">';
    foreach ($items as $item) {
        $label = is_array($item) ? ddys_open_value($item, array('name', 'title', 'label', 'value'), '') : $item;
        $code = is_array($item) ? ddys_open_value($item, array('code', 'slug', 'id'), '') : '';
        if ($label !== '') {
            $html .= '<span>' . ddys_open_h($label) . ($code !== '' ? ' <code>' . ddys_open_h($code) . '</code>' : '') . '</span>';
        }
    }
    $html .= '</div>';
    return ddys_open_wrap($html, $args);
}

function ddys_open_render_search($args = array())
{
    $q = ddys_open_get('q', ddys_open_get('ddys_q', isset($args['q']) ? $args['q'] : ''));
    $type = ddys_open_get('type', ddys_open_get('ddys_type', isset($args['type']) ? $args['type'] : 'movie'));
    $type = ddys_open_choice($type, array('movie', 'share', 'request'), 'movie');
    $html = '<form class="ddys-phpwind-search" method="get" action="' . ddys_open_attr(ddys_open_plugin_page_url()) . '">';
    $html .= '<input type="hidden" name="m" value="app">';
    $html .= '<input type="hidden" name="c" value="index">';
    $html .= '<input type="hidden" name="a" value="run">';
    $html .= '<input type="hidden" name="app" value="' . ddys_open_attr(DDYS_OPEN_PHPWIND_ID) . '">';
    $html .= '<input type="hidden" name="view" value="search">';
    $html .= '<input type="search" name="q" value="' . ddys_open_attr($q) . '" placeholder="搜索低端影视">';
    $html .= '<select name="type"><option value="movie"' . ($type === 'movie' ? ' selected' : '') . '>影片</option><option value="share"' . ($type === 'share' ? ' selected' : '') . '>分享</option><option value="request"' . ($type === 'request' ? ' selected' : '') . '>求片</option></select>';
    $html .= '<button type="submit">搜索</button></form>';
    if ($q !== '') {
        $payload = ddys_open_api_get('/search', array('q' => $q, 'type' => $type, 'per_page' => isset($args['per_page']) ? $args['per_page'] : 12), array());
        $html .= ddys_open_render_list($payload, $args);
    }
    return ddys_open_wrap($html, $args);
}

function ddys_open_render_request_form($args = array())
{
    $settings = ddys_open_settings();
    if (empty($settings['enable_request_form'])) {
        return ddys_open_render_empty('求片表单未启用。', $args);
    }
    $html = '<form class="ddys-phpwind-request-form" method="post" action="' . ddys_open_attr(ddys_open_endpoint_url('request')) . '" data-ddys-phpwind-request-form>';
    $html .= '<input type="hidden" name="ddys_nonce" value="' . ddys_open_attr(ddys_open_nonce('front')) . '">';
    $html .= '<label>片名<input type="text" name="title" maxlength="255" required></label>';
    $html .= '<label>年份<input type="number" name="year" min="1900" max="2099"></label>';
    $html .= '<label>类型<select name="type"><option value=""></option><option value="movie">电影</option><option value="series">剧集</option><option value="variety">综艺</option><option value="anime">动漫</option></select></label>';
    $html .= '<label>豆瓣 ID<input type="text" name="douban_id" maxlength="30"></label>';
    $html .= '<label>IMDb ID<input type="text" name="imdb_id" maxlength="30"></label>';
    $html .= '<label>备注<textarea name="description" maxlength="1000"></textarea></label>';
    $html .= '<button type="submit">提交求片</button><p class="ddys-phpwind-status" role="status"></p></form>';
    return ddys_open_wrap($html, $args);
}

function ddys_open_render_pagination_meta($meta)
{
    if (!is_array($meta) || empty($meta['total'])) {
        return '';
    }
    $page = isset($meta['page']) ? (int)$meta['page'] : 1;
    return '<div class="ddys-phpwind-page-meta">第 ' . ddys_open_h($page) . ' 页，共 ' . ddys_open_h($meta['total']) . ' 条</div>';
}

function ddys_open_frontend_assets()
{
    static $printed = false;
    if ($printed) {
        return '';
    }
    $printed = true;
    $settings = ddys_open_settings();
    $html = '';
    if (!empty($settings['enable_styles'])) {
        $html .= "\n" . '<link rel="stylesheet" href="' . ddys_open_attr(ddys_open_plugin_url('static/css/frontend.css?v=' . DDYS_OPEN_PHPWIND_VERSION)) . '">';
    }
    $html .= "\n" . '<script defer src="' . ddys_open_attr(ddys_open_plugin_url('static/js/frontend.js?v=' . DDYS_OPEN_PHPWIND_VERSION)) . '"></script>';
    return $html;
}

function ddys_open_print_frontend_assets()
{
    echo ddys_open_frontend_assets();
}

function ddys_open_admin_assets()
{
    if (!ddys_open_is_admin_plugin_page()) {
        return '';
    }
    $url = ddys_open_plugin_url();
    return "\n" . '<link rel="stylesheet" href="' . ddys_open_attr($url . 'static/css/admin.css?v=' . DDYS_OPEN_PHPWIND_VERSION) . '">'
        . "\n" . '<script defer src="' . ddys_open_attr($url . 'static/js/admin.js?v=' . DDYS_OPEN_PHPWIND_VERSION) . '"></script>';
}

function ddys_open_is_admin_plugin_page()
{
    return ddys_open_get('app') === DDYS_OPEN_PHPWIND_ID;
}

function ddys_open_print_admin_assets()
{
    echo ddys_open_admin_assets();
}

function ddys_open_nav_item()
{
    $settings = ddys_open_settings();
    if (empty($settings['show_nav'])) {
        return '';
    }
    return '<a class="ddys-phpwind-nav nav-link" href="' . ddys_open_attr(ddys_open_page_url('latest')) . '">低端影视</a>';
}

function ddys_open_print_nav_item()
{
    echo ddys_open_nav_item();
}

function ddys_open_render_page($view, $params = array())
{
    $view = ddys_open_choice($view, ddys_open_page_views(), 'latest');
    if ($view === 'movies') {
        return ddys_open_render_shortcode('ddys_movies', array(
            'type' => isset($params['type']) ? $params['type'] : '',
            'genre' => isset($params['genre']) ? $params['genre'] : '',
            'region' => isset($params['region']) ? $params['region'] : '',
            'year' => isset($params['year']) ? $params['year'] : '',
            'sort' => isset($params['sort']) ? $params['sort'] : 'latest',
            'page' => isset($params['page']) ? $params['page'] : 1,
            'per_page' => isset($params['per_page']) && $params['per_page'] !== '' ? $params['per_page'] : (isset($params['limit']) ? $params['limit'] : 12)
        ));
    }
    if ($view === 'hot') {
        return ddys_open_render_shortcode('ddys_hot', array('limit' => isset($params['limit']) ? $params['limit'] : 12));
    }
    if ($view === 'search') {
        return ddys_open_render_shortcode('ddys_search', array('q' => isset($params['q']) ? $params['q'] : '', 'type' => isset($params['type']) ? $params['type'] : 'movie', 'per_page' => isset($params['per_page']) ? $params['per_page'] : ''));
    }
    if ($view === 'suggest') {
        $q = isset($params['q']) ? ddys_open_scalar($params['q']) : '';
        if ($q === '') {
            return ddys_open_render_empty('请输入关键词以获取搜索建议。');
        }
        return ddys_open_render_shortcode('ddys_suggest', array('q' => $q, 'limit' => isset($params['limit']) ? $params['limit'] : 8));
    }
    if ($view === 'calendar') {
        return ddys_open_render_shortcode('ddys_calendar', array('year' => isset($params['year']) ? $params['year'] : '', 'month' => isset($params['month']) ? $params['month'] : ''));
    }
    if ($view === 'movie') {
        return ddys_open_render_shortcode('ddys_movie', array('slug' => isset($params['slug']) ? $params['slug'] : ''));
    }
    if ($view === 'sources') {
        return ddys_open_render_shortcode('ddys_sources', array('slug' => isset($params['slug']) ? $params['slug'] : ''));
    }
    if ($view === 'related') {
        return ddys_open_render_shortcode('ddys_related', array('slug' => isset($params['slug']) ? $params['slug'] : ''));
    }
    if ($view === 'comments') {
        return ddys_open_render_shortcode('ddys_comments', array('slug' => isset($params['slug']) ? $params['slug'] : '', 'page' => isset($params['page']) ? $params['page'] : 1, 'per_page' => isset($params['per_page']) ? $params['per_page'] : 12));
    }
    if ($view === 'collections') {
        return ddys_open_render_shortcode('ddys_collections', array('page' => isset($params['page']) ? $params['page'] : 1, 'per_page' => isset($params['per_page']) ? $params['per_page'] : 12));
    }
    if ($view === 'collection') {
        return ddys_open_render_shortcode('ddys_collection', array('slug' => isset($params['slug']) ? $params['slug'] : ''));
    }
    if ($view === 'shares') {
        return ddys_open_render_shortcode('ddys_shares', array('page' => isset($params['page']) ? $params['page'] : 1, 'per_page' => isset($params['per_page']) ? $params['per_page'] : 12));
    }
    if ($view === 'share') {
        return ddys_open_render_shortcode('ddys_share', array('id' => isset($params['id']) ? $params['id'] : ''));
    }
    if ($view === 'requests') {
        return ddys_open_render_shortcode('ddys_requests', array('page' => isset($params['page']) ? $params['page'] : 1, 'per_page' => isset($params['per_page']) ? $params['per_page'] : 12));
    }
    if ($view === 'activities') {
        return ddys_open_render_shortcode('ddys_activities', array('page' => isset($params['page']) ? $params['page'] : 1, 'per_page' => isset($params['per_page']) ? $params['per_page'] : 12));
    }
    if ($view === 'user') {
        return ddys_open_render_shortcode('ddys_user', array('username' => isset($params['username']) ? $params['username'] : ''));
    }
    if ($view === 'types') {
        return ddys_open_render_shortcode('ddys_types', array());
    }
    if ($view === 'genres') {
        return ddys_open_render_shortcode('ddys_genres', array());
    }
    if ($view === 'regions') {
        return ddys_open_render_shortcode('ddys_regions', array());
    }
    return ddys_open_render_shortcode('ddys_latest', array('limit' => isset($params['limit']) ? $params['limit'] : 12));
}

function ddys_open_page_tabs($active)
{
    $tabs = array(
        'movies' => '筛选',
        'latest' => '最新',
        'hot' => '热门',
        'search' => '搜索',
        'suggest' => '建议',
        'calendar' => '日历',
        'collections' => '片单',
        'shares' => '分享',
        'requests' => '求片',
        'activities' => '动态',
        'types' => '类型',
        'genres' => '题材',
        'regions' => '地区'
    );
    $html = '<nav class="ddys-phpwind-tabs">';
    foreach ($tabs as $view => $label) {
        $html .= '<a class="' . ($active === $view ? 'active' : '') . '" href="' . ddys_open_attr(ddys_open_page_url($view)) . '">' . ddys_open_h($label) . '</a>';
    }
    $html .= '</nav>';
    return $html;
}

function ddys_open_page_title($view)
{
    $titles = array(
        'movies' => '低端影视影片筛选',
        'latest' => '低端影视最新',
        'hot' => '低端影视热门',
        'search' => '搜索低端影视',
        'suggest' => '低端影视搜索建议',
        'calendar' => '低端影视日历',
        'movie' => '低端影视影片详情',
        'sources' => '低端影视影片资源',
        'related' => '低端影视相关推荐',
        'comments' => '低端影视评论',
        'collections' => '低端影视片单',
        'collection' => '低端影视片单详情',
        'shares' => '低端影视分享',
        'share' => '低端影视分享详情',
        'requests' => '低端影视求片',
        'activities' => '低端影视动态',
        'user' => '低端影视用户',
        'types' => '低端影视类型',
        'genres' => '低端影视题材',
        'regions' => '低端影视地区'
    );
    return isset($titles[$view]) ? $titles[$view] : '低端影视';
}

