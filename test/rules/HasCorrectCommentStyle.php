<?php
/**
 * Lithium: the most rad php framework
 *
 * @copyright     Copyright 2011, Union of RAD (http://union-of-rad.org)
 * @license       http://opensource.org/licenses/bsd-license.php The BSD License
 */

namespace li3_quality\test\rules;

use lithium\util\String;

class HasCorrectCommentStyle extends \li3_quality\test\Rule {

	/**
	 * The PHP 5+ comment tokens
	 *
	 * @var array
	 */
	public $inspectableTokens = array(
		T_COMMENT,
		T_DOC_COMMENT,
	);

	/**
	 * The regexes to use on detecting docblocks
	 *
	 * @var array
	 */
	public $patterns = array(
		'PAGE_LEVEL'    => '/{:begin}\/\*\*({:wline} \*( (.*))?)+{:wline} \*\/$/',
		'CLASS_LEVEL'   => '/{:begin}\t\/\*\*({:wline}\t \*( (.*))?)+{:wline}\t \*\/$/',
		'TEST_LEVEL'    => '/\s?\/\/( (.*))?$/',
		'TEST_FUNCTION' => '/^test/',
		'HAS_TAGS'      => '/ \* @/',
		'TAG_FORMAT'    => array(
			'/',
			'{:begin}\t?\/\*\*',
			'(({:wlinet} \*( [^@].*)?)+)',
			'{:wlinet} \*',
			'(({:wlinet} \* @(.*)))',
			'(({:wlinet} \* (@|[ ]{5})(.*))+)?',
			'{:wlinet} \*\/',
			'/',
		),
	);

	/**
	 * Patterns to replace inside the regex to make them shorter and easier to read
	 *
	 * @var array
	 */
	public $regexInject = array(
		'begin' => '(^|\r\n|\r|\n)',
		'wline' => '(\r\n|\r|\n)',
		'wlinet' => '(\r\n|\r|\n)\t?',
	);

	/**
	 * Will iterate tokens looking for comments and if found will determine the regex
	 * to test the comment against.
	 *
	 * @param  Testable $testable The testable object
	 * @return void
	 */
	public function apply($testable) {
		$tokens = $testable->tokens();
		foreach ($tokens as $tokenId => $token) {
			if (in_array($token['id'], $this->inspectableTokens)) {
				$inClass = $testable->tokenIn(array(T_CLASS), $tokenId);
				$inFunction = $testable->tokenIn(array(T_FUNCTION), $tokenId);
				$content = null;
				if ($inClass && $inFunction) {
					$function = $testable->findPrev(array(T_FUNCTION), $tokenId);
					$functionNameId = $testable->findNext(array(T_STRING), $function);
					$pattern = $this->compilePattern('TEST_FUNCTION');
					if (preg_match($pattern, $tokens[$functionNameId]['content']) === 0) {
						$this->addViolation(array(
							'message' => 'Comments should not appear in methods.',
							'line' => $token['line'],
						));
					}
					$match = 'TEST_LEVEL';
				} elseif ($inClass XOR $inFunction) {
					$match = 'CLASS_LEVEL';
				} elseif (!$inClass && !$inFunction) {
					$match = 'PAGE_LEVEL';
				}
				if (isset($tokens[$tokenId - 1]) && $tokens[$tokenId - 1]['id'] === T_WHITESPACE) {
					$content .= $tokens[$tokenId - 1]['content'];
				}
				$content .= $token['content'];
				$pattern = $this->compilePattern($match);
				if (preg_match($pattern, $content) === 0) {
					$this->addViolation(array(
						'message' => 'Docblocks are in the incorrect format.',
						'line' => $token['line'],
					));
				} else {
					$hasTagsPattern = $this->compilePattern('HAS_TAGS');
					if (preg_match($hasTagsPattern, $content) === 1) {
						$tagsPattern = $this->compilePattern('TAG_FORMAT');
						if (preg_match($tagsPattern, $content) === 0) {
							$this->addViolation(array(
								'pattern' => $tagsPattern,
								'content' => $content,
								'message' => 'Tags should be last and have a blank docblock line.',
								'line' => $token['line'],
							));
						}
					}
				}
			}
		}
	}

	/**
	 * A helper method to help compile patterns
	 *
	 * @param  string $key
	 * @return string
	 */
	public function compilePattern($key) {
		$items = $this->patterns[$key];
		if (is_array($items)) {
			$items = implode(null, $items);
		}
		return String::insert($items, $this->regexInject);
	}

}

?>