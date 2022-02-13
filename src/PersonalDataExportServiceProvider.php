<?php

namespace Spatie\PersonalDataExport;

use Illuminate\Support\Facades\Route;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use Spatie\PersonalDataExport\Commands\CleanOldPersonalDataExportsCommand;
use Spatie\PersonalDataExport\Http\Controllers\PersonalDataExportController;

class PersonalDataExportServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('laravel-personal-data-export')
            ->hasConfigFile()
            ->hasCommand(CleanOldPersonalDataExportsCommand::class)
            ->hasTranslations();
    }

    public function packageBooted()
    {
        Route::macro('personalDataExports', function (string $url) {
            Route::get("$url/{zipFilename}", [PersonalDataExportController::class, 'export'])->name('personal-data-exports');
        });
    }
}
