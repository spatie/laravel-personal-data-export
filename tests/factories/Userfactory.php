<?php

use Faker\Generator as Faker;
use Spatie\PersonalDataDownload\Tests\TestClasses\User;

$factory->define(User::class, function (Faker $faker) {
    return [
        'email' => $faker->unique()->safeEmail,
    ];
});
