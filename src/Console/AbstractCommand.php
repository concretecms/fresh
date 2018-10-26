<?php

namespace PortlandLabs\Fresh\Console;

use Concrete\Core\Application\Application;
use Concrete\Core\Application\ApplicationAwareInterface;
use Concrete\Core\Cache;
use Concrete\Core\Config\Repository\Repository;
use Concrete\Core\Console\Command;
use PortlandLabs\Fresh\DatabaseModifier;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\OutputStyle;
use Symfony\Component\Console\Style\SymfonyStyle;

abstract class AbstractCommand extends Command
{

    protected $app;

    public function __construct(Application $app, $name = null)
    {
        if (!$this->parser) {
            $name = substr($this->signature, 0, strpos($this->signature, ' '));
        }

        parent::__construct($name);

        $this->app = $app;
    }

    /**
     * Handle a call to this command
     *
     * @param \Concrete\Core\Application\Application $app
     * @param \Concrete\Core\Config\Repository\Repository $repository
     * @return int|void
     */
    public function handle(Application $app, Repository $config)
    {
        $cleanerClass = null;
        $cleaner = null;

        if ($this->input->hasArgument($this->classArgument)) {
            $cleanerClass = $this->input->getArgument($this->classArgument);
        }

        // If a class isn't passed in, let's just resolve it from config
        if (!$cleanerClass) {
            $cleanerClass = $config->get($this->classConfigItem);
        }

        // Handle being passed an object
        if (\is_object($cleanerClass)) {
            $cleaner = $cleanerClass;
            $cleanerClass = get_class($cleaner);

            // Make sure the cleaner has the application if it needs it
            if ($cleaner instanceof ApplicationAwareInterface) {
                $cleaner->setApplication($app);
            }
        }

        // Build the cleaner instance
        if (!$cleaner && $cleanerClass) {
            $cleaner = $app->make($cleanerClass);
        }

        // Handle invalid cleaner
        if (!$cleaner || !$this->validateModifier($cleaner)) {
            if (is_object($cleaner)) {
                $class = class_basename($cleanerClass);
                $this->output->writeln("<error>Invalid $this->noun:</error> $class");
            } else {
                $this->output->writeln("<error>Invalid $this->noun</error>");
            }

            return 1;
        }

        $class = class_basename(\get_class($cleaner));
        $this->output->writeln("<info>$this->verb:</info> $class");

        // Disable caches
        Cache::disableAll();

        // Set cleaner output and run the cleaner
        $cleaner->setOutput($this->getOutput());
        $cleaner();
    }

    /**
     * Validate a given modifier, make sure it's the right type
     *
     * @param \PortlandLabs\Fresh\DatabaseModifier $modifier
     *
     * @return bool
     */
    abstract protected function validateModifier(DatabaseModifier $modifier): bool;

    /**
     * Shim for older concrete5 installs
     *
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     *
     * @return mixed
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            parent::execute($input, $output);
        } catch (\LogicException $e) {
            // Legacy support
            $this->input = $input;
            $this->output = new SymfonyStyle($input, $output);
            return isset($this->app) ? $this->app->call([$this, 'handle']) : $this->handle();
        }
    }

    /**
     * Get the output instance
     *
     */
    public function getOutput(): OutputStyle
    {
        return $this->output;
    }
}
