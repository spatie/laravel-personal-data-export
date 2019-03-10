<?php

namespace Spatie\PersonalDataExport\Exceptions;

use Exception;
use Spatie\PersonalDataExport\ExportsPersonalData;

class InvalidUser extends Exception
{
    public static function doesNotHaveAnEmailProperty(ExportsPersonalData $user)
    {
        $class = get_class($user);

        return new static("Could not create a personal data download for `$class` because it does have an email property.");
    }
}
