<?php

namespace Spatie\PersonalDataDownload\Mail;

use Illuminate\Mail\Mailable;

class PersonalDataDownloadCreatedMail extends Mailable
{
    /** @var string */
    protected $zipFilename;

    public function __construct(string $zipFilename)
    {
        $this->zipFilename = $zipFilename;
    }

    public function build()
    {
        return $this->view('personal-data-download::mail', [
            'zipFilename' => $this->zipFilename,
        ]);
    }
}