<?php

namespace Spatie\PersonalDataDownload;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Spatie\PersonalDataDownload\Commands\CleanOldPersonalDataDownloadsCommand;
use Spatie\PersonalDataDownload\Http\Controllers\PersonalDataDownloadController;

class PersonalDataDownloadServiceProvider extends ServiceProvider
{
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/personal-data-download.php' => config_path('personal-data-download.php'),
            ], 'config');

            $this->loadViewsFrom(__DIR__.'/../resources/views', 'personal-data-download');

            $this->publishes([
                __DIR__.'/../resources/views' => base_path('resources/views/vendor/personal-data-download'),
            ], 'views');
        }

        Route::macro('personalDataDownloads', function (string $url) {
            Route::get("$url/{zipFilename}", PersonalDataDownloadController::class)->name('personal-data-downloads');
        });

        $this->commands([
           CleanOldPersonalDataDownloadsCommand::class,
        ]);
    }

    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/personal-data-download.php', 'personal-data-download');
    }
}
