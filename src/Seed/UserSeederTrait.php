<?php

namespace PortlandLabs\Fresh\Seed;

use Concrete\Core\Attribute\AttributeKeyInterface;
use Concrete\Core\User\Group\Group;
use Concrete\Core\User\RegistrationService;
use Concrete\Core\User\UserInfo;

trait UserSeederTrait
{

    protected $username;
    protected $email;
    protected $groups = [];
    protected $attributes = [];
    protected $password;
    protected $erroredAttributeKeys = [];

    /**
     * Begin registering a user.
     * call ->register() to complete the registration
     *
     * @param string|null $username
     * @param string|null $email
     * @param array $groups
     * @param array $attributes
     *
     * @return \PortlandLabs\Fresh\Seed\Seeder|static
     */
    public function user(
        string $username = null,
        string $email = null,
        array $groups = [],
        array $attributes = []
    ): Seeder {
        $this->username = $username;
        $this->email = $email;
        $this->groups = $groups;
        $this->attributes = $attributes;

        return $this;
    }

    /**
     * Set the user username
     *
     * @param string $username
     *
     * @return \PortlandLabs\Fresh\Seed\Seeder|static
     */
    public function withUsername(string $username): Seeder
    {
        $this->username = $username;
        return $this;
    }

    /**
     * Set the user email
     *
     * @param string $email
     *
     * @return \PortlandLabs\Fresh\Seed\Seeder|static
     */
    public function withEmail(string $email): Seeder
    {
        $this->email = $email;
        return $this;
    }

    /**
     * Add a user group for this user to end up in
     *
     * @param string|\Concrete\Core\User\Group\Group $group
     *
     * @return \PortlandLabs\Fresh\Seed\Seeder|static
     */
    public function withAddedGroup($group): Seeder
    {
        $this->groups[] = $group;
        return $this;
    }

    /**
     * Add attribute keys and values for the user to get
     *
     * @param AttributeKeyInterface|string $key Attribute key or handle
     * @param mixed $value The value
     *
     * @return \PortlandLabs\Fresh\Seed\Seeder|static
     */
    public function withAttribute($key, $value): Seeder
    {
        $handle = $key;
        if ($key instanceof AttributeKeyInterface) {
            $handle = $key->getAttributeKeyHandle();
        }

        $this->attributes[$handle] = [$key, $value];

        return $this;
    }

    /**
     * Set the user password
     *
     * @param string $password
     *
     * @return \PortlandLabs\Fresh\Seed\Seeder|static
     */
    public function withPassword(string $password): Seeder
    {
        $this->password = $password;

        return $this;
    }

    /**
     *
     * @param \Concrete\Core\User\RegistrationService $factory
     *
     * @return \Concrete\Core\User\UserInfo
     */
    public function register(RegistrationService $factory, callable $callback = null): UserInfo
    {
        /** @var RegistrationService $factory */
        $user = $factory->create([
            'uName' => $this->username,
            'uEmail' => $this->email,
            'uPassword' => $this->password ?: $this->generateUserPassword($this->username, $this->email),
            'uIsValidated' => true
        ]);

        $this->applyGroups($user);
        $this->applyAttributes($user);

        if ($callback) {
            $user = $callback($user);
        }

        return $user;
    }

    protected function applyGroups(UserInfo $userInfo)
    {
        /** @var \Concrete\Core\User\User $user */
        $user = $userInfo->getUserObject();
        $userGroups = $user->getUserGroups();

        foreach ($this->groups as &$group) {
            if (is_string($group)) {
                $group = Group::getByName($group);
            }

            if (!$group instanceof Group) {
                // Ignore failure
                continue;
            }

            if (!in_array($group->getGroupID(), $userGroups)) {
                $user->enterGroup($group);
                $userGroups[] = $group->getGroupID();
            }
        }
    }

    protected function applyAttributes(UserInfo $user)
    {
        foreach ($this->attributes as $attribute) {
            try {
                $user->setAttribute(
                    $attribute[0],
                    $attribute[1]);
            } catch (\Exception $e) {
                $this->erroredAttributeKeys[] = $attribute[0];
            }
        }
    }

    /**
     * Suuuuuuuper secure passwords
     *
     * @param string $userName
     * @param string $email
     *
     * @return string
     */
    protected function generateUserPassword(string $userName, string $email): string
    {
        return $userName . '!123';
    }

}
