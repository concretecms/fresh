<?php

namespace PortlandLabs\Fresh\Clean;

class SimpleCleaner extends Cleaner
{

    /**
     * Simple entry point for all default cleaners
     */
    public function run()
    {
        $this->call([
            FileCleaner::class,
            UserCleaner::class,
            EntityCleaner::class,
            PrivateMessageCleaner::class,
            LogCleaner::class,
            FormCleaner::class
        ]);
    }
}
