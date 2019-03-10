<?php

return [
    /*
     * The disk where the exports will be stored by default.
     */
    'disk' => 'personal-data-exports',

    /*
     * The amount of days the exports will be available.
     */
    'delete_after_days' => 5,

    /*
     * Determines wheter the user should be logged in to be able
     * to access the export.
     */
    'authentication_required' => true,

    /*
     * The mailable which will be sent to the user when the export
     * has been created.
     */
    'mailable' => \Spatie\PersonalDataExport\Mail\PersonalDataExportCreatedMail::class,
];
