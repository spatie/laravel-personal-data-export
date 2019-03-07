<?php

namespace Spatie\PersonalDataDownload\Jobs;

use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Mail;
use Spatie\PersonalDataDownload\Mail\PersonalDataDownloadCreatedMail;
use Spatie\PersonalDataDownload\PersonalData;
use Spatie\PersonalDataDownload\Zip;
use Spatie\TemporaryDirectory\TemporaryDirectory;

class CreatePersonalDataDownloadJob implements ShouldQueue
{
    /** @var \Illuminate\Database\Eloquent\Model */
    protected $user;

    public function __construct(Model $user)
    {
        $this->user = $user;
    }

    public function handle(Filesystem $filesystem)
    {
        $temporaryDirectory = (new TemporaryDirectory())->create();

        $personalData = (new PersonalData($temporaryDirectory))->forUser($this->user);

        $this->user->selectPersonalData($personalData);

        $zipFilename = $this->zipAndUploadPersonalData($personalData, $filesystem, $temporaryDirectory);

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
}