<?php

namespace Spatie\PersonalDataExport\Tests\TestClasses;

use Spatie\PersonalDataExport\ExportsPersonalData;
use Spatie\PersonalDataExport\PersonalDataSelection;

class InvalidUser implements ExportsPersonalData
{
    protected $guarded = [];

    protected $table = 'users';

    public function selectPersonalData(PersonalDataSelection $personalData): void
    {
    }

    public function getPersonalDataExportName(): string
    {
    }

    public function getKey()
    {
        return 1;
    }
}
