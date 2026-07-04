<?php
defined('WEKIT_VERSION') or exit(403);

Wind::import('APPCENTER:service.srv.iPwInstall');
require_once dirname(dirname(dirname(__FILE__))) . '/source/bootstrap.php';

class App_DdysOpen_InstallDo implements iPwInstall {
	public function install($install) {
		ddys_open_bootstrap();
		ddys_open_ensure_runtime();
		return true;
	}

	public function backUp($install) {
		return true;
	}

	public function revert($install) {
		return true;
	}

	public function unInstall($install) {
		ddys_open_bootstrap();
		ddys_open_cache_clear();
		return true;
	}

	public function rollback($install) {
		ddys_open_bootstrap();
		ddys_open_cache_clear();
		return true;
	}
}

