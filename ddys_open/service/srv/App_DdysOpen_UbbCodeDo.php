<?php
defined('WEKIT_VERSION') or exit(403);

require_once dirname(dirname(dirname(__FILE__))) . '/source/bootstrap.php';

class App_DdysOpen_UbbCodeDo {
	public function parse($message) {
		ddys_open_bootstrap();
		return ddys_open_parse_shortcodes($message);
	}
}

