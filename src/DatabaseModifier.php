<?php

namespace PortlandLabs\Fresh;

use Concrete\Core\Application\ApplicationAwareInterface;
use Concrete\Core\Application\ApplicationAwareTrait;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Database modifier class based on Laravel's database seeder class
 */
abstract class DatabaseModifier implements ApplicationAwareInterface
{

    use ApplicationAwareTrait;

    /**
     * The console output instance.
     *
     * @var \Symfony\Component\Console\Style\OutputStyle|\Concrete\Core\Console\OutputStyle
     */
    protected $output;

    /**
     * Seed the given connection from the given path.
     *
     * @param  array|string $class
     * @param  bool $silent
     *
     * @return $this
     */
    public function call($class, $silent = false)
    {
        $classes = !is_array($class) ? [$class] : $class;

        foreach ($classes as $modifier) {
            if ($silent === false && isset($this->output)) {
                $basename = class_basename($modifier);
                $this->output->section($basename);
            }

            $this->resolve($modifier)->__invoke();
        }

        return $this;
    }

    /**
     * Silently seed the given connection from the given path.
     *
     * @param  array|string $class
     *
     * @return void
     */
    public function callSilent($class)
    {
        $this->call($class, true);
    }

    /**
     * Resolve an instance of the given seeder class.
     *
     * @param  string $class
     *
     * @return \Illuminate\Database\Seeder
     */
    protected function resolve($class)
    {
        if (isset($this->app)) {
            $instance = $this->app->make($class);
        } else {
            $instance = new $class;
        }
        if (isset($this->output)) {
            $instance->setOutput($this->output);
        }
        return $instance;
    }

    /**
     * Set the console output instance.
     *
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     *
     * @return $this
     */
    public function setOutput(OutputInterface $output)
    {
        $this->output = $output;
        return $this;
    }

    /**
     * Run the database seeds.
     *
     * @return void
     *
     * @throws \InvalidArgumentException
     */
    public function __invoke()
    {
        if (!method_exists($this, 'run')) {
            throw new InvalidArgumentException('Method [run] missing from ' . get_class($this));
        }
        return isset($this->app)
            ? $this->app->call([$this, 'run'])
            : $this->run();
    }
}
