<?php

namespace Spatie\PersonalDataExport\Tests\Tests\Jobs;

use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Spatie\PersonalDataExport\Events\PersonalDataExportCreated;
use Spatie\PersonalDataExport\Events\PersonalDataSelected;
use Spatie\PersonalDataExport\Exceptions\InvalidUser as InvalidUserException;
use Spatie\PersonalDataExport\Jobs\CreatePersonalDataExportJob;
use Spatie\PersonalDataExport\Notifications\PersonalDataExportedNotification;
use Spatie\PersonalDataExport\Tests\TestCase;
use Spatie\PersonalDataExport\Tests\TestClasses\InvalidUser;
use Spatie\PersonalDataExport\Tests\TestClasses\User;

class CreatePersonalDataExportJobTest extends TestCase
{
    protected string $diskName;

    public function setUp(): void
    {
        parent::setUp();

        Storage::fake($this->diskName);

        Notification::fake();

        Event::fake();
    }

    /** @test */
    public function it_can_create_zip_file_with_all_personal_data_and_mail_a_link_to_it()
    {
        $user = factory(User::class)->create();

        dispatch(new CreatePersonalDataExportJob($user));

        $allFiles = Storage::disk('personal-data-exports')->allFiles();
        $this->assertCount(1, $allFiles);

        $zipPath = $this->getFullPath($this->diskName, $allFiles[0]);
        $this->assertZipContains($zipPath, 'attributes.json', json_encode($user->attributesToArray(), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
        $this->assertZipContains($zipPath, 'avatar.png');
        $this->assertZipContains($zipPath, 'thumbnail.png');

        Notification::assertSentTo($user, PersonalDataExportedNotification::class, function (PersonalDataExportedNotification $notification) use ($allFiles, $user) {
            if ($notification->zipFilename !== $allFiles[0]) {
                return false;
            }

            return true;
        });

        Event::assertDispatched(PersonalDataSelected::class);
        Event::assertDispatched(PersonalDataExportCreated::class);
    }

    /** @test */
    public function it_will_fail_if_the_model_does_not_have_an_email()
    {
        $invalidUser = new InvalidUser();

        $this->expectException(InvalidUserException::class);

        dispatch(new CreatePersonalDataExportJob($invalidUser));
    }

    protected function getFullPath(string $diskName, string $filename): string
    {
        $root = Storage::disk($diskName)->getConfig()['root'];

        return $root.'/'.$filename;
    }
}
