<?php
/**
 * Lithium: the most rad php framework
 *
 * @copyright     Copyright 2011, Union of RAD (http://union-of-rad.org)
 * @license       http://opensource.org/licenses/bsd-license.php The BSD License
 */
namespace li3_quality\test;

use SplFileInfo;

abstract class Rule extends \lithium\core\Object {

	/**
	 * Contains the current violations.
	 */
	protected $_violations = array();

	/**
	 * This method will need to addViolations if one is found
	 *
	 * @param   object $testable The testable object
	 * @return  void
	 */
	abstract public function apply($testable);

	/**
	 * Will determine if `apply()` had any violations
	 *
	 * @return  boolean
	 */
	public function success() {
		return empty($this->_violations);
	}

	/**
	 * Will add violations in the correct way
	 *
	 * @param   array $violation The violation should include message and line keys
	 * @return  void
	 */
	public function addViolation($violation = array()) {
		$this->_violations[] = $violation;
	}

	/**
	 * Will return a list of current violations
	 *
	 * @return  array
	 */
	public function violations() {
		return $this->_violations;
	}

	/**
	 * Will reset the current list of violations
	 *
	 * @return  void
	 */
	public function reset() {
		$this->_violations = array();
	}

	/**
	 * A switch to check if this rule should be applied to the current tests or not
	 */
	public function enabled() {
		return true;
	}

	/**
	 * A helper method which helps finding tokens. If there are no tokens
	 * on this line, we go backwards assuming a multiline token.
	 *
	 * @param  int    $line   The line you are on
	 * @param  array  $tokens The tokens to iterate
	 * @return int            The token id if found, -1 if not
	 */
	protected function _findTokenByLine($line, $tokens) {
		foreach ($tokens as $id => $token) {
			if ($token['line'] === $line) {
				return $id;
			}
		}
		return $line === 0 ? -1 : $this->_findTokenByLine($line - 1, $tokens);
	}

	/**
	 * Will find the next token
	 *
	 * @param  array           $tokens The array of tokens
	 * @param  array           $types  The types you wish to find (T_VARIABLE, T_FUNCTION, ...)
	 * @param  integer         $start  Where you want to start
	 * @return integer|boolean         The index of the next $type or false if nothing is found
	 */
	public function findNext($tokens, array $types, $start = 0) {
		$total = count($tokens);
		for ($id = $start; $id < $total;$id++) {
			if (isset($tokens[$id]) && in_array($tokens[$id]['id'], $types)) {
				return $id;
			}
		}
		return false;
	}

	/**
	 * Will find the previous token
	 *
	 * @param  array           $tokens The array of tokens
	 * @param  array           $types  The types you wish to find (T_VARIABLE, T_FUNCTION, ...)
	 * @param  integer         $start  Where you want to start
	 * @return integer|boolean         The index of the next $type or false if nothing is found
	 */
	public function findPrev($tokens, array $types, $start = 0) {
		for ($id = $start; $id >= 0;$id--) {
			if (isset($tokens[$id]) && in_array($tokens[$id]['id'], $types)) {
				return $id;
			}
		}
		return false;
	}

	/**
	 * Will detect if the given token is inside of a specific token
	 *
	 * @param  array  $tokens    The array of tokens
	 * @param  array  $findToken The tokens you are looking for
	 * @param  int    $start     The token index that could be inside $findToken
	 * @return bool
	 */
	public function tokenIn($tokens, $findToken, $start) {
		$openBrackets = 0;
		$prevToken = $this->findPrev($tokens, $findToken, $start);
		if ($prevToken !== false) {
			for ($id = $prevToken; $id < $start;$id++) {
				if ($tokens[$id]['content'] === '{') {
					$openBrackets++;
				} elseif ($tokens[$id]['content'] === '}') {
					$openBrackets--;
				}
			}
		}
		return $openBrackets >= 1;
	}

	/**
	 * Will return a new SplFileInfo Object to play with
	 *
	 * @param  string $path The path to the file
	 * @return object
	 */
	public function fileInfo($path) {
		return new SplFileInfo($path);
	}

}

?>