**THIS PACKAGE IS IN DEVELOPMENT, DO NOT USE YET**

# Create personal data downloads in a Laravel app

[![Latest Version on Packagist](https://img.shields.io/packagist/v/spatie/personal-data-download.svg?style=flat-square)](https://packagist.org/packages/spatie/personal-data-download)
[![Build Status](https://img.shields.io/travis/spatie/personal-data-download/master.svg?style=flat-square)](https://travis-ci.org/spatie/personal-data-download)
[![Code coverage](https://scrutinizer-ci.com/g/spatie/personal-data-download/badges/coverage.png)](https://scrutinizer-ci.com/g/spatie/personal-data-download)
[![Quality Score](https://img.shields.io/scrutinizer/g/spatie/personal-data-download.svg?style=flat-square)](https://scrutinizer-ci.com/g/spatie/personal-data-download)
[![Total Downloads](https://img.shields.io/packagist/dt/spatie/personal-data-download.svg?style=flat-square)](https://packagist.org/packages/spatie/personal-data-download)

This package makes it easy to let a user download all personal data. Such a download consists of a zip file containing all user properties and related info.

You can create and mail such a zip by dispatching this job

```php
// in a controller

use Spatie\PersonalDataDownload\Jobs\CreatePersonalDataDownloadJob;

// ...

dispatch(new CreatePersonalDataDownloadJob(auth()->user());
```

The package will create a zip, by default this will be done on a queue. When the zip has been created a link to it will be mailed to the user.

You can configure which data will be put in the download in the `selectPersonalData` method on the `user`.

```php
// in your user model

public function selectPersonalData(PersonalDataDownload $personalDataDownload) {
    $personalDataDownload
        ->add('user.json', ['name' => $this->name, 'email' => $this->email])
        ->addFile(storage_path("avatars/{$this->id}.jpg");
}
```

## Installation

You can install the package via composer:

```bash
composer require spatie/personal-data-download
```

## Usage

``` php
$skeleton = new Spatie\Skeleton();
echo $skeleton->echoPhrase('Hello, Spatie!');
```

### Testing

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
