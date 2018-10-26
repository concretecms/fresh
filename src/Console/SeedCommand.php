<?php

namespace PortlandLabs\Fresh\Console;

use Concrete\Core\Application\Application;
use Concrete\Core\Config\Repository\Repository;
use PortlandLabs\Fresh\DatabaseModifier;
use PortlandLabs\Fresh\Seed\Seeder;
use Symfony\Component\Console\Style\OutputStyle;

class SeedCommand extends AbstractCommand implements FreshCommandInterface
{

    protected $signature = 'fresh:seed {seeder?}';

    protected $classArgument = 'seeder';
    protected $classConfigItem = 'fresh::seeders.seeder';
    protected $verb = 'Seeding';
    protected $noun = 'Seeder';

    /**
     * Get the output instance
     *
     * @return OutputStyle
     */
    public function getOutput(): OutputStyle
    {
        return $this->output;
    }

    /**
     * Validate a given modifier, make sure it's the right type
     *
     * @param \PortlandLabs\Fresh\DatabaseModifier $modifier
     *
     * @return bool
     */
    protected function validateModifier(DatabaseModifier $modifier): bool
    {
        return $modifier instanceof Seeder;
    }
}
