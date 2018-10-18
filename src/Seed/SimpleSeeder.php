<?php

namespace PortlandLabs\Seed\Seed;

use Concrete\Core\Config\Repository\Repository;

class SimpleSeeder extends Seeder
{

    public function run(Repository $config)
    {
        $this->call([
            UserSeeder::class
        ]);
    }
}
