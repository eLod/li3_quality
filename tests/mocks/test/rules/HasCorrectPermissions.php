<?php
/**
 * Lithium: the most rad php framework
 *
 * @copyright     Copyright 2011, Union of RAD (http://union-of-rad.org)
 * @license       http://opensource.org/licenses/bsd-license.php The BSD License
 */

namespace li3_quality\tests\mocks\test\rules;

use li3_quality\tests\mocks\test\SplFileInfo;

class HasCorrectPermissions extends \li3_quality\test\rules\HasCorrectPermissions {

	protected $_config = array();

	public function __construct($options) {
		$this->_config = $options;
	}

	public function fileInfo($path) {
		return new SplFileInfo($path, $this->_config);
	}

}

?>