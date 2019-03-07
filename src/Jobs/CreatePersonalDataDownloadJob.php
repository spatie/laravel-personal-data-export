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
use Spatie\PersonalDataDownload\Mail\PersonalDataDownloadCreatedMail;

class CreatePersonalDataDownloadJob implements ShouldQueue
{
    /** @var \Illuminate\Database\Eloquent\Model */
    protected $user;

    public function __construct(Model $user)
    {
        $this->user = $user;
    }

    public function handle()
    {
        $temporaryDirectory = (new TemporaryDirectory())->create();

        $personalData = (new PersonalData($temporaryDirectory))->forUser($this->user);

        $this->user->selectPersonalData($personalData);

        $zipFilename = $this->zipAndUploadPersonalData($personalData, $this->getDisk(), $temporaryDirectory);

        $temporaryDirectory->delete();

        Mail::to($this->user)->send(new PersonalDataDownloadCreatedMail($zipFilename));
    }

    protected function selectPersonalData(TemporaryDirectory $temporaryDirectory): PersonalData
    {
        $personalData = new PersonalData($temporaryDirectory);

        $this->user->selectPersonalData($personalData);

        return $personalData;
    }

    protected function zipAndUploadPersonalData(
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
}
