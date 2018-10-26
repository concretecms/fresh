<?php

namespace PortlandLabs\Fresh\Clean;

use Concrete\Core\Database\Connection\Connection;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;

class FormCleaner extends Cleaner
{

    public function run(EntityManagerInterface $em)
    {
        $this->clearLegacyForms($em->getConnection());
    }

    protected function clearLegacyForms(Connection $connection)
    {
        $this->output->writeln('<info>⤷</info> Clearing legacy answer sets');
        $connection->exec('truncate btFormAnswerSet');

        $this->output->writeln('<info>⤷</info> Clearing legacy answers');
        $connection->exec('truncate btFormAnswers');
    }

}
