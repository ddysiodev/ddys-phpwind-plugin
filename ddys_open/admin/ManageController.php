<?php
defined('WEKIT_VERSION') or exit(403);

Wind::import('ADMIN:library.AdminBaseController');
require_once dirname(dirname(__FILE__)) . '/source/bootstrap.php';

class ManageController extends AdminBaseController {

	public function beforeAction($handlerAdapter) {
		parent::beforeAction($handlerAdapter);
		ddys_open_bootstrap();
	}

	public function run() {
		$this->_assignPageData();
	}

	public function doRunAction() {
		if (!ddys_open_verify_nonce(ddys_open_post('ddys_admin_nonce'), 'admin')) {
			$this->showError('DDYS 管理令牌无效，请刷新后重试。');
		}
		$settings = $this->_settingsFromPost();
		if (!ddys_open_save_settings($settings)) {
			$this->showError('DDYS 设置保存失败，请检查扩展目录写入权限。');
		}
		$this->showMessage('设置已保存。', 'app/manage/run?app=' . DDYS_OPEN_PHPWIND_ID);
	}

	public function testAction() {
		if (!ddys_open_verify_nonce(ddys_open_get('nonce'), 'admin')) {
			$this->showError('DDYS 管理令牌无效，请刷新后重试。');
		}
		$result = ddys_open_api_get('/latest', array('limit' => 1), array('no_cache' => true));
		if (ddys_open_is_error($result)) {
			$this->showError($result['message'], 'app/manage/run?app=' . DDYS_OPEN_PHPWIND_ID);
		}
		$this->showMessage('DDYS API 连接正常。', 'app/manage/run?app=' . DDYS_OPEN_PHPWIND_ID);
	}

	public function clearAction() {
		if (!ddys_open_verify_nonce(ddys_open_get('nonce'), 'admin')) {
			$this->showError('DDYS 管理令牌无效，请刷新后重试。');
		}
		$count = ddys_open_cache_clear();
		$this->showMessage('已清理 DDYS 缓存文件：' . $count, 'app/manage/run?app=' . DDYS_OPEN_PHPWIND_ID);
	}

	private function _assignPageData() {
		$settings = ddys_open_settings();
		$stats = ddys_open_cache_stats();
		$nonce = ddys_open_nonce('admin');
		$this->setOutput($settings, 'settings');
		$this->setOutput($stats, 'cacheStats');
		$this->setOutput($this->_configChecks($settings, $stats), 'configChecks');
		$this->setOutput(ddys_open_shortcodes(), 'shortcodes');
		$this->setOutput(ddys_open_admin_assets(), 'ddysAdminAssets');
		$this->setOutput(ddys_open_route_url('manage', 'doRun', array(), true), 'saveUrl');
		$this->setOutput(ddys_open_route_url('manage', 'test', array('nonce' => $nonce), true), 'testUrl');
		$this->setOutput(ddys_open_route_url('manage', 'clear', array('nonce' => $nonce), true), 'clearUrl');
		$this->setOutput(ddys_open_page_url('latest'), 'pageUrl');
		$this->setOutput(ddys_open_endpoint_url('api'), 'apiUrl');
		$this->setOutput($nonce, 'adminNonce');
	}

	private function _settingsFromPost() {
		$keys = array(
			'api_base_url', 'site_base_url', 'api_key', 'timeout',
			'default_cache_ttl', 'dictionary_cache_ttl', 'fresh_cache_ttl',
			'list_cache_ttl', 'detail_cache_ttl', 'community_cache_ttl',
			'theme', 'layout', 'columns', 'target', 'default_limit',
			'request_interval'
		);
		$settings = array();
		foreach ($keys as $key) {
			$settings[$key] = ddys_open_post($key);
		}
		foreach (array('show_source_link', 'enable_styles', 'enable_request_form', 'show_nav', 'debug') as $key) {
			$settings[$key] = ddys_open_post($key) === '1' ? 1 : 0;
		}
		return $settings;
	}

	private function _configChecks($settings, $stats) {
		$checks = array();
		$checks[] = array('ok' => preg_match('#^https?://#i', $settings['api_base_url']), 'label' => 'API Base URL');
		$checks[] = array('ok' => preg_match('#^https?://#i', $settings['site_base_url']), 'label' => '源站 URL');
		$checks[] = array('ok' => !empty($settings['api_key']) || empty($settings['enable_request_form']), 'label' => '求片 API Key');
		$checks[] = array('ok' => !empty($stats['writable']), 'label' => '缓存目录可写');
		$checks[] = array('ok' => function_exists('curl_init') || ini_get('allow_url_fopen'), 'label' => 'HTTP 请求能力');
		$checks[] = array('ok' => function_exists('json_decode'), 'label' => 'JSON 扩展');
		return $checks;
	}
}

