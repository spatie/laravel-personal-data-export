<?php

namespace Spatie\PersonalDataDownload\Events;

use Illuminate\Database\Eloquent\Model;

class PersonalDataHasBeenDownloaded
{
    /** @var string */
    protected $zipFilename;

    /** @var \Spatie\PersonalDataDownload\Events\Model|null */
    protected $user;

    public function __construct(string $zipFilename, ?Model $user)
    {
        $this->zipFilename = $zipFilename;

        $this->user = $user;
    }
}
