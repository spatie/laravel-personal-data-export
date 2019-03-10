<?php

return [
    /*
     * The disk where the downloads will be stored by default.
     */
    'disk' => 'personal-data-exports',

    /*
     * The amount of days the gdpr downloads will be available.
     */
    'delete_after_days' => 5,

    /*
     * Determines wheter the user should be logged in to be able
     * to access the gdpr download.
     */
    'authentication_required' => true,

    /*
     * The mailable which will be sent to the user when the gdpr download
     * has been created.
     */
    'mailable' => \Spatie\PersonalDataExport\Mail\PersonalDataExportCreatedMail::class,
];
