<?php

return [
    /*
     * The class that holds personal data. In a vanilla Laravel app
     * a valid value would be `App\User::class`.
     */
    'personal_data_source' => '',

    /*
     * The disk where the downloads will be stored by default.
     */
    'disk' => 'personal-data-downloads',

    /*
     * The amount of days the gdpr downloads will be available.
     */
    'delete_after_days' => 30,

    /*
     * Determines wheter the user should be logged in to be able
     * to access the gdpr download.
     */
    'authentication_required' => true,

    /*
     * The mailable which will be sent to the user when the gdpr download
     * has been created.
     */
    'mailable' => \Spatie\PersonalDataDownload\PersonalDataDownloadCreatedMail::class,
];