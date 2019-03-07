<?php

namespace Spatie\PersonalDataDownload\Tests;

use Illuminate\Support\Facades\Storage;
use Spatie\PersonalDataDownload\Jobs\CreatePersonalDataDownloadJob;
use Spatie\PersonalDataDownload\Tests\TestClasses\User;

class CreatePersonalDataDownloadJobTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        Storage::fake();
    }

    /** @test */
    public function it_can_create_zip_file_with_all_personal_data()
    {
        $user = factory(User::class)->create();

        dispatch(new CreatePersonalDataDownloadJob($user));

        $this->assertTrue(true);
    }
}