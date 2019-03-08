@component('mail::message')
    # Personal data download

    Your can now download a zip file containing all data we got for your account!

    @component('mail::button', ['url' => route('personal-data-downloads', $zipFilename)])
        Download zip file
    @endcomponent

    Thanks,<br>
    {{ config('app.name') }}
@endcomponent