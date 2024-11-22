<?php
// First, include necessary files and use the namespace
use Yohns\Core\E;
use Yohns\Core\Err;

include 'vendor/autoload.php';

// Initialize the error handler with custom options
E::initiate([
    'log' => __DIR__ . '/logs',  // Directory where logs will be stored
    'display' => true,           // Whether to display errors in browser
    'store' => [
        '_POST' => true,         // Log POST data
        '_FILES' => true,        // Log uploaded files data
        '_GET' => true,          // Log GET parameters
        '_COOKIE' => false,      // Don't log cookies
        '_SESSION' => true       // Log session data
    ]
]);

// Set up the error and exception handlers
set_error_handler([E::class, 'errHandler']);
set_exception_handler([E::class, 'excHandler']);

// Example 1: Triggering a warning
function divideByZero() {
    $number = 10;
    $result = $number / 0;  // This will trigger a warning
}

// Example 2: Triggering a user error
function validateAge($age) {
    if ($age < 0) {
        trigger_error("Age cannot be negative", E_USER_ERROR);
    }
    return true;
}

// Example 3: Throwing an exception
function processData($data) {
    if (empty($data)) {
        throw new \Err("Data cannot be empty");
    }
    return $data;
}

// Example 4: Undefined variable notice
function useUndefinedVariable() {
    echo $undefinedVar;  // This will trigger a notice
}

// Testing different error scenarios
try {
    // Test 1: Division by zero
    //echo "Testing division by zero:\n";
    //divideByZero();

    // Test 2: Invalid age validation
    //echo "Testing age validation:\n";
    //validateAge(-5);

    // Test 3: Empty data processing
    //echo "Testing empty data processing:\n";
    //processData([]);

    //// Test 4: Undefined variable
    //echo "Testing undefined variable:\n";
    //useUndefinedVariable();
		echo $hi;
} catch (\Err $e) {
    // This will be caught by the exception handler if not caught here
    echo "Caught exception: " . $e->getMessage();
}