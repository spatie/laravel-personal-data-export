<?php

namespace Spatie\PersonalDataExport\Mail;

use Illuminate\Mail\Mailable;

class PersonalDataExportCreatedMail extends Mailable
{
    /** @var string */
    public $zipFilename;

    /** @var \Illuminate\Support\Carbon */
    public $deletionDatetime;

    /**
     * PersonalDataExportCreatedMail constructor.
     *
     * @param string $zipFilename
     */
    public function __construct(string $zipFilename)
    {
        $this->zipFilename = $zipFilename;

        $this->deletionDatetime = now()->addDays(config('personal-data-export.delete_after_days'));
    }

    public function build()
    {
        return $this->markdown('personal-data-export::mail');
    }
}
