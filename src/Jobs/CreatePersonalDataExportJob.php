<?php

namespace Spatie\PersonalDataExport\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Spatie\PersonalDataExport\Events\PersonalDataExportCreated;
use Spatie\PersonalDataExport\Events\PersonalDataSelected;
use Spatie\PersonalDataExport\Exceptions\InvalidUser;
use Spatie\PersonalDataExport\ExportsPersonalData;
use Spatie\PersonalDataExport\PersonalDataSelection;
use Spatie\PersonalDataExport\Zip;
use Spatie\TemporaryDirectory\TemporaryDirectory;

/**
 * @method static \Illuminate\Foundation\Bus\PendingDispatch dispatch(\Spatie\PersonalDataExport\ExportsPersonalData $user)
 * @method static \Illuminate\Foundation\Bus\PendingDispatch|\Illuminate\Support\Fluent dispatchIf(bool $condition, \Spatie\PersonalDataExport\ExportsPersonalData $user)
 * @method static \Illuminate\Foundation\Bus\PendingDispatch|\Illuminate\Support\Fluent dispatchUnless(bool $condition, \Spatie\PersonalDataExport\ExportsPersonalData $user)
 * @method static mixed dispatchNow(\Spatie\PersonalDataExport\ExportsPersonalData $user)
 * @method static void dispatchAfterResponse(\Spatie\PersonalDataExport\ExportsPersonalData $user)
 */
class CreatePersonalDataExportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /** @var \Spatie\PersonalDataExport\ExportsPersonalData */
    protected $user;

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

        $this->mailZip($zipFilename);
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
