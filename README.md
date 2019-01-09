# So Fresh and so Clean
The concrete5 `fresh` package makes it simple to clean a database or seed it with fresh data.

# Usage
Fresh adds two new commands to a concrete5 install:

- `concrete5 fresh:clean [cleaner?]` To clean (destroy) data and leave you with something clean that is safer to share
- `concrete5 fresh:seed [seeder?]` To seed data into your concrete5 install

## Examples
Seeders use the [`fresh::seeders` config group](https://github.com/concrete5/fresh/blob/master/config/seeders.php) and Cleaners use [`fresh::cleaners`](https://github.com/concrete5/fresh/blob/master/config/cleaners.php). Overrides for these settings will likely end up in `application/config/fresh/seeders.php` and `application/config/fresh/cleaners.php`.

#### Clean the site

```
$ ./vendor/bin/concrete5 fresh:clean
```

#### Seed 5 admins and 15 users into your site

```
$ ./vendor/bin/concrete5 c5:config set fresh::seeders.admins 5
$ ./vendor/bin/concrete5 c5:config set fresh::seeders.users 15
$ ./vendor/bin/concrete5 fresh:seed
```

#### Seed or Clean using a custom seeder

This package can only seed using one seeder or Cleaner at a time. Luckily [aggregate seeders](https://github.com/concrete5/fresh/blob/master/src/Seed/SimpleSeeder.php) and [cleaners](https://github.com/concrete5/fresh/blob/master/src/Clean/SimpleCleaner.php) totally work.

```
./vendor/bin/concrete5 fresh:seed "\Some\Custom\Seeder"
./vendor/bin/concrete5 fresh:clean "\Some\Custom\Cleaner"
```

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
