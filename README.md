# Create zip files containing personal data

[![Latest Version on Packagist](https://img.shields.io/packagist/v/spatie/laravel-personal-data-export.svg?style=flat-square)](https://packagist.org/packages/spatie/laravel-personal-data-export)
[![Build Status](https://img.shields.io/travis/spatie/laravel-personal-data-export/master.svg?style=flat-square)](https://travis-ci.org/spatie/laravel-personal-data-export)
[![StyleCI](https://github.styleci.io/repos/174338628/shield?branch=master)](https://github.styleci.io/repos/174338628)
[![Quality Score](https://img.shields.io/scrutinizer/g/spatie/laravel-personal-data-export.svg?style=flat-square)](https://scrutinizer-ci.com/g/spatie/laravel-personal-data-export)
[![Total Downloads](https://img.shields.io/packagist/dt/spatie/laravel-personal-data-export.svg?style=flat-square)](https://packagist.org/packages/spatie/laravel-personal-data-export)

This package makes it easy to let a user download an export containing all the personal data. Such an export consists of a zip file containing all the user properties and related info.

You can create and mail such a zip by dispatching the `CreatePersonalDataExportJob` job:

```php
// somewhere in your app

use Spatie\PersonalDataExport\Jobs\CreatePersonalDataExportJob;

// ...

dispatch(new CreatePersonalDataExportJob(auth()->user());
```

The package will create a zip containing all the personal data. When the zip has been created, a link to it will be mailed to the user. By default, the zips are saved in a non-public location, and the user should be logged in to be able to download the zip.

You can configure which data will will be exported in the `selectPersonalData` method on the `user`.

```php
// in your User model

public function selectPersonalData(PersonalDataSelection $personalDataSelection) {
    $personalDataSelection
        ->add('user.json', ['name' => $this->name, 'email' => $this->email])
        ->addFile(storage_path("avatars/{$this->id}.jpg")
        ->addFile('other-user-data.xml', 's3'));
}
```

This package also offers an artisan command to remove old zip files.

## Installation

You can install the package via composer:

```bash
composer require spatie/laravel-personal-data-export
```

You need to use this macro in your routes file. It'll register a route where users can download their personal data exports.

```php
// in your routes file

Route::personalDataExports('personal-data-exports');
```

You must add a disk named `personal-data-exports` to `config/filesystems` (the name of the disk can be configured in `config/personal-data-export`). You can use any driver that you want. We recommend that your disk is not publicly accessible. If you're using the `local` driver, make sure you use a path that is not inside the public path of your app.

```php
// in config/filesystems.php

// ...

'disks' => [

    'personal-data-exports' => [
        'driver' => 'local',
        'root' => storage_path('app/personal-data-exports'),
    ],

// ...
```

To automatically clean up older personal data exports, you can schedule this command in your console kernel:

```php
// app/Console/Kernel.php

protected function schedule(Schedule $schedule)
{
   $schedule->command('personal-data-export:clean')->daily();
}
```

Optionally, you can publish the config file with:

```php
php artisan vendor:publish --provider="Spatie\PersonalDataExport\PersonalDataExportServiceProvider" --tag="config"
```

This is the content of the config file, which will be published at `config/personal-data-export.php`:

```php
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
     * Determines whether the user should be logged in to be able
     * to access the export.
     */
    'authentication_required' => true,

    /*
     * The mailable which will be sent to the user when the export
     * has been created.
     */
    'mailable' => \Spatie\PersonalDataExport\Mail\PersonalDataExportCreatedMail::class,
];

```

Optionally, you can publish the view used by the mail with:

```php
php artisan vendor:publish --provider="Spatie\PersonalDataExport\PersonalDataExportServiceProvider" --tag="views"
```

This will create a file under `views/vendor/laravel-personal-data-export/mail.blade.php` that you can customize.

## Usage

### Selecting personal data

First, you'll have to prepare your user model. You should let your model implement the `Spatie\PersonalDataExport\ExportsPersonalData` interface. This is what that interface looks like:

```php
namespace Spatie\PersonalDataExport;

interface ExportsPersonalData
{
    public function selectPersonalData(PersonalDataSelection $personalData): void;

    public function personalDataExportName(): string;
}
```

The `selectPersonalData` is used to determine the content of the personal download. Here's an example implementation:

```php
// in your user model

public function selectPersonalData(PersonalDataSelection $personalData): void {
    $personalData
        ->add('user.json', ['name' => $this->name, 'email' => $this->email])
        ->addFile(storage_path("avatars/{$this->id}.jpg"))
        ->addFile('other-user-data.xml', 's3');
}
```

`$personalData` is used to determine the content of the zip file that the user will be able to download. You can call these methods on it:

- `add`: the first parameter is the name of the file in the inside the zip file. The second parameter is the content that should go in that file. If you pass an array here, we will encode it to JSON.
- `addFile`: the first parameter is a path to a file which will be copied to the zip. You can also add a disk name as the second parameter.

The name of the export itself can be set using the `personalDataExportName` on the user. This will only affect the name of the download that will be sent as a response to the user, not the name of the zip stored on disk.

```php
// on your user

public function personalDataExportName(string $realFilename): string {
    $userName = Str::slug($this->name);

    return "personal-data-{$userName}.zip";
}
```

### Creating an export

You can create a personal data export by executing this job somewhere in your application:

```php
// somewhere in your app

use Spatie\PersonalDataExport\Jobs\CreatePersonalDataExportJob;

// ...

dispatch(new CreatePersonalDataExportJob(auth()->user());
```

By default, this job is queued. It will copy all files and content you selected in the `selectPersonalData` on your user to a temporary directory. Next, that temporary directory will be zipped and copied over to the `personal-data-exports` disk. A link to this zip will be mailed to the user. 

### Securing the export

We recommend that the `personal-data-exports` disk is not publicly accessible. If you're using the `local` driver for this disk, make sure you use a path that is not inside the public path of your app.

When the user clicks the download link in the mail that gets sent after creating the export, a request will be sent to underlying `PersonalDataExportController`. This controller will check if there is a user logged in and if the request personal data zip belongs to the user. If this is the case, that controller will stream the zip to the user.

If you don't want to enforce that a user should be logged in to able to download a personal data export, you can set the `authentication_required` config value to `false`. Setting the value to `false` is less secure because anybody with a link to a zip file will be able to download it, but because the name of the zip file contains many random characters, it will be hard to guess it.

### Customizing the mail

You can customize mail by [publishing the views](https://github.com/spatie/laravel-personal-data-export#installation) and editing `views/vendor/laravel-personal-data-export/mail.blade.php`

You can also customize the mailable itself by creating your own mailable that extends `\Spatie\PersonalDataExport\Mail\PersonalDataExportCreatedMail` and register the class name of your mailable in the `mailable` config key of `config/personal-data-export.php`.

### Customizing the queue

You can customize the job that creates the zip file and mails it by extending the `Spatie\PersonalDataExport\Jobs\CreatePersonalDataExportJob` and dispatching your own custom job class.

```php
use Spatie\PersonalDataExport\Jobs\CreatePersonalDataExportJob;

class MyCustomJobClass extends CreatePersonalDataExportJob
{
    public $queue = 'my-custom-queue`
}
```

```php
dispatch(new MyCustomJobClass(auth()->user());
```

### Events

#### PersonalDataSelected

This event will be fired after the personal data has been selected. It has two public properties:
- `$personalData`: an instance of `PersonalData`. In your listeners you can call the `add`, `addFile` methods on this object to add extra content to the zip.
- `$user`: the user for which this personal data has been selected.

#### PersonalDataExportCreated

This event will be fired after the personal data zip has been created. It has two public properties:
- `$zipFilename`: the name of the zip filename.
- `$user`: the user for which this zip has been created.

#### PersonalDataExportDownloaded

This event will be fired after the export has been download. It has two public properties:
- `$zipFilename`: the name of the zip filename.
- `$user`: the user for which this zip has been created.

You could use this event to immediately clean up the downloaded zip.

## Testing

You can run all tests by issuing this command:

``` bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security

If you discover any security related issues, please email freek@spatie.be instead of using the issue tracker.

## Postcardware

You're free to use this package, but if it makes it to your production environment, we highly appreciate you sending us a postcard from your hometown, mentioning which of our package(s) you are using.

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
