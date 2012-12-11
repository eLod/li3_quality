<?php

namespace li3_quality\tests\cases\test\rules;

class HasCorrectPermissionsTest extends \li3_quality\test\Unit {

	public $rule = 'li3_quality\tests\mocks\test\rules\HasCorrectPermissions';

	public function testExecuteable() {
		$this->assertRuleFail(array(
			'source' => null,
			'path' => 'executeableFile.php',
			'executeable' => true,
		), $this->rule);
	}

	public function testNonExecuteable() {
		$this->assertRulePass(array(
			'source' => null,
			'path' => 'nonExecuteableFile.php',
			'executeable' => false,
		), $this->rule);
	}

}

?>