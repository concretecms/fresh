<?php

namespace Concrete\Package\Seed;

use Concrete\Core\Package\Package;
use PortlandLabs\Seed\Console\CleanCommand;
use PortlandLabs\Seed\Console\SeedCommand;

class Controller extends Package
{

    protected $pkgHandle = 'seed';

    public function getPackageName()
    {
        return t('Database Seeder');
    }

    public function getPackageDescription()
    {
        return t('Seed or sanitize a site with good data');
    }

    public function on_start()
    {
        if ($this->app->resolved('console')) {
            $this->app->make('console')->add($this->app->make(SeedCommand::class));
            $this->app->make('console')->add($this->app->make(CleanCommand::class));
        }
    }

}
