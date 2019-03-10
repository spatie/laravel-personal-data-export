<?php

namespace Spatie\PersonalDataExport\Events;

use Illuminate\Database\Eloquent\Model;
use Spatie\PersonalDataExport\ExportsPersonalData;

class PersonalDataExportDownloaded
{
    /** @var string */
    protected $zipFilename;

    /** @var \Spatie\PersonalDataExport\ExportsPersonalData|null */
    protected $user;

    public function __construct(string $zipFilename, ?ExportsPersonalData $user)
    {
        $this->zipFilename = $zipFilename;

        $this->user = $user;
    }
}
