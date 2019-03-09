<?php

namespace Spatie\PersonalDataDownload\Events;

use Illuminate\Database\Eloquent\Model;
use Spatie\PersonalDataDownload\PersonalData;

class PersonalDataSelected
{
    /** @var \Spatie\PersonalDataDownload\PersonalData */
    public $personalData;

    /** @var \Illuminate\Database\Eloquent\Model */
    public $user;

    public function __construct(PersonalData $personalData, Model $user)
    {
        $this->personalData = $personalData;

        $this->user = $user;
    }
}
