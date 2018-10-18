<?php

namespace PortlandLabs\Seed\Clean;

use Concrete\Core\Database\Connection\Connection;
use Faker\Factory;

/**
 * Clean out private messages
 */
class PrivateMessageCleaner extends Cleaner
{

    /**
     * Loop over private messages and update them to have lipsum instead of real data
     *
     * @param \Concrete\Core\Database\Connection\Connection $connection
     *
     * @throws \Doctrine\DBAL\DBALException
     */
    public function run(Connection $connection)
    {
        $faker = Factory::create();

        foreach ($this->allPrivateMessages($connection) as $privateMessageID) {
            $this->output->writeln('<info>⤷</info> Private Message ' . $privateMessageID);
            $connection->update('UserPrivateMessages',
                [
                    'msgSubject' => $faker->sentence,
                    'msgBody' => $faker->paragraph
                ],
                [
                    'msgID' => $privateMessageID
                ]);
        }
    }

    /**
     * Collect all private messages in a scalable way
     *
     * @param \Concrete\Core\Database\Connection\Connection $connection
     *
     * @return \Generator
     */
    protected function allPrivateMessages(Connection $connection)
    {
        $qb = $connection->createQueryBuilder()->select('msgID')->from('UserPrivateMessages')->setMaxResults(10);
        $i = 0;

        while(1) {
            $qb->setFirstResult($i);
            $results = $qb->execute();
            if (!$results->rowCount()) {
                break;
            }

            foreach ($results as $item) {
                yield $item['msgID'];
                $i++;
            }
        }
    }

}
