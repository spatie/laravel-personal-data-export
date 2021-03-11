<?php

namespace Spatie\PersonalDataExport\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Spatie\PersonalDataExport\Events\PersonalDataExportCreated;
use Spatie\PersonalDataExport\Events\PersonalDataSelected;
use Spatie\PersonalDataExport\Exceptions\InvalidUser;
use Spatie\PersonalDataExport\ExportsPersonalData;
use Spatie\PersonalDataExport\PersonalDataSelection;
use Spatie\PersonalDataExport\Zip;
use Spatie\TemporaryDirectory\TemporaryDirectory;

class CreatePersonalDataExportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected ExportsPersonalData | Model $user;

    public function __construct(ExportsPersonalData $user)
    {
        $this->ensureValidUser($user);

        $this->user = $user;

        $this->queue = config('personal-data-export.job.queue');

        $this->connection = config('personal-data-export.job.connection');
    }

    public function handle()
    {
        $temporaryDirectory = (new TemporaryDirectory())->create();

        $personalDataSelection = $this->selectPersonalData($temporaryDirectory);

        event(new PersonalDataSelected($personalDataSelection, $this->user));

        $zipFilename = $this->zipPersonalData($personalDataSelection, $this->getDisk(), $temporaryDirectory);

        $temporaryDirectory->delete();

        event(new PersonalDataExportCreated($zipFilename, $this->user));

        $this->notifyZip($zipFilename);
    }

    protected function selectPersonalData(TemporaryDirectory $temporaryDirectory): PersonalDataSelection
    {
        $personalData = (new PersonalDataSelection($temporaryDirectory))->forUser($this->user);

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

    protected function notifyZip(string $zipFilename)
    {
        $notificationClass = config('personal-data-export.notification');

        $this->user->notify(new $notificationClass($zipFilename));
    }

    protected function ensureValidUser(ExportsPersonalData $user)
    {
        if (is_null($user->email ?? null)) {
            throw InvalidUser::doesNotHaveAnEmailProperty($user);
        }
    }
}
