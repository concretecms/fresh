<?php

namespace PortlandLabs\Fresh\Clean;

use function basename;
use Concrete\Core\Config\Repository\Repository;
use Concrete\Core\Entity\File\Version;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Exception;
use League\Flysystem\FileNotFoundException;
use function touch;

/**
 * A cleaner that cleans out certain file types
 * Configure fresh::cleaners.skip_file_types to set which file types are excluded
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
        $skipTypes = $config->get('fresh::cleaners.skip_file_types');

        if (is_null($skipTypes)) {
            $skipTypes = $config->get('fresh::cleaners.default_skip_file_types');
        }

        $qb = $em->createQueryBuilder();

        $qb->select('v')->from(Version::class, 'v');

        if ($skipTypes) {
            $this->output->writeln('<info>Skipping types: ' . implode(', ', $skipTypes));
            $qb->where($qb->expr()->notIn('v.fvExtension', ':extensions'));
            $qb->setParameter('extensions', $skipTypes);
        }

        /** @var Version $version */
        foreach ($qb->getQuery()->iterate() as $row) {
            $version = head($row);

            $this->output->writeln(sprintf(
                '<info>⤷</info> Truncating File %d Version %d: %s',
                $version->getFileID(),
                $version->getFileVersionID(),
                $version->getFileName()
            ));

            $this->zeroOut($version);
            $em->detach($version);
        }

        // Clear out anything related to us
        $em->clear();
    }

    /**
     * Zero out a file on the filesystem
     *
     * @param \Concrete\Core\Entity\File\Version $version
     */
    protected function zeroOut(Version $version)
    {
        try {
            $version->getFileResource()->put('');
        } catch (FileNotFoundException $e) {
            // If the file is not found, create an empty stand-in
            $path = DIR_BASE . $version->getRelativePath();
            $dir = dirname($path);
            $this->output->writeln('  <info>⤷</info> File doesn\'t exist.. Creating it now.');
            if (!file_exists($dir) && !mkdir(\dirname($path), 0777, true)) {
                $this->output->error($e->getMessage());
            } else {
                touch($path);
            }
        } catch (Exception $e) {
            $this->output->error($e->getMessage());
        }
    }

}
