# Events

to do...

add this to `/config/bootstrap.php`:

```php
<?php

$app->on('tables.export.before', function($table, &$type, &$options) {

    // populate referenced table fields when exporting data
    $options['populate'] = 2;

    // use labels instead of field names as column headers
    if ($type == 'ods' || $type == 'xls' || $type == 'xlsx') {
        $options['pretty'] = true;
    }

});
```
