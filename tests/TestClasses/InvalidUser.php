<?php

namespace Spatie\PersonalDataDownload\Tests\TestClasses;

use Spatie\PersonalDataDownload\PersonalData;
use Illuminate\Foundation\Auth\User as BaseUser;

class InvalidUser extends BaseUser
{
    protected $guarded = [];

    protected $table = 'users';
}
