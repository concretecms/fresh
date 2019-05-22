<?php

return [
    'cleaner' =>  \PortlandLabs\Fresh\Clean\SimpleCleaner::class,
    'attributes' => [
        /** Map attributes directly to a Faker property */
        /** 'first_name' => 'firstName' */
    ],
    /** Whether the super admin should be cleaned by the UserCleaner */
    'clean_super_admin' => false,
    /** User controlled 'skip_user_groups' */
    'skip_user_groups' => ['Administrators'],
    /** File types that we don't need to sanitize */
    'default_skip_file_types' => [
        'jpg', 'jpeg', 'png', 'gif', 'svg', 'apng', 'bmp', 'ico'
    ],
    /** User controlled 'skip_file_types' */
    'skip_file_types' => null,
    /** Which entities need to be cleared */
    'entities' => []
];
