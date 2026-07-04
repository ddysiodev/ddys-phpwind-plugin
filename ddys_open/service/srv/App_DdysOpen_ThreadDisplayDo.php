<?php
defined('WEKIT_VERSION') or exit(403);

Wind::import('SRV:forum.srv.threadDisplay.do.PwThreadDisplayDoBase');
require_once dirname(dirname(dirname(__FILE__))) . '/source/bootstrap.php';

class App_DdysOpen_ThreadDisplayDo extends PwThreadDisplayDoBase {
	public function bulidRead($read) {
		if (isset($read['content']) && strpos($read['content'], '[ddys_') !== false) {
			ddys_open_bootstrap();
			$read['content'] = ddys_open_parse_shortcodes($read['content']);
		}
		return $read;
	}
}

