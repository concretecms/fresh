<?php

namespace PortlandLabs\Fresh\Clean;

use Concrete\Core\Database\Connection\Connection;
use Psr\Log\LoggerInterface;

/**
 * A cleaner for clearing out the Logs database table
 */
class LogCleaner extends Cleaner
{

    /**
     * Clear out any log entries
     *
     * @param \Concrete\Core\Database\Connection\Connection $database
     *
     * @throws \Doctrine\DBAL\DBALException
     */
    public function run(Connection $database, LoggerInterface $logger)
    {
        $this->output->writeln('<info>⤷</info> Truncating Logs');
        $database->exec('truncate Logs');

        $this->output->writeln('<info>⤷</info> Adding a single log entry');
        $logger->debug('Truncated logs with `seed:clean` command');
    }
}
