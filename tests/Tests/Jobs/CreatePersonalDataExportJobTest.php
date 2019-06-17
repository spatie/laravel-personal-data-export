<?php

namespace Spatie\PersonalDataExport\Tests\Tests\Jobs;

use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Storage;
use Spatie\PersonalDataExport\Tests\TestCase;
use Spatie\PersonalDataExport\Tests\TestClasses\User;
use Spatie\PersonalDataExport\Events\PersonalDataSelected;
use Spatie\PersonalDataExport\Tests\TestClasses\InvalidUser;
use Spatie\PersonalDataExport\Events\PersonalDataExportCreated;
use Spatie\PersonalDataExport\Jobs\CreatePersonalDataExportJob;
use Spatie\PersonalDataExport\Mail\PersonalDataExportCreatedMail;
use Spatie\PersonalDataExport\Exceptions\InvalidUser as InvalidUserException;

class CreatePersonalDataExportJobTest extends TestCase
{
    /** @var string */
    protected $diskName;

    public function setUp(): void
    {
        parent::setUp();

        Storage::fake($this->diskName);

        Mail::fake();

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

        Mail::assertSent(PersonalDataExportCreatedMail::class, function (PersonalDataExportCreatedMail $mail) use ($allFiles, $user) {
            if (! $mail->hasTo($user->email)) {
                return false;
            }

            if ($mail->zipFilename !== $allFiles[0]) {
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

    public function getFullPath(string $diskName, string $filename): string
    {
        return Storage::disk($diskName)->getDriver()->getAdapter()->getPathPrefix().'/'.$filename;
    }
}
