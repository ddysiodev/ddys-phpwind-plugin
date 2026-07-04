<?php
defined('WEKIT_VERSION') or exit(403);

class App_DdysOpen_EditorAppDo {
	public function getEditorApp($var) {
		$var[] = array(
			'name' => 'ddys_open',
			'params' => array('shortcode' => 'ddys_latest', 'limit' => 12)
		);
		return $var;
	}
}

