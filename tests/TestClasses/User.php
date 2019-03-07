<?php

namespace Spatie\PersonalDataDownload\Tests\TestClasses;

use Illuminate\Foundation\Auth\User as BaseUser;
use Spatie\PersonalDataDownload\PersonalData;

class User extends BaseUser
{
    public function selectPersonalData(PersonalData $personalData)
    {
        $personalData->addContent('attributes.json', $this->attributesToArray());
    }
}