<?php

namespace Spatie\PersonalDataExport\Events;

use Spatie\PersonalDataExport\ExportsPersonalData;

class PersonalDataExportCreated
{
    /** @var string */
    public $zipFilename;

    /** @var \Spatie\PersonalDataExport\ExportsPersonalData */
    public $user;

    public function __construct(string $zipFilename, ExportsPersonalData $user)
    {
        $this->zipFilename = $zipFilename;

        $this->user = $user;
    }
}
