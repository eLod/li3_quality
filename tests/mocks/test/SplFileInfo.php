<?php

namespace li3_quality\tests\mocks\test;

class SplFileInfo extends \SplFileInfo {

	protected $_config = array();

	public function __construct($path, $options) {
		parent::__construct($path);
		$this->_config = $options + array(
			'executeable' => true,
		);
	}

	public function isExecutable() {
		return $this->config('executeable');
	}

	public function config($name = null) {
		if (is_null($name)) {
			return $this->_config;
		} elseif (isset($this->_config[$name])) {
			return $this->_config[$name];
		}
		return;
	}

}

?>