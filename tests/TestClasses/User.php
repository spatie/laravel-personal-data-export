<?php

namespace Spatie\PersonalDataExport\Tests\TestClasses;

use Illuminate\Support\Str;
use Illuminate\Foundation\Auth\User as BaseUser;
use Spatie\PersonalDataExport\ExportsPersonalData;
use Spatie\PersonalDataExport\PersonalDataSelection;

class User extends BaseUser implements ExportsPersonalData
{
    public function selectPersonalData(PersonalDataSelection $personalData): void
    {
        $personalData
            ->addFile(__DIR__.'/../stubs/avatar.png')
            ->addFile('thumbnail.png', 'user-disk')
            ->add('attributes.json', $this->attributesToArray());
    }

    public function personalDataExportName(): string
    {
        $usernameSlug = Str::slug($this->name);

        return "personal-data-{$usernameSlug}.zip";
    }
}
