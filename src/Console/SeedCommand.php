<?php

namespace PortlandLabs\Seed\Console;

use Concrete\Core\Application\Application;
use Concrete\Core\Config\Repository\Repository;
use Concrete\Core\Console\Command;
use Concrete\Core\Console\OutputStyle;
use PortlandLabs\Seed\Seed\Seeder;

class SeedCommand extends Command implements SeedCommandInterface
{

    protected $signature = 'seed:seed {seeder?}';

    public function __construct($name = null)
    {
        parent::__construct($name);
    }

    /**
     * Handle a call to this command
     *
     * @param \Concrete\Core\Config\Repository\Repository $repository
     */
    public function handle(Application $app, Repository $config)
    {
        if ($seeder = $this->input->getArgument('seeder')) {
            $seeders = [$seeder];
        } else {
            $seeders = $config->get('seed::seeders.seeders');
        }

        /** @var \PortlandLabs\Seed\Seed\Seeder[] $seeders */
        foreach ($seeders as $seeder) {
            if (\is_string($seeder)) {
                $seeder = $app->make($seeder);
            }

            if (!$seeder instanceof Seeder) {
                continue;
            }

            $class = class_basename(get_class($seeder));

            $this->output->writeln("<info>Seeding:</info> $class");

            $seeder->setOutput($this->getOutput());
            $seeder();
        }
    }

    /**
     * Get the output instance
     *
     * @return \Concrete\Core\Console\OutputStyle
     */
    public function getOutput(): OutputStyle
    {
        return $this->output;
    }
}
