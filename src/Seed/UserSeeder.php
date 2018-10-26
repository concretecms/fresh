<?php

namespace PortlandLabs\Fresh\Seed;

use Concrete\Core\Config\Repository\Repository;
use Concrete\Core\User\RegistrationService;
use Faker\Generator;
use Faker\Provider\Person;

/**
 * A seeder for adding users to concrete5
 */
class UserSeeder extends Seeder
{

    use UserSeederTrait;

    /**
     * Seed users into concrete5
     *
     * @param \Concrete\Core\Config\Repository\Repository $config
     */
    public function run(Repository $config)
    {
        $users = (int)$config->get('fresh::seeders.users', 0);
        $admins = (int)$config->get('fresh::seeders.admins', 0);

        $faker = \Faker\Factory::create();
        $service = $this->app->make(RegistrationService::class);

        while($users--) {
            $this->seedUser($faker, $service);
        }

        while($admins--) {
            $this->seedUser($faker, $service, ['administrators']);
        }

    }

    /**
     * Seed a single new User entry
     *
     * @param \Faker\Generator $faker
     * @param \Concrete\Core\User\RegistrationService $service
     * @param array $groups
     *
     * @return \Concrete\Core\User\UserInfo
     */
    protected function seedUser(Generator $faker, RegistrationService $service, array $groups = [])
    {
        $gender = mt_rand(1, 2) === 2 ? Person::GENDER_MALE : Person::GENDER_FEMALE;

        $firstName = $faker->firstName($gender);
        $lastName = $faker->lastName;

        $user = $this->user(\Illuminate\Support\Str::slug($firstName . ' ' . $lastName))
            ->withEmail($faker->email)
            ->withAttribute('first_name', $firstName)
            ->withAttribute('last_name', $lastName);

        foreach ($groups as $group) {
            $user->withAddedGroup($group);
        }

        $this->output->writeln(sprintf('<info>Adding user:</info> "%s %s"%s', $firstName, $lastName,
            $groups ? sprintf(' to %s', implode(', ', $groups)) : ''));
        return $user->register($service);
    }

}
