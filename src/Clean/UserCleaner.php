<?php

namespace PortlandLabs\Fresh\Clean;

use Concrete\Core\Config\Repository\Repository;
use Concrete\Core\Entity\User\User;
use Concrete\Core\User\Group\Group;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\ORMException;
use Faker\Factory;
use Faker\Generator;
use Faker\Provider\Person;

/**
 * A cleaner for clearing out user's PII
 * This class will clear out username, user email, password, and configured attributes
 */
class UserCleaner extends Cleaner
{

    /**
     * Clean all users
     *
     * @param \Doctrine\ORM\EntityManagerInterface $em
     * @param \Concrete\Core\Config\Repository\Repository $config
     */
    public function run(EntityManagerInterface $em, Repository $config)
    {
        $faker = Factory::create();
        $cleanAdmin = $config->get('fresh::cleaners.clean_super_admin', false);
        $repository = $em->getRepository(User::class);
        $allUsers = $repository->findAll();
        $skipUIDs = $this->getSkipUIDs($config);

        // Clean users
        foreach ($allUsers as $user) {
            // Skip if clean_super_admin is false or the user exists in the skip groups
            if ((!$cleanAdmin && $user->getUserID() === USER_SUPER_ID) || in_array($user->getUserID(), $skipUIDs, false)) {
                continue;
            }

            $start = microtime(true);
            $this->cleanUser($user, $faker, $em, $repository, $config);
            $end = microtime(true);

            $this->output->writeln(sprintf('  <info>⤷</info> Completed in <info>%s</info> ms using %s bytes of memory',
                ($end - $start) * 1000, memory_get_usage(true)));
            $this->output->newLine();
        }
    }

    /**
     * Clean a user object
     *
     * @param \Concrete\Core\Entity\User\User $user
     * @param \Faker\Generator $faker
     * @param \Doctrine\ORM\EntityManagerInterface $entityManager
     * @param \Doctrine\ORM\EntityRepository $repository
     * @param \Concrete\Core\Config\Repository\Repository $config
     */
    protected function cleanUser(
        User $user,
        Generator $faker,
        EntityManagerInterface $entityManager,
        EntityRepository $repository,
        Repository $config
    ) {
        $gender = mt_rand(1, 2) === 2 ? Person::GENDER_FEMALE : Person::GENDER_MALE;
        $firstName = $faker->firstName($gender);
        $lastName = $faker->lastName;

        do {
            $username = $faker->userName;
            $email = $faker->email;
        } while($this->inUse($repository, $username, $email));

        $this->output->writeln(sprintf('<info>⤷</info> User %s <info>-></info> %s', $user->getUserID(), $username));

        $entityManager->transactional(function (EntityManagerInterface $localManager) use (
            $user,
            $username,
            $email,
            $faker
        ) {
            /** @var User $user */
            $user = $localManager->merge($user);

            $user->setUserEmail($email);
            $user->setUserName($username);
            $user->setUserPassword(password_hash($this->generateUserPassword($username, $email),
                PASSWORD_DEFAULT));
            $user->setUserLastIP('127.0.0.1');
        });

        $attributes = $config->get('fresh::cleaners.attributes');
        if ($attributes) {
            $info = $user->getUserInfoObject();
            foreach ($attributes as $key => $map) {
                $value = $this->fakerValue($faker, $map, $user, $firstName, $lastName);

                try {
                    $info->setAttribute($key, $value);
                    $this->output->writeln("  <info>⤷</info> Setting $key <info>→</info> $value");
                } catch (ORMException $e) {
                    // Ignore
                }
            }
        }

    }

    /**
     * Get a mapped value from Faker
     *
     * @param \Faker\Generator $faker
     * @param string|array $map
     *
     * @return string|string[]
     */
    protected function fakerValue(Generator $faker, $map, User $user, $firstName, $lastName)
    {
        $localMap = [
            'first_name' => $firstName,
            'last_name' => $lastName
        ];

        if (is_string($map) && isset($localMap[$map])) {
            return $localMap[$map];
        }

        if (is_array($map)) {
            return array_map(function($item) use ($faker, $user, $firstName, $lastName) {
                return $this->fakerValue($faker, $item, $user, $firstName, $lastName);
            }, $map);
        }

        return $faker->{$map};
    }

    /**
     * Suuuuuuuper secure passwords
     *
     * @param string $username
     * @param string $email
     *
     * @return string
     */
    protected function generateUserPassword(string $username, string $email): string
    {
        return "$username!123";
    }

    /**
     * Determine whether the current username or email is in use
     *
     * @param \Doctrine\ORM\EntityRepository $userRepository
     * @param string $username
     * @param string $email
     *
     * @return bool
     */
    private function inUse(EntityRepository $userRepository, string $username, string $email)
    {
        $qb = $userRepository->createQueryBuilder('u');
        $result = $qb->select('count(u.uID) as c')
            ->where($qb->expr()->eq('u.uName', ':username'))
            ->orWhere($qb->expr()->eq('u.uEmail', ':email'))
            ->getQuery()->execute([
                'username' => $username,
                'email' => $email
            ]);

        return (bool)$result[0]['c'];
    }

    /**
     * Get the users IDs from skip user groups
     *
     * @param Repository $config
     *
     * @return array
     */
    protected function getSkipUIDs(Repository $config)
    {
        $skipUIDs = [];
        $skipGroups = $config->get('fresh::cleaners.skip_user_groups');

        if (is_array($skipGroups)) {
            foreach ($skipGroups as $skipGroup) {
                $group = Group::getByName($skipGroup);
                if ($group) {
                    $skipUIDs = array_unique(array_merge($skipUIDs, $group->getGroupMemberIDs()));
                }
            }
        }

        return $skipUIDs;
    }
}
