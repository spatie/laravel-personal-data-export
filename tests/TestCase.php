<?php

namespace Spatie\PersonalDataDownload\Tests;

use Carbon\Carbon;
use Illuminate\Foundation\Application;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Orchestra\Testbench\TestCase as Orchestra;
use PHPUnit\Framework\Assert;
use Spatie\QueryBuilder\QueryBuilderServiceProvider;
use Spatie\PersonalDataDownload\PersonalDataDownloadServiceProvider;
use Spatie\TemporaryDirectory\TemporaryDirectory;
use ZipArchive;

class TestCase extends Orchestra
{
    /** @var string */
    protected $diskName;

    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpDatabase($this->app);

        $this->withFactories(__DIR__.'/factories');

        Route::personalDataDownloads('personal-data-downloads');

        Carbon::setTestNow(Carbon::createFromFormat('Y-m-d H:i:s', '2019-01-01 00:00:00'));

        $this->diskName = config('personal-data-download.disk');
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
    }

    protected function getPackageProviders($app)
    {
        return [PersonalDataDownloadServiceProvider::class];
    }

    public function assertZipContains($zipFile, $expectedFileName, $expectedContents = null)
    {
        Assert::assertFileExists($zipFile);

        $zip = new ZipArchive();

        $zip->open($zipFile);

        $temporaryDirectory = (new TemporaryDirectory())->create();

        $zipDirectoryName = 'extracted-files';

        $zip->extractTo($temporaryDirectory->path($zipDirectoryName));

        $expectedZipFilePath = $temporaryDirectory->path($zipDirectoryName . '/' . $expectedFileName);

        Assert::assertFileExists($expectedZipFilePath);

        if (is_null($expectedContents)) {
            return;
        }

        $actualContents = file_get_contents($expectedZipFilePath);

        Assert::assertEquals($expectedContents, $actualContents );
    }
}
