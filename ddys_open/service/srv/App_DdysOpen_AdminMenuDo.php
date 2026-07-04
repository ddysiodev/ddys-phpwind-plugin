<?php
defined('WEKIT_VERSION') or exit(403);

class App_DdysOpen_AdminMenuDo {
	public function getAdminMenu($config) {
		$config += array(
			'app_ddys_open' => array('低端影视 API', 'app/manage/*?app=ddys_open', '', '', 'appcenter')
		);
		return $config;
	}
}

