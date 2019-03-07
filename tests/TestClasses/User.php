<?php

namespace Spatie\PersonalDataDownload\Tests\TestClasses;

use Spatie\PersonalDataDownload\PersonalData;
use Illuminate\Foundation\Auth\User as BaseUser;

class User extends BaseUser
{
    public function selectPersonalData(PersonalData $personalData)
    {
        $personalData
            ->addFile(__DIR__.'/../stubs/avatar.png')
            ->addContent('attributes.json', $this->attributesToArray());
    }
}
