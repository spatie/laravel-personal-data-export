<?php

namespace Spatie\PersonalDataExport\Tests\Tests\Http\Controllers;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Spatie\PersonalDataExport\Events\PersonalDataExportDownloaded;
use Spatie\PersonalDataExport\Jobs\CreatePersonalDataExportJob;
use Spatie\PersonalDataExport\Tests\TestCase;
use Spatie\PersonalDataExport\Tests\TestClasses\User;
use Symfony\Component\HttpFoundation\Response;

class PersonalDataExportsControllerTest extends TestCase
{
    /** @var \Illuminate\Foundation\Auth\User */
    protected $user;

    /** @var string */
    protected $downloadUrl;

    public function setUp(): void
    {
        parent::setUp();

        Storage::fake($this->diskName);

        $this->user = factory(User::class)->create();

        $zipFilename = $this->createPersonalDataExport($this->user);

        $this->downloadUrl = route('personal-data-exports', $zipFilename);

        Mail::fake();

        Event::fake();
    }

    /** @test */
    public function it_can_download_the_personal_data_download()
    {
        $this->withoutExceptionHandling();

        $this
            ->actingAs($this->user)
            ->get($this->downloadUrl)
            ->assertSuccessful();

        Event::assertDispatched(PersonalDataExportDownloaded::class);
    }

    /** @test */
    public function it_cannot_download_personal_data_from_other_users()
    {
        $anotherUser = factory(User::class)->create();

        $this
            ->actingAs($anotherUser)
            ->get($this->downloadUrl)
            ->assertStatus(Response::HTTP_UNAUTHORIZED);

        Event::assertNotDispatched(PersonalDataExportDownloaded::class);
    }

    /** @test */
    public function guests_cannot_download_personal_data_by_default()
    {
        $this
            ->get($this->downloadUrl)
            ->assertStatus(Response::HTTP_FORBIDDEN);

        Event::assertNotDispatched(PersonalDataExportDownloaded::class);
    }

    /** @test */
    public function guests_can_download_personal_data_if_the_authentication_is_turned_off()
    {
        config()->set('personal-data-export.authentication_required', false);

        $this
            ->get($this->downloadUrl)
            ->assertSuccessful();

        Event::assertDispatched(PersonalDataExportDownloaded::class);
    }

    /** @test */
    public function it_returns_a_404_for_zipfiles_that_dont_exists()
    {
        $this
            ->actingAs($this->user)
            ->get($this->downloadUrl.'invalid')
            ->assertStatus(Response::HTTP_NOT_FOUND);

        Event::assertNotDispatched(PersonalDataExportDownloaded::class);
    }

    protected function createPersonalDataExport(User $user): string
    {
        dispatch(new CreatePersonalDataExportJob($user));

        $allFiles = Storage::disk($this->diskName)->allFiles();

        return Arr::last($allFiles);
    }
}
