<?php

namespace PortlandLabs\Seed\Console;

use Concrete\Core\Console\OutputStyle;

interface SeedCommandInterface
{

    /**
     * Get the output object associated with this command
     *
     * @return \Concrete\Core\Console\OutputStyle
     */
    public function getOutput(): OutputStyle;

}
