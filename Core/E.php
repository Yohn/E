<?php

namespace Yohns\Core;

use JW3B\Helpful\Str;

/**
 * Class E
 *
 * Handles error management, logging, and display.
 */
class E {
	/**
	 * Indicates whether the error display has been opened.
	 *
	 * @var bool
	 */
	protected static bool $opened = false;

	/**
	 * Options for error handling and logging.
	 *
	 * @var array
	 */
	protected static array $opts = [];

	/**
	 * Map of error constants to their string representations.
	 *
	 * @var array
	 */
	protected static array $exceptions = [
		E_ERROR							=> 'E_ERROR',							// 1			Fatal
		E_WARNING						=> 'E_WARNING',						// 2			Run-time
		E_PARSE							=> 'E_PARSE',							// 4			Run-time
		E_NOTICE						=> 'E_NOTICE',						// 8			Run-time
		E_CORE_ERROR				=> 'E_CORE_ERROR',				// 16			Fatal
		E_CORE_WARNING			=> 'E_CORE_WARNING',			// 32			Warning
		E_COMPILE_ERROR			=> 'E_COMPILE_ERROR',			// 64			Fatal
		E_COMPILE_WARNING		=> 'E_COMPILE_WARNING',		// 128		Compile-time
		E_USER_ERROR				=> 'E_USER_ERROR',				// 256		User-generated
		E_USER_WARNING			=> 'E_USER_WARNING',			// 512		User-generated
		E_USER_NOTICE				=> 'E_USER_NOTICE',				// 1024		User-generated
		E_STRICT						=> 'E_STRICT',						// 2048		PHP tells us how to code
		E_RECOVERABLE_ERROR	=> 'E_RECOVERABLE_ERROR',	// 4096		Catchable
		E_DEPRECATED				=> 'E_DEPRECATED',				// 8192		Run-time
		E_USER_DEPRECATED		=> 'E_USER_DEPRECATED',		// 16384	User-generated
		E_ALL								=> 'E_ALL' 								// 32767	All errors
	];

	/**
	 * Constructor is private to prevent instantiation.
	 */
	private function __construct() {}

	/**
	 * Initiate error handling with custom options.
	 *
	 * @param array $over Custom options for error handling.
	 *
	 * Example of usage:
	 * ```php
	 * $opts = [
	 *		 'log' => '/path/to/log',
	 *		 'store' => ['_POST', '_FILES', '_GET', '_COOKIE', '_SESSION'],
	 *		 'display' => true
	 * ];
	 * ```
	 */
	public static function initiate(array $over = []): void {
		self::$opts = [
			'dir'			=> $over['log'] ?? __DIR__.'/../logs',
			//? removed cause we didnt use it anyways. It was to set the error log file, but removed long ago.
			// 'file'		=> $over['file'] ?? false,
			'display'	=> $over['display'] ?? true,
			'store'		=> [
				'_POST'			=> $over['store']['_POST'] ?? true,
				'_FILES'		=> $over['store']['_FILES'] ?? true,
				'_GET'			=> $over['store']['_GET'] ?? true,
				'_COOKIE'		=> $over['store']['_COOKIE'] ?? false,
				'_SESSION'	=> $over['store']['_SESSION'] ?? false,
			],
		];
		//ini_set('error_log', self::$opts['file']);
	}

	/**
	 * Error handler function to process errors.
	 *
	 * @param int $errno	 Error number.
	 * @param string $errstr Error message.
	 * @param string $errfile File where the error occurred.
	 * @param int $errline Line number where the error occurred.
	 * @return bool Always returns true to indicate the error has been handled.
	 */
	public static function errHandler(int $errno, string $errstr, string $errfile, int $errline): bool {
		$mixed = self::mapErrorCode($errno);
		$message = $mixed['type'] . ': ' . $errstr;

		$log = 'Yohns Error: ' . PHP_EOL
			. $message . PHP_EOL
			. 'File: ' . self::displayFilePath($errfile) . PHP_EOL
			. 'Line #:' . $errline;
		self::log($log, $mixed['type']);
		if (self::$opts['display']) {
			self::display($log, $mixed['type']);
		}
		return true;
	}

	/**
	 * Exception handler to process uncaught exceptions.
	 *
	 * @param \Throwable $exception The exception that was thrown.
	 * @return bool Always returns false to indicate the exception has been handled.
	 */
	public static function excHandler(\Throwable $exception): bool {
		$log = 'Yohns Fatal Error: ' . PHP_EOL
			. $exception->getMessage() . PHP_EOL
			. 'File: ' . self::displayFilePath($exception->getFile()) . PHP_EOL
			. 'Line: ' . $exception->getLine();
		self::log($log, 'Fatal');
		self::display($log, 'Fatal');
		return false;
	}

