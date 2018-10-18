<?php

namespace PortlandLabs\Seed\Clean;

class SimpleCleaner extends Cleaner
{

    /**
     * Simple entry point for all default cleaners
     */
    public function run()
    {
        $this->call([
            FileCleaner::class,
            PrivateMessageCleaner::class,
            LogCleaner::class,
            UserCleaner::class
        ]);
    }
}
