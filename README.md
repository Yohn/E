# Error Handling
An internal error handler and error logger.

## Yohns\Core\E


```php
include 'vendor/autoload.php';

use Yohns\Core\E;

E::initiate([
	// the directory to store the error logs in.
	'dir' => __DIR__.'/logs',
	// default to display the errors
	'display' => true,
	// what variables to store
	'store' => [
		// if any $_POST variable is set
		'_POST',
		// if any $_FILES variable is set
		'_FILES',
		// if any $_GET variable is set
		'_GET',
		// if any $_COOKIE variable is set, default this is not saved
		//'_COOKIE',
		// if any $_SESSION variable is set, default this is not saved
		//'_SESSION'
	],
]);

set_error_handler('Yohns\Core\E::errHandler');
set_exception_handler('Yohns\Core\E::excHandler');
```

## Yohns\Core\Err
```php
include 'vendor/autoload.php';

use Yohns\Core\Err;

try {
	throw new Err("This is a custom error message.");
} catch (Err $e) {
	echo $e->getDetailedMessage();
}
```