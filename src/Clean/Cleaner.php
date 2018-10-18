<?php

namespace PortlandLabs\Seed\Clean;

use PortlandLabs\Seed\DatabaseModifier;

/**
 * Parent class for Cleaner subclasses
 *
 * A cleaner's job is to take things that already exist in the database and sanitize them for use in unsafe environments
 */
abstract class Cleaner extends DatabaseModifier
{

}
