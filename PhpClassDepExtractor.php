<?php

/**
 * PhpClassDepExtractor -- library for extracting class hierarchy data from PHP files.
 * (c) 2013 Stefaan Lippens
 *
 * See LICENCE file for the full copyright and license information.
 */

// TODO: option to stop parsing a file on first hit

class PhpClassDepExtractionException extends Exception {}

/**
 * PhpClassDepExtractor functionality implemented as static functions
 * in a class (poor man's namespace pattern).
 */
class PhpClassDepExtractor {

	/**
	 * Increment index in token array as long as it points
	 * to whitespace or comments. If index reaches end of token
	 * array, null is returned.
	 */
	private static function _skipWhiteSpaceAndComments($tokens, $i)
	{
		$size = count($tokens);
		while (
			$i < $size
			&& is_array($tokens[$i])
			&& ($tokens[$i][0] === T_WHITESPACE
				|| $tokens[$i][0] === T_COMMENT || $tokens[$i][0] === T_DOC_COMMENT)
			) {
			$i++;
		}

		return $i;
	}

	/**
	 * Extract classes/interfaces and their dependencies from a PHP source code
	 * @param string $sourceCode
	 */
	public static function extractFromSourceCode($sourceCode) {
		$classes = array();

		// Parse source code in tokens.
		$tokens = token_get_all($sourceCode);

		// Loop over the tokens and detect class definitions
		$size = count($tokens);
		for ($i = 0; $i < $size; $i++) {

			// Search for 'class' or 'interface' keyword
			if (is_array($tokens[$i]) && ($tokens[$i][0] === T_CLASS || $tokens[$i][0] === T_INTERFACE))
			{
				$type = $tokens[$i][0];

				// After 'class' keyword, skip whitespace and comments.
				$i = self::_skipWhiteSpaceAndComments($tokens, $i + 1);

				// Now we should have the class name, a T_STRING
				if ($i < $size && is_array($tokens[$i]) && $tokens[$i][0] == T_STRING) {
					$name = $tokens[$i][1];
					$classes[$name] = array();
				}
				else {
					throw new PhpClassDepExtractionException('Failed to detect class/interface name after "class"/"interface" keyword.');
				}

				// Skip whitespace and comments.
				$i = self::_skipWhiteSpaceAndComments($tokens, $i + 1);

				// Handle 'extends' (note that multiple "parents" are possible in case of interfaces).
				if ($i < $size && is_array($tokens[$i]) && $tokens[$i][0] == T_EXTENDS) {
					do {
						$i = self::_skipWhiteSpaceAndComments($tokens, $i + 1);
						// Get extended class/interface and store as dependency.
						if ($i < $size && is_array($tokens[$i]) && $tokens[$i][0] == T_STRING) {
							$classes[$name][] = $tokens[$i][1];
						}
						else {
							throw new PhpClassDepExtractionException('Failed to detect class/interface name after "extends" keyword.');
						}
						$i = self::_skipWhiteSpaceAndComments($tokens, $i + 1);
					} while ($i < $size && $tokens[$i] === ',');
				}

				// Handle 'implements'.
				if ($i < $size && is_array($tokens[$i]) && $tokens[$i][0] == T_IMPLEMENTS) {
					do {
						$i = self::_skipWhiteSpaceAndComments($tokens, $i + 1);
						// Get implemented interface and store as dependency.
						if ($i < $size && is_array($tokens[$i]) && $tokens[$i][0] == T_STRING) {
							$classes[$name][] = $tokens[$i][1];
						}
						else {
							throw new PhpClassDepExtractionException('Failed to detect interface name after "implements" keyword.');
						}
						$i = self::_skipWhiteSpaceAndComments($tokens, $i + 1);
					} while ($i < $size && $tokens[$i] === ',');
				}

				// At this point the class/interface body should start.
				if ($i < $size && $tokens[$i] !== '{')
				{
					throw new PhpClassDepExtractionException('Failed to detect start of class/interface body.');
				}
			}
		}

		return $classes;
	}
}

