<?php

namespace Spatie\PersonalDataExport\Events;

use Illuminate\Database\Eloquent\Model;
use Spatie\PersonalDataExport\ExportsPersonalData;
use Spatie\PersonalDataExport\PersonalDataSelection;

class PersonalDataSelected
{
    /** @var \Spatie\PersonalDataExport\PersonalDataSelection */
    public $personalData;

    /** @var \Spatie\PersonalDataExport\ExportsPersonalData */
    public $user;

    public function __construct(PersonalDataSelection $personalData, ExportsPersonalData $user)
    {
        $this->personalData = $personalData;

        $this->user = $user;
    }
}
