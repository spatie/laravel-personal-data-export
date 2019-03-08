<?php

namespace Spatie\PersonalDataDownload\Mail;

use Illuminate\Mail\Mailable;

class PersonalDataDownloadCreatedMail extends Mailable
{
    /** @var string */
    public $zipFilename;

    /** @var \Illuminate\Support\Carbon */
    public $deletionDatetime;

    /**
     * PersonalDataDownloadCreatedMail constructor.
     *
     * @param string $zipFilename
     */
    public function __construct(string $zipFilename)
    {
        $this->zipFilename = $zipFilename;

        $this->deletionDatetime = now()->addDays(config('personal-data-download.delete_after_days'));
    }

    public function build()
    {
        return $this->markdown('personal-data-download::mail');
    }
}
