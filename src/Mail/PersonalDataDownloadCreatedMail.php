<?php

namespace Spatie\PersonalDataDownload\Mail;

use Illuminate\Mail\Mailable;

class PersonalDataDownloadCreatedMail extends Mailable
{
    /** @var string */
    public $zipFilename;

    public function __construct(string $zipFilename)
    {
        $this->zipFilename = $zipFilename;
    }

    public function build()
    {
        return $this->markdown('personal-data-download::mail');
    }
}
