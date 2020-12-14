<?php

namespace Spatie\PersonalDataExport\Events;

use Spatie\PersonalDataExport\ExportsPersonalData;

class PersonalDataExportDownloaded
{
    public function __construct(
        string $zipFilename,
        ?ExportsPersonalData $user
    ) {}
}
