<?php

namespace PortlandLabs\Fresh\Console;

use Concrete\Core\Application\Application;
use Concrete\Core\Application\ApplicationAwareInterface;
use Concrete\Core\Config\Repository\Repository;
use PortlandLabs\Fresh\Clean\Cleaner;
use PortlandLabs\Fresh\DatabaseModifier;
use Symfony\Component\Console\Style\OutputStyle;

class CleanCommand extends AbstractCommand implements FreshCommandInterface
{
    /** @var string Signature for modern support */
    protected $signature = 'fresh:clean {cleaner?}';

    protected $classArgument = 'cleaner';
    protected $classConfigItem = 'fresh::cleaners.cleaner';
    protected $verb = 'Cleaning';
    protected $noun = 'Cleaner';

    /**
     * Validate a given modifier, make sure it's the right type
     *
     * @param \PortlandLabs\Fresh\DatabaseModifier $modifier
     *
     * @return bool
     */
    protected function validateModifier(DatabaseModifier $modifier): bool
    {
        return $modifier instanceof Cleaner;
    }
}
