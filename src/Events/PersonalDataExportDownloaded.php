<?php

namespace Spatie\PersonalDataExport\Events;

use Spatie\PersonalDataExport\ExportsPersonalData;

class PersonalDataExportDownloaded
{
    /** @var string */
    public $zipFilename;

    /** @var \Spatie\PersonalDataExport\ExportsPersonalData|null */
    public $user;

    public function __construct(string $zipFilename, ?ExportsPersonalData $user)
    {
        $this->zipFilename = $zipFilename;

        $this->user = $user;
    }
}
