<?php

namespace PortlandLabs\Seed\Clean;

use Concrete\Core\Config\Repository\Repository;
use Concrete\Core\Entity\File\Version;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\Pagination\Paginator;

/**
 * A cleaner that cleans out certain file types
 * Configure seed::cleaners.skip_file_types to set which file types are excluded
 */
class FileCleaner extends Cleaner
{

    /**
     * Cleans all file versions that match certain types
     *
     * @param \Doctrine\ORM\EntityManagerInterface $em
     * @param \Concrete\Core\Config\Repository\Repository $config
     *
     * @throws \League\Flysystem\FileNotFoundException
     */
    public function run(EntityManagerInterface $em, Repository $config)
    {
        $skipTypes = $config->get('seed::cleaners.skip_file_types');

        if (is_null($skipTypes)) {
            $skipTypes = $config->get('seed::cleaners.default_skip_file_types');
        }

        $qb = $em->createQueryBuilder();

        $qb->select('v')->from(Version::class, 'v');

        if ($skipTypes) {
            $qb->where($qb->expr()->notIn('v.fvExtension', ':extensions'));
            $qb->setParameter('extensions', $skipTypes);
        }

        $paginator = new Paginator($qb, false);

        /** @var Version $version */
        foreach ($paginator as $version) {
            $this->output->writeln(sprintf(
                '<info>⤷</info> Truncating File %d Version %d: %s',
                $version->getFileID(),
                $version->getFileVersionID(),
                $version->getFileName()
            ));

            $this->zeroOut($version);
        }

        exit;
    }

    /**
     * Zero out a file on the filesystem
     *
     * @param \Concrete\Core\Entity\File\Version $version
     *
     * @throws \League\Flysystem\FileNotFoundException
     */
    protected function zeroOut(Version $version)
    {
        $version->getFileResource()->put('');
    }

}
