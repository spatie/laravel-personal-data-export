<?php

namespace Spatie\PersonalDataDownload\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CleanOldPersonalDataDownloadsCommand extends Command
{
    protected $signature = 'personal-data-download:clean';

    protected $description = 'Remove old personal downloads';

    public function handle()
    {
        $this->comment('Start deleting old personal downloads...');

        $oldZipFiles = collect($this->getDisk()->allFiles())
            ->filter(function(string $zipFilename) {
                return Str::endsWith($zipFilename, '.zip');
            })
            ->filter(function(string $zipFilename) {
                $zipFilenameParts = explode('-', $zipFilename);

                if (! isset($zipFilenameParts[1])) {
                    return false;
                }

                $dateCreated = Carbon::createFromTimestamp($zipFilenameParts[1]);

                $threshold = now()->subDays(config('personal-data-download.delete_after_days'));

                return $dateCreated->isBefore($threshold);
            })
            ->toArray();

        $this->getDisk()->delete($oldZipFiles);

        $this->comment(count($oldZipFiles) . ' old zip files have been deleted.');
        $this->info('All done!');
    }

    protected function getDisk(): Filesystem
    {
        return Storage::disk(config('personal-data-download.disk'));
    }
}