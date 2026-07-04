<?php
defined('WEKIT_VERSION') or exit(403);

require_once dirname(dirname(__FILE__)) . '/source/bootstrap.php';

class IndexController extends PwBaseController {

	public function beforeAction($handlerAdapter) {
		parent::beforeAction($handlerAdapter);
		ddys_open_bootstrap();
	}

	public function run() {
		$view = ddys_open_get('view', 'latest');
		$params = array(
			'limit' => ddys_open_get('limit'),
			'page' => ddys_open_get('page'),
			'q' => ddys_open_get('q'),
			'type' => ddys_open_get('type'),
			'year' => ddys_open_get('year'),
			'month' => ddys_open_get('month'),
			'slug' => ddys_open_get('slug')
		);
		$assets = ddys_open_frontend_assets();
		$content = ddys_open_page_tabs($view) . ddys_open_render_page($view, $params);
		$this->setOutput(ddys_open_page_title($view), 'ddysTitle');
		$this->setOutput($assets, 'ddysAssets');
		$this->setOutput($content, 'ddysContent');
	}

	public function apiAction() {
		ddys_open_json_response(ddys_open_proxy_response());
	}

	public function requestAction() {
		ddys_open_json_response(ddys_open_handle_request_form());
	}
}