	/**
	 * Display an error message in the browser.
	 *
	 * @param string $msg	The error message to display.
	 * @param string $type The type of error.
	 * @return void
	 */
	public static function display(string $msg, string $type): void {
		// Check if a JSON header is set
		$headers = headers_list();
		$isJsonHeaderSet = false;
		foreach ($headers as $header) {
			if (stripos($header, 'Content-Type: application/json') !== false) {
				$isJsonHeaderSet = true;
				break;
			}
		}
		if ($isJsonHeaderSet) {
			// Output JSON response
			echo json_encode([
				'status' => 'error',
				'type' => $type,
				'message' => htmlspecialchars($msg),
			], JSON_PRETTY_PRINT);
		} else {
			// Output HTML response
			if ($type == 'Fatal' && !self::$opened) {
				echo '<html data-bs-theme="dark"><head><link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous"></head><body>';
				self::$opened = true;
			}
			echo '<pre class="alert alert-danger m-5 w-75 mx-auto">' . htmlspecialchars($msg) . '</pre>';
		}
	}

	/**
	 * Display a file path relative to the document root.
	 *
	 * @param string $path The full file path.
	 * @return string The relative file path.
	 */
	public static function displayFilePath(string $path): string {
		return str_replace([$_SERVER['DOCUMENT_ROOT'], __DIR__], '', $path);
	}

	/**
	 * Log the error details to a file.
	 *
	 * @param string $notes The notes or message to log.
	 * @param string $type	The type of log (e.g., 'Fatal', 'Warning').
	 * @return bool Returns true if the log was successful.
	 */
	public static function log(string $notes, string $type = 'basic'): bool {
		$parts = explode('|', $type);
		$name = count($parts) > 1 ? array_shift($parts) : $type;
		$name .= '-' . date('Y-m-d') . '-at-' . date('g-i-sA');
		$dir = self::$opts['dir'] . '/' . $type . '/';
		if (!is_dir($dir)) mkdir($dir, 0777, true);
		$file = $dir . Str::clean_url($name) . '.log';

		$logContent = self::prepareLogContent($notes);

		$fp = fopen($file, 'a');
		if ($fp) {
			flock($fp, LOCK_EX);
			fwrite($fp, $logContent);
			flock($fp, LOCK_UN);
			fclose($fp);
			return true;
		}
		return false;
	}

	/**
	 * Prepare the content to be logged, including debug information.
	 *
	 * @param string $notes The primary message to log.
	 * @return string The complete log content.
	 */
	private static function prepareLogContent(string $notes): string {
		$debug = print_r(debug_backtrace(), true);
		$lastError = print_r(error_get_last(), true);
		$ref = $_SERVER['HTTP_REFERER'] ?? 'Direct input';
		$gg = $_SERVER['QUERY_STRING'] ?? '';
		$rs = $_SERVER['REDIRECT_STATUS'] ?? '';
		$ip = $_SERVER['REMOTE_ADDR'] ?? '';

		$P = self::$opts['store']['_POST'] ? '_POST = ' . print_r($_POST, 1) : '_POST off';
		$F = self::$opts['store']['_FILES'] ? '_FILES = ' . print_r($_FILES, 1) : '_FILES off';
		$G = self::$opts['store']['_GET'] ? '_GET = ' . print_r($_GET, 1) : '_GET off';
		$C = self::$opts['store']['_COOKIE'] ? '_COOKIE = ' . print_r($_COOKIE, 1) : '_COOKIE off';
		$S = isset($_SESSION) && self::$opts['store']['_SESSION']
			? '_SESSION = ' . print_r($_SESSION, 1)
			: (isset($_SESSION) ? '_SESSION off = User ID = ' . ($_SESSION['Uid'] ?? 'not signed in') . ' Uname = ' . ($_SESSION['Uname'] ?? 'null') : '_SESSION not available');

		return "Yohns Error Report - " . date('l jS F Y h:i:s A') . PHP_EOL
			. $notes . PHP_EOL
			. PHP_EOL
			. "========== ENVIRONMENT ==========" . PHP_EOL
			. 'Referer = ' . $ref . PHP_EOL
			. 'IP = ' . $ip . PHP_EOL
			. 'QueryString = ' . $gg . PHP_EOL
			. 'RedirectStatus = ' . $rs . PHP_EOL
			. "=========== DEBUG INFO ==========" . PHP_EOL
			. $debug . PHP_EOL
			. "============ LAST ERROR ==========" . PHP_EOL
			. $lastError . PHP_EOL
			. PHP_EOL
			. "=========== GLOBALS ==========" . PHP_EOL
			. $P . PHP_EOL
			. $F . PHP_EOL
			. $G . PHP_EOL
			. $C . PHP_EOL
			. $S . PHP_EOL;
	}

	/**
	 * Map an error code to its string representation.
	 *
	 * @param int $errno The error code.
	 * @return array An array containing the type and exception name.
	 */
	private static function mapErrorCode(int $errno): array {
		$type = self::$exceptions[$errno] ?? 'Unknown';
		return ['type' => $type];
	}
}
