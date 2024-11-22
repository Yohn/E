<?php

namespace Yohns\Core;

use Exception;
/**
 * Custom error handling class that extends PHP's built-in RuntimeException.
 * This class is designed to represent an error that can be thrown
 * during the execution of the program.
 *
 * ```php
 * try {
 * 	throw new Err("This is a custom error message.");
 * } catch (Err $e) {
 * 	echo $e->getDetailedMessage();
 * }
 * ```
 */
final class Err extends Exception
{
	/**
	 * Constructs a new Err exception.
	 *
	 * @param string $message [optional] The error message that should be logged or displayed.
	 * @param int $code [optional] The error code; default is 0 which denotes no specific error code.
	 * @param Throwable|null $previous [optional] The previous throwable used for the exception chaining.
	 */
	public function __construct(string $message = "", int $code = 0, Throwable $previous = null)
	{
		// Ensure all previous parameters are properly passed to the base Exception class
		parent::__construct($message, $code, $previous);
	}

	/**
	 * Get a detailed error message that includes the file name,
	 * line number, and the error message itself.
	 *
	 * @return string A formatted string containing the error details.
	 */
	public function getDetailedMessage(): string
	{
		return sprintf(
			"Error in file %s on line %d: %s",
			$this->getFile(),
			$this->getLine(),
			$this->getMessage()
		);
	}
}