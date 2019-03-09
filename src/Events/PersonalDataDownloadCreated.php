<?php

namespace Spatie\PersonalDataDownload\Events;

use Illuminate\Database\Eloquent\Model;

class PersonalDataDownloadCreated
{
    /** @var string */
    public $zipFilename;

    /** @var \Illuminate\Database\Eloquent\Model */
    public $user;

    public function __construct(string $zipFilename, Model $user)
    {
        $this->zipFilename = $zipFilename;

        $this->user = $user;
    }
}