<?php

namespace PortlandLabs\Fresh\Console;

use Symfony\Component\Console\Style\OutputStyle;

interface FreshCommandInterface
{

    /**
     * Get the output object associated with this command
     *
     * @return \Symfony\Component\Console\Style\OutputStyle
     */
    public function getOutput(): OutputStyle;

}
