<?php
defined('WEKIT_VERSION') or exit(403);

class App_DdysOpen_ThreadDisplayInjector extends PwBaseHookInjector {
	public function run() {
		Wind::import('EXT:ddys_open.service.srv.App_DdysOpen_ThreadDisplayDo');
		return new App_DdysOpen_ThreadDisplayDo();
	}
}

