<?php

namespace Spatie\PersonalDataDownload\Exceptions;

use Exception;
use Illuminate\Database\Eloquent\Model;

class InvalidUser extends Exception
{
    public static function doesNotHaveSelectPersonalDataMethod(Model $user)
    {
        $class = get_class($user);

        return new static("Could not create a personal data download for `$class` because it does not have a `selectsPersonalData` method on it");
    }


}