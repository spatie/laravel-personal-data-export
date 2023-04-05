<?php

namespace Spatie\PersonalDataExport\Tests\TestClasses;

use Illuminate\Notifications\Notifiable;
use Spatie\PersonalDataExport\ExportsPersonalData;
use Spatie\PersonalDataExport\PersonalDataSelection;

class InvalidUser implements ExportsPersonalData
{
    use Notifiable;

    protected $guarded = [];

    protected $table = 'users';

    public function selectPersonalData(PersonalDataSelection $personalDataSelection): void
    {
    }

    public function personalDataExportName(): string
    {
    }

    public function getKey()
    {
        return 1;
    }
}
