# So Fresh and so Clean
The concrete5 `fresh` package makes it simple to clean a database or seed it with fresh data.


## Customizing
Fresh is really easy to customize with your own cleaners / seeders. A few ways to get started are listed below

### Quick and Dirty
If you're working to test something, or needing to quickly clean things from your install without permanent changes to
your project, you may just want a simple entry point for custom functionality.

In your `/application/bootstrap/app.php` you can define your cleaner / seeder:
```php
// Override `fresh::cleaners.cleaner` config entry
$app['config']['fresh::cleaners.cleaner'] = new Class() extends \PortlandLabs\Fresh\Clean\Cleaner {

    public function run()
    {
        $this->output->section('Custom Cleaner!');
    }
};
```

### Maintainable and happy

Rather than making a quick and dirty anonymous class, let's use configuration to point to a class that exists in our
namespace.

First make sure you have a class that exists in your namespace, in this example we're using `\PortlandLabs\FooBaz\CleanRoutine`.

Next override the `fresh::cleaners.cleaner` or the `fresh::seeders.seeder` config item
```php
<?php

return [
    'cleaner' => '\PortlandLabs\FooBaz\CleanRoutine'
];
```
