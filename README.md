# Simple PHP data filter
# Usage
```php
<?php
require 'vendor/autoload';

$zero_to_null = true;
$user_id = Kuvardin\DataFilter::getInt($_POST['id'] ?? null, $zero_to_null);
if ($user_id !== null) {
    // ...
}
```
