<?php

namespace Spatie\PersonalDataDownload\Jobs;

use Illuminate\Support\Facades\Mail;
use Spatie\PersonalDataDownload\Zip;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Contracts\Queue\ShouldQueue;
use Spatie\PersonalDataDownload\PersonalData;
use Illuminate\Contracts\Filesystem\Filesystem;
use Spatie\TemporaryDirectory\TemporaryDirectory;
use Spatie\PersonalDataDownload\Exceptions\InvalidUser;
use Spatie\PersonalDataDownload\Events\PersonalDataSelected;
use Spatie\PersonalDataDownload\Events\PersonalDataDownloadCreated;

class CreatePersonalDataDownloadJob implements ShouldQueue
{
    /** @var \Illuminate\Database\Eloquent\Model */
    protected $user;

    public function __construct(Model $user)
    {
        $this->ensureValidUser($user);

        $this->user = $user;
    }

    public function handle()
    {
        $temporaryDirectory = (new TemporaryDirectory())->create();

        $personalData = (new PersonalData($temporaryDirectory))->forUser($this->user);

        $this->user->selectPersonalData($personalData);

        event(new PersonalDataSelected($personalData, $this->user));

        $zipFilename = $this->zipPersonalData($personalData, $this->getDisk(), $temporaryDirectory);

        $temporaryDirectory->delete();

        event(new PersonalDataDownloadCreated($zipFilename, $this->user));

        $this->mailZip($zipFilename);
    }

    protected function selectPersonalData(TemporaryDirectory $temporaryDirectory): PersonalData
    {
        $personalData = new PersonalData($temporaryDirectory);

        $this->user->selectPersonalData($personalData);

        return $personalData;
    }

    protected function zipPersonalData(
        PersonalData $personalData,
        Filesystem $filesystem,
        TemporaryDirectory $temporaryDirectory
    ): string {
        $zip = Zip::createForPersonalData($personalData, $temporaryDirectory);

        $zipFilename = pathinfo($zip->path(), PATHINFO_BASENAME);

        $filesystem->writeStream($zipFilename, fopen($zip->path(), 'r'));

        return $zipFilename;
    }

    public function getDisk(): Filesystem
    {
        return Storage::disk(config('personal-data-download.disk'));
    }

    protected function mailZip(string $zipFilename)
    {
        $mailableClass = config('personal-data-download.mailable');

        Mail::to($this->user)->send(new $mailableClass($zipFilename));
    }

    protected function ensureValidUser(Model $user)
    {
        if (! method_exists($user, 'selectPersonalData')) {
            throw InvalidUser::doesNotHaveSelectPersonalDataMethod($user);
        }
    }
}
