<?php

namespace PortlandLabs\Seed\Console;

use Concrete\Core\Application\Application;
use Concrete\Core\Config\Repository\Repository;
use Concrete\Core\Console\Command;
use Concrete\Core\Console\OutputStyle;
use PortlandLabs\Seed\Clean\Cleaner;

class CleanCommand extends Command implements SeedCommandInterface
{

    protected $signature = 'seed:clean {cleaner?}';

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
        if ($cleaner = $this->input->getArgument('cleaner')) {
            $cleaners = [$cleaner];
        } else {
            $cleaners = $config->get('seed::cleaners.cleaners');
        }

        /** @var \PortlandLabs\Seed\Seed\Cleaner[] $cleaners */
        foreach ($cleaners as $cleaner) {
            if (\is_string($cleaner)) {
                $cleaner = $app->make($cleaner);
            }

            if (!$cleaner instanceof Cleaner) {
                if (is_object($cleaner)) {
                    $class = class_basename(get_class($cleaner));
                    $this->output->writeln("<error>Invalid Cleaner:</error> $class");
                } else {
                    $this->output->writeln("<error>Invalid Cleaner</error>");
                }
                continue;
            }

            $class = class_basename(get_class($cleaner));
            $this->output->writeln("<info>Cleaning:</info> $class");

            $cleaner->setOutput($this->getOutput());
            $cleaner();
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
