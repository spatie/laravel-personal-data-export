<?php

namespace Spatie\PersonalDataDownload\Events;

use Illuminate\Database\Eloquent\Model;

class PersonalDataDownloadCreated
{
    /** @var string */
    protected $zipFilename;

    /** @var \Illuminate\Database\Eloquent\Model */
    protected $user;

    public function __construct(string $zipFilename, Model $user)
    {
        $this->zipFilename = $zipFilename;

        $this->user = $user;
    }
}