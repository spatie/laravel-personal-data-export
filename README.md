**THIS PACKAGE IS IN DEVELOPMENT, DO NOT USE YET**

# Create personal data downloads in a Laravel app

[![Latest Version on Packagist](https://img.shields.io/packagist/v/spatie/personal-data-download.svg?style=flat-square)](https://packagist.org/packages/spatie/personal-data-download)
[![Build Status](https://img.shields.io/travis/spatie/personal-data-download/master.svg?style=flat-square)](https://travis-ci.org/spatie/personal-data-download)
[![StyleCI](https://github.styleci.io/repos/174338628/shield?branch=master)](https://github.styleci.io/repos/174338628)
[![Code coverage](https://scrutinizer-ci.com/g/spatie/personal-data-download/badges/coverage.png)](https://scrutinizer-ci.com/g/spatie/personal-data-download)
[![Quality Score](https://img.shields.io/scrutinizer/g/spatie/personal-data-download.svg?style=flat-square)](https://scrutinizer-ci.com/g/spatie/personal-data-download)
[![Total Downloads](https://img.shields.io/packagist/dt/spatie/personal-data-download.svg?style=flat-square)](https://packagist.org/packages/spatie/personal-data-download)

This package makes it easy to let a user download all personal data. Such a download consists of a zip file containing all user properties and related info.

You can create and mail such a zip by dispatching the `CreatePersonalDataDownloadJob` job:

```php
// somewhere in your app

use Spatie\PersonalDataDownload\Jobs\CreatePersonalDataDownloadJob;

// ...

dispatch(new CreatePersonalDataDownloadJob(auth()->user());
```

The package will create a zip containing all personal data. When the zip has been created a link to it will be mailed to the user. By default the zips are saved in a non public location and the user should be logged in to be able to download the zip.

You can configure which data will be put in the download in the `selectPersonalData` method on the `user`.

```php
// in your User model

public function selectPersonalData(PersonalData $personalData) {
    $personalData
        ->add('user.json', ['name' => $this->name, 'email' => $this->email])
        ->addFile(storage_path("avatars/{$this->id}.jpg");
        ->addFile('other-user-data.xml', 's3');
}
```

This package also offers an artisan command to remove old zip files.

## Installation

You can install the package via composer:

```bash
composer require spatie/personal-data-download
```

You need to use this macro in your routes file. It 'll register a route where users can download their personal data downloads.

```php
// in your routes file

Route::personalDataDownloads('personal-data-downloads');
```

You must add a disk named `personal-data-downloads` to `config/filesystems` (the name of the disk can be configured in `config/personal-data-download`. You can use any driver that you want. We recommend that your disk is not publicly accessible. If you're use the `local` driver, make sure you use a but that is not inside the public path of your app.

```php
// in config/filesystems.php

// ...

'disks' => [

    'personal-data-downloads' => [
        'driver' => 'local',
        'root' => storage_path('app/personal-data-downloads'),
    ],

// ...
```

To automatically clean up older personal data downloads, you can schedule this command in your console kernel:

```php
// app/Console/Kernel.php

protected function schedule(Schedule $schedule)
{
   $schedule->command('personal-data-download:clean')->daily();
}
```

Optionally you can publish the config file with:

```php
php artisan vendor:publish --provider="EventSauce\LaravelEventSauce\EventSauceServiceProvider" --tag="config"
```

This is the content of the config file, which will be published at `config/personal-data-download.php`:

```php
return [
    /*
     * The disk where the downloads will be stored by default.
     */
    'disk' => 'personal-data-downloads',

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
     * The mailable which will be sent to the user when the personal 
     * data download has been created.
     */
    'mailable' => \Spatie\PersonalDataDownload\Mail\PersonalDataDownloadCreatedMail::class,
];
```

Optionally you can publish the view used by the mail with:

```php
php artisan vendor:publish --provider="EventSauce\LaravelEventSauce\EventSauceServiceProvider" --tag="views"
```

This will create a file under `views/vendor/laravel-personal-data-download/mail.blade.php` that you can customize.

## Usage

### Selecting personal data

First you'll have to preperate your user model. You should add a `selectPersonalData` function which accepts an instance of `Spatie\PersonalDataDownload\PersonalData`.

```php
// in your user model

public function selectPersonalData(PersonalData $personalData) {
    $personalData
        ->add('user.json', ['name' => $this->name, 'email' => $this->email])
        ->addFile(storage_path("avatars/{$this->id}.jpg");
        ->addFile('other-user-data.xml', 's3');
}
```

`$personalData` is used to determine the content of the zip file that the user will be able to download. You can call these methods on it:

- `add`: the first parameter is the name of the file in the inside te zipfile. The second parameter is the content that should go in that file. If you pass an array here, we will encode it to json.
- `addFile`: the first parameter is a path to a file which will be copied to the zip. You can also add a disk name as the second parameter.

### Creating a download

You can create a personal data download by executing this job somewhere in your application:

```php
// somewhere in your app

use Spatie\PersonalDataDownload\Jobs\CreatePersonalDataDownloadJob;

// ...

dispatch(new CreatePersonalDataDownloadJob(auth()->user());
```

By default this job is queued. It will copy all files and content you selected in the `selectPersonalData` on your user to a temporary directory. Next that temporary directory will be zipped and copied over to the `personal-data-downloads` disk. A link to this zip will be mailed to the user. 

### Securing the download

We recommend that the `personal-data-downloads` is not publicly accessible. If you're use the `local` driver for this disk, make sure you use a but that is not inside the public path of your app.

When the user click the downoad link in the mail that gets send after creating the personal download, a request will be sent to underlying `PersonalDataDownloadController`. This controller will check if there is a user logged in and if the request personal data zip belongs to the user. If this is the case, that controller will stream the zip to the user.

If you don't want to enforce that a user should be logged in to able to download a personal data zip, you can set the `authentication_required` config value to `false`. Setting the value to `false` is less secure because anybody with a link to a zip file will be able to download it, but because the name of the zip file contains many random characters, it will be hard to just guess it.

### Customizing the mail

You can customize mail by [publishing the views](TODO: addl link) and editing `views/vendor/laravel-personal-data-download/mail.blade.php`

You can also customize the mailable it self by creating your own mailable that extends `\Spatie\PersonalDataDownload\Mail\PersonalDataDownloadCreatedMail` and register the class name of your mailable in the `mailable` config key of `config/personal-data-download.php`.

### Customizing the queue

You can customize the job that creates the zip file and mails it by extending the `Spatie\PersonalDataDownload\Jobs\CreatePersonalDataDownloadJob` and dispatching your own custom job class.

```php
use Spatie\PersonalDataDownload\Jobs\CreatePersonalDataDownloadJob;

class MyCustomJobClass extends CreatePersonalDataDownloadJob
{
    public $queue = 'my-custom-queue`
}
```

```php
dispatch(new MyCustomJobClass(auth()->user());
```

### Testing

You can run all tests by issueing this command:

``` bash
composer test
```

### Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

### Security

If you discover any security related issues, please email freek@spatie.be instead of using the issue tracker.

## Postcardware

You're free to use this package, but if it makes it to your production environment we highly appreciate you sending us a postcard from your hometown, mentioning which of our package(s) you are using.

Our address is: Spatie, Samberstraat 69D, 2060 Antwerp, Belgium.

We publish all received postcards [on our company website](https://spatie.be/en/opensource/postcards).

## Credits

- [Freek Van der Herten](https://github.com/freekmurze)
- [All Contributors](../../contributors)

## Support us

Spatie is a webdesign agency based in Antwerp, Belgium. You'll find an overview of all our open source projects [on our website](https://spatie.be/opensource).

Does your business depend on our contributions? Reach out and support us on [Patreon](https://www.patreon.com/spatie). 
All pledges will be dedicated to allocating workforce on maintenance and new awesome stuff.

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
