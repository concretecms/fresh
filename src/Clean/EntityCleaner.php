<?php

namespace PortlandLabs\Fresh\Clean;

use Concrete\Core\Config\Repository\Repository;
use Concrete\Core\Entity\Express\Entity;
use Concrete\Core\Entity\Express\Entry;
use Doctrine\ORM\EntityManagerInterface;

class EntityCleaner extends Cleaner
{

    /**
     * Delete all entries for entities listed in `fresh::cleaners.entities`
     *
     * @param \Concrete\Core\Config\Repository\Repository $config
     * @param \Doctrine\ORM\EntityManagerInterface $em
     */
    public function run(Repository $config, EntityManagerInterface $em)
    {
        $entities = (array)$config->get('fresh::cleaners.entities', []);
        $buffer = [];
        $count = 0;

        if ($entities) {
            foreach ($this->allEntries($em, $entities) as $entry) {
                $this->output->writeln(sprintf('  <info>⤷</info> Deleting entry "%s" of type "%s"', $entry->getID(), $entry->getEntity()->getHandle()));
                $em->remove($entry);

                $buffer[] = $entry;
                $count++;

                if ($count > 100) {
                    $this->clearBuffer($em, $buffer);
                    $buffer = [];
                    $count = 0;
                }
            }
        }

        $this->clearBuffer($em, $buffer);
    }

    /**
     * Clear removed entities from the buffer
     *
     * @param \Doctrine\ORM\EntityManagerInterface $em
     * @param array $buffer
     */
    protected function clearBuffer(EntityManagerInterface $em, array $buffer)
    {
        if (!$buffer) {
            return;
        }

        $em->flush();
        foreach ($buffer as $entry) {
            $em->detach($entry);
        }
    }

    /**
     * Get all entries
     *
     * @param \Doctrine\ORM\EntityManagerInterface $em
     * @param array $entities
     *
     * @return iterable|Entry[]
     */
    private function allEntries(EntityManagerInterface $em, array $entities): iterable
    {
        $qb = $em->createQueryBuilder();
        $results = $qb
            ->select('e')
            ->from(Entry::class, 'e')
            ->join('e.entity', 'entity')
            ->where($qb->expr()->in('entity.handle', $entities))
            ->orderBy('entity.handle')
            ->getQuery()
            ->iterate();

        foreach ($results as $row) {
            $entry = head($row);
            yield $entry;
        }
    }

}
