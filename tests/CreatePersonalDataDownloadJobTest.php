<?php

namespace Spatie\PersonalDataDownload\Tests;

use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Spatie\PersonalDataDownload\Exceptions\InvalidUser;
use Spatie\PersonalDataDownload\Tests\TestClasses\InvalidUser as InvalidUserModel;
use Spatie\PersonalDataDownload\Tests\TestClasses\User;
use Spatie\PersonalDataDownload\Jobs\CreatePersonalDataDownloadJob;
use Spatie\PersonalDataDownload\Mail\PersonalDataDownloadCreatedMail;

class CreatePersonalDataDownloadJobTest extends TestCase
{
    /** @var string */
    protected $diskName;

    public function setUp(): void
    {
        parent::setUp();

        Storage::fake($this->diskName);

        Mail::fake();
    }

    /** @test */
    public function it_can_create_zip_file_with_all_personal_data_and_mail_a_link_to_it()
    {
        $user = factory(User::class)->create();

        dispatch(new CreatePersonalDataDownloadJob($user));

        $allFiles = Storage::disk('personal-data-downloads')->allFiles();
        $this->assertCount(1, $allFiles);

        $zipPath = $this->getFullPath($this->diskName, $allFiles[0]);
        $this->assertZipContains($zipPath, 'attributes.json', json_encode($user->attributesToArray()));
        $this->assertZipContains($zipPath, 'avatar.png');

        Mail::assertSent(PersonalDataDownloadCreatedMail::class, function (PersonalDataDownloadCreatedMail $mail) use ($allFiles, $user) {
            if (! $mail->hasTo($user->email)) {
                return false;
            }

            if ($mail->zipFilename !== $allFiles[0]) {
                return false;
            }

            return true;
        });
    }

    /** @test */
    public function it_will_fail_if_the_model_does_not_have_a_selectsPersonalData_method()
    {
        $invalidUser = InvalidUserModel::create(['email' => 'john@example.com']);

        $this->expectException(InvalidUser::class);

        dispatch(new CreatePersonalDataDownloadJob($invalidUser));
    }

    public function getFullPath(string $diskName, string $filename): string
    {
        return Storage::disk($diskName)->getDriver()->getAdapter()->getPathPrefix().'/'.$filename;
    }
}
