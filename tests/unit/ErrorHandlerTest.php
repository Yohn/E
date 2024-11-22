<?php

use Yohns\Core\E;

beforeEach(function () {
	// Setup test environment
	if (!defined('E_ALL')) {
		define('E_ALL', 32767);
	}

	// Create temporary log directory with unique name
	$this->tempDir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'error_handler_tests_' . uniqid();
	if (!is_dir($this->tempDir)) {
		mkdir($this->tempDir, 0777, true);
	}

	// Initialize error handler with test configuration
	E::initiate([
		'log' => $this->tempDir,
		'display' => false, // Disable display for testing
		'store' => [
			'_POST' => true,
			'_FILES' => true,
			'_GET' => true,
			'_COOKIE' => false,
			'_SESSION' => false,
		]
	]);
});

afterEach(function () {
	// Improved cleanup function
	if (isset($this->tempDir) && is_dir($this->tempDir)) {
		$files = new RecursiveIteratorIterator(
			new RecursiveDirectoryIterator($this->tempDir, RecursiveDirectoryIterator::SKIP_DOTS),
			RecursiveIteratorIterator::CHILD_FIRST
		);

		foreach ($files as $fileinfo) {
			if ($fileinfo->isDir()) {
				rmdir($fileinfo->getRealPath());
			} else {
				unlink($fileinfo->getRealPath());
			}
		}
		rmdir($this->tempDir);
	}
});

test('error handler creates log file for warnings', function () {
	ob_start();
	E::errHandler(E_WARNING, 'Test warning message', __FILE__, __LINE__);
	ob_end_clean();

	$logFiles = glob($this->tempDir . DIRECTORY_SEPARATOR . 'E_WARNING' . DIRECTORY_SEPARATOR . '*.log');
	expect($logFiles)->toHaveCount(1);

	$logContent = file_get_contents($logFiles[0]);
	expect($logContent)
		->toContain('Test warning message')
		->toContain('E_WARNING')
		->toContain('File:');
});

test('error handler creates log file for fatal errors', function () {
	ob_start();
	E::errHandler(E_ERROR, 'Test fatal error message', __FILE__, __LINE__);
	ob_end_clean();

	$logFiles = glob($this->tempDir . DIRECTORY_SEPARATOR . 'E_ERROR' . DIRECTORY_SEPARATOR . '*.log');
	expect($logFiles)->toHaveCount(1);

	$logContent = file_get_contents($logFiles[0]);
	expect($logContent)
		->toContain('Test fatal error message')
		->toContain('E_ERROR');
});

test('exception handler creates log file', function () {
	ob_start();
	$exception = new Exception('Test exception message');
	E::excHandler($exception);
	ob_end_clean();

	$logFiles = glob($this->tempDir . DIRECTORY_SEPARATOR . 'Fatal' . DIRECTORY_SEPARATOR . '*.log');
	expect($logFiles)->toHaveCount(1);

	$logContent = file_get_contents($logFiles[0]);
	expect($logContent)
		->toContain('Test exception message')
		->toContain('Fatal');
});

test('display method formats output as JSON when appropriate headers are set', function () {
	// Mock headers_list function
	if (!function_exists('headers_list')) {
		function headers_list() {
			return ['Content-Type: application/json'];
		}
	}

	ob_start();
	E::display('Test error message', 'Warning');
	$output = ob_get_clean();

	$jsonOutput = json_decode($output, true);
	expect($jsonOutput)
		->toBeArray()
		->and($jsonOutput['status'])->toBe('error')
		->and($jsonOutput['type'])->toBe('Warning')
		->and($jsonOutput['message'])->toContain('Test error message');
});

test('display method formats output as HTML when no JSON header is set', function () {
	// Mock headers_list function
	if (!function_exists('headers_list')) {
		function headers_list() {
			return [];
		}
	}

	ob_start();
	E::display('Test error message', 'Warning');
	$output = ob_end_clean();

	expect($output)
		->toContain('alert-danger')
		->toContain('Test error message');
});

test('log method creates directory structure if it doesn\'t exist', function () {
	$newLogDir = $this->tempDir . DIRECTORY_SEPARATOR . 'nested' . DIRECTORY_SEPARATOR . 'test_logs';
	E::initiate(['log' => $newLogDir]);

	E::log('Test log message', 'TestType');

	$typeDir = $newLogDir . DIRECTORY_SEPARATOR . 'TestType';
	expect(is_dir($typeDir))->toBeTrue();

	// Clean up the additional test directory
	$files = glob($typeDir . DIRECTORY_SEPARATOR . '*.log');
	foreach ($files as $file) {
		unlink($file);
	}
	rmdir($typeDir);
	rmdir($newLogDir . DIRECTORY_SEPARATOR . 'nested');
	rmdir($newLogDir);
});

test('displayFilePath removes document root from path', function () {
	$_SERVER['DOCUMENT_ROOT'] = DIRECTORY_SEPARATOR . 'var' . DIRECTORY_SEPARATOR . 'www' . DIRECTORY_SEPARATOR . 'html';
	$fullPath = DIRECTORY_SEPARATOR . 'var' . DIRECTORY_SEPARATOR . 'www' . DIRECTORY_SEPARATOR . 'html' . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'file.php';

	$result = E::displayFilePath($fullPath);

	expect($result)->toBe(DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'file.php');
});