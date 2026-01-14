<?php

namespace Yohns\Core;

/**
 * Class Str
 *
 * String manipulation utilities.
 */
class Str {

	/**
	 * Clean a URL string.
	 *
	 * @param string $str The string to clean.
	 * @return string|null The cleaned URL.
	 */
	public static function clean_url(string $str): string|null {
		// Convert to lowercase
		$str = strtolower($str);
		// Trim whitespace
		$str = trim($str);
		// Replace spaces with hyphens and remove invalid characters
		$str = preg_replace('/[^a-z0-9]+/', '-', $str);
		// Trim hyphens from the beginning and end
		$str = trim($str, '-');
		return $str;
	}

}