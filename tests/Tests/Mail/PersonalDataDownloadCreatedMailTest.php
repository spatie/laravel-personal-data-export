<?php

namespace Spatie\PersonalDataExport\Tests\Tests\Mail;

use Spatie\PersonalDataExport\Mail\PersonalDataExportCreatedMail;
use Spatie\PersonalDataExport\Tests\TestCase;

class PersonalDataDownloadCreatedMailTest extends TestCase
{
    /** @test */
    public function the_personal_data_download_created_mail_can_be_rendered_to_a_string()
    {
        $zipFilename = 'personal-data.zip';

        $renderedMail = (new PersonalDataExportCreatedMail($zipFilename))->render();

        $this->assertTrue(is_string($renderedMail));
    }
}
