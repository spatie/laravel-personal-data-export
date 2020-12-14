<?php

namespace Spatie\PersonalDataExport\Events;

use Spatie\PersonalDataExport\ExportsPersonalData;

class PersonalDataExportCreated
{
    public function __construct(
        public string $zipFilename,
        public ExportsPersonalData $user
    ) {}
}
