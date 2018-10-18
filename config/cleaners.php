<?php
use \PortlandLabs\Seed\Clean;

return [
    'cleaners' => [
        'simple' => Clean\SimpleCleaner::class
    ],
    'attributes' => [
        /** Map attributes directly to a Faker property */
        'first_name' => 'firstName',
        'last_name' => 'lastName'
    ],
    'clean_super_admin' => false,
    /** File types that we don't need to sanitize */
    'default_skip_file_types' => [
        'jpg', 'jpeg', 'png', 'gif', 'svg', 'apng', 'bmp', 'ico'
    ],
    'skip_file_types' => null
];
