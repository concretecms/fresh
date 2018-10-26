<?php

namespace PortlandLabs\Fresh\Clean;

use Concrete\Core\Config\Repository\Repository;
use Concrete\Core\Entity\Express\Entity;
use Concrete\Core\Entity\Express\Entry;
use Doctrine\ORM\EntityManagerInterface;

class EntityCleaner extends Cleaner
{

    public function run(Repository $config, EntityManagerInterface $em)
    {
        $entities = (array)$config->get('fresh::cleaners.entities', []);
        $buffer = [];
        $count = 0;

        if ($entities) {
            foreach ($this->allEntries($em, $entities) as $entry) {
                $this->output->writeln(sprintf('  <info>⤷</info> Deleting entry "%s"', $entry->getID()));
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

    private function allEntries(EntityManagerInterface $em, array $entities)
    {
        $entityRepository = $em->getRepository(Entity::class);
        $entryRepository = $em->getRepository(Entry::class);

        foreach ($entities as $entityHandle) {
            /** @var Entity $entity */
            $entity = $entityRepository->findOneBy([
                'handle' => $entityHandle
            ]);

            if (!$entity) {
                continue;
            }

            $this->output->writeln(sprintf('<info>⤷</info> Clearing submissions for entity "%s"', $entityHandle));

            $entries = $entryRepository->findBy([
                'entity' => $entity
            ]);

            foreach ($entries as $entry) {
                yield $entry;
            }
        }
    }

}
