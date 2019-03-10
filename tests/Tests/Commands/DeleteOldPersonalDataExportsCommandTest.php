<?php

namespace Spatie\PersonalDataExport\Tests\Tests\Commands;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Spatie\PersonalDataExport\Tests\TestCase;
use Spatie\PersonalDataExport\Tests\TestClasses\User;
use Spatie\PersonalDataExport\Jobs\CreatePersonalDataExportJob;

class DeleteOldPersonalDataExportsCommandTest extends TestCase
{
    /** @var \Illuminate\Contracts\Filesystem\Filesystem */
    protected $disk;

    public function setUp(): void
    {
        parent::setUp();

        Storage::fake($this->diskName);

        $this->disk = Storage::disk($this->diskName);

        Mail::fake();
    }

    /** @test */
    public function it_will_delete_zips_that_are_older_than_the_configured_amount_of_days()
    {
        $zipFile = $this->createPersonalDataExport();

        $this->artisan('personal-data-export:clean')->assertExitCode(0);
        $this->assertTrue($this->disk->exists($zipFile));

        $this->progressDays(config('personal-data-export.delete_after_days'));
        $this->artisan('personal-data-export:clean')->assertExitCode(0);
        $this->assertTrue($this->disk->exists($zipFile));

        $this->progressDays(1);
        $this->artisan('personal-data-export:clean')->assertExitCode(0);
        $this->assertFalse($this->disk->exists($zipFile));
    }

    /** @test */
    public function it_will_not_delete_any_other_files()
    {
        $this->disk->put('my-file', 'my contents');

        $this->artisan('personal-data-export:clean')->assertExitCode(0);
        $this->assertTrue($this->disk->exists('my-file'));

        $this->progressDays(100);
        $this->artisan('personal-data-export:clean')->assertExitCode(0);
        $this->assertTrue($this->disk->exists('my-file'));
    }

    protected function createPersonalDataExport(): string
    {
        $user = factory(User::class)->create();

        dispatch(new CreatePersonalDataExportJob($user));

        $allFiles = Storage::disk($this->diskName)->allFiles();

        return Arr::last($allFiles);
    }
}
