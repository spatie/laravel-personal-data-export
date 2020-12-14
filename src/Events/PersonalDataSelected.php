<?php

namespace Spatie\PersonalDataExport\Events;

use Spatie\PersonalDataExport\ExportsPersonalData;
use Spatie\PersonalDataExport\PersonalDataSelection;

class PersonalDataSelected
{
    public function __construct(
        public PersonalDataSelection $personalData,
        public ExportsPersonalData $user
    ) {}
}
