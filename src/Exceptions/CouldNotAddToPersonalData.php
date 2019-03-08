<?php

namespace Spatie\PersonalDataDownload\Exceptions;

use Exception;

class CouldNotAddToPersonalData extends Exception
{
    public static function fileAlreadyAddedToPersonalData(string $path)
    {
        return new static("Could not add `{$path}` because it already exists.");
    }


}