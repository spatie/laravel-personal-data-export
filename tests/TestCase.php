<?php

namespace Spatie\PersonalDataDownload\Tests;

use Illuminate\Foundation\Application;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Orchestra\Testbench\TestCase as Orchestra;
use Spatie\QueryBuilder\QueryBuilderServiceProvider;
use Spatie\PersonalDataDownload\PersonalDataDownloadServiceProvider;

class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpDatabase($this->app);

        $this->withFactories(__DIR__.'/factories');

        Route::personalDataDownloads('personal-data-downloads');
    }

    protected function setUpDatabase(Application $app)
    {
        Schema::create('users', function(Blueprint $table) {
            $table->increments('id');
            $table->string('email');
            $table->timestamps();
        });
    }

    public function getEnvironmentSetUp($app)
    {
        config()->set('database.default', 'sqlite');
        config()->set('database.connections.sqlite', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);
        config()->set('app.key', '6rE9Nz59bGRbeMATftriyQjrpF7DcOQm');

    }

    protected function getPackageProviders($app)
    {
        return [PersonalDataDownloadServiceProvider::class];
    }
}
