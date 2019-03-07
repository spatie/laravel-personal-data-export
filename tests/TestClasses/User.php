<?php

namespace Spatie\PersonalDataDownload\Tests\TestClasses;

use Illuminate\Database\Eloquent\Model;
use Spatie\PersonalDataDownload\PersonalData;

class User extends Model
{
    public function selectPersonalData(PersonalData $personalData)
    {
        $personalData->addContent('attributes.json', $this->attributesToArray());
    }
}