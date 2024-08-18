# Yohns\Core\E

```php
<?php
use Yohns\Core\E;
E::initiate();
//self::$class = new \stdClass();
set_error_handler('Yohns\E::errHandler');
set_exception_handler('Yohns\E::excHandler');
```