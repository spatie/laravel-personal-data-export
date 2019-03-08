@component('mail::message')
    # Personal data download

    You can now download a zip file containing all data we got for your account!

    @component('mail::button', ['url' => route('personal-data-downloads', $zipFilename)])
        Download zip file
    @endcomponent

    This file will be deleted at {{ $deletionDatetime->format('Y-m-d H:i:s') }}

    Thanks,
    {{ config('app.name') }}
@endcomponent