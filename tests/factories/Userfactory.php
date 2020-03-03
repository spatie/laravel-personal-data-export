<?php

use Faker\Generator as Faker;
use Spatie\PersonalDataExport\Tests\TestClasses\User;

$factory->define(User::class, function (Faker $faker) {
    return [
        'username' => $faker->userName,
        'email' => $faker->unique()->safeEmail,
    ];
});
