<?php

namespace Spatie\PersonalDataExport\Tests\TestClasses;

use Illuminate\Support\Str;
use Spatie\PersonalDataExport\ExportsPersonalData;
use Spatie\PersonalDataExport\PersonalDataSelection;
use Illuminate\Foundation\Auth\User as BaseUser;

class User extends BaseUser implements ExportsPersonalData
{
    public function exportsPersonalData(PersonalDataSelection $personalData): void
    {
        $personalData
            ->addFile(__DIR__.'/../stubs/avatar.png')
            ->add('attributes.json', $this->attributesToArray());
    }

    public function getPersonalDataExportName(): string
    {
        $usernameSlug = Str::slug($this->name);

        return "personal-data-{$usernameSlug}.zip";
    }
}
