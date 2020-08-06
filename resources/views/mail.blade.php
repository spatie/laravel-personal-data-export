@component('mail::message')
# Personal data download

You can now download a zip file containing all data we got for your account!

@component('mail::button', ['url' => \Illuminate\Support\Facades\URL::temporarySignedRoute('personal-data-exports', $deletionDatetime, ['zipFilename' => $zipFilename])])
Download zip file
@endcomponent

This file will be deleted at {{ $deletionDatetime->format('Y-m-d H:i:s') }}

Thanks,
{{ config('app.name') }}
@endcomponent
