<?php

namespace Spatie\PersonalDataExport\Jobs;

use Illuminate\Support\Facades\Mail;
use Spatie\PersonalDataExport\ExportsPersonalData;
use Spatie\PersonalDataExport\Zip;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Contracts\Queue\ShouldQueue;
use Spatie\PersonalDataExport\PersonalDataSelection;
use Illuminate\Contracts\Filesystem\Filesystem;
use Spatie\TemporaryDirectory\TemporaryDirectory;
use Spatie\PersonalDataExport\Exceptions\InvalidUser;
use Spatie\PersonalDataExport\Events\PersonalDataSelected;
use Spatie\PersonalDataExport\Events\PersonalDataExportCreated;

class CreatePersonalDataExportJob implements ShouldQueue
{
    /** @var \Spatie\PersonalDataExport\ExportsPersonalData */
    protected $user;

    public function __construct(ExportsPersonalData $user)
    {
        $this->ensureValidUser($user);

        $this->user = $user;
    }

    public function handle()
    {
        $temporaryDirectory = (new TemporaryDirectory())->create();

        $personalDataSelection = (new PersonalDataSelection($temporaryDirectory))->forUser($this->user);

        $this->user->selectPersonalData($personalDataSelection);

        event(new PersonalDataSelected($personalDataSelection, $this->user));

        $zipFilename = $this->zipPersonalData($personalDataSelection, $this->getDisk(), $temporaryDirectory);

        $temporaryDirectory->delete();

        event(new PersonalDataExportCreated($zipFilename, $this->user));

        $this->mailZip($zipFilename);
    }

    protected function selectPersonalData(TemporaryDirectory $temporaryDirectory): PersonalDataSelection
    {
        $personalData = new PersonalDataSelection($temporaryDirectory);

        $this->user->selectPersonalData($personalData);

        return $personalData;
    }

    protected function zipPersonalData(
        PersonalDataSelection $personalData,
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
        return Storage::disk(config('personal-data-export.disk'));
    }

    protected function mailZip(string $zipFilename)
    {
        $mailableClass = config('personal-data-export.mailable');

        Mail::to($this->user)->send(new $mailableClass($zipFilename));
    }

    protected function ensureValidUser(ExportsPersonalData $user)
    {
        if (is_null($user->email ?? null)) {
            throw InvalidUser::doesNotHaveAnEmailProperty($user);
        }
    }
}
