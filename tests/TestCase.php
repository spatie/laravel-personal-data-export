<?php

namespace Spatie\PersonalDataExport\Tests;

use ZipArchive;
use Carbon\Carbon;
use PHPUnit\Framework\Assert;
use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Schema\Blueprint;
use Orchestra\Testbench\TestCase as Orchestra;
use Spatie\TemporaryDirectory\TemporaryDirectory;
use Spatie\PersonalDataExport\PersonalDataExportServiceProvider;

class TestCase extends Orchestra
{
    /** @var string */
    protected $diskName;

    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpDatabase($this->app);

        $this->withFactories(__DIR__.'/factories');

        Route::personalDataExports('personal-data-exports');

        Carbon::setTestNow(Carbon::createFromFormat('Y-m-d H:i:s', '2019-01-01 00:00:00'));

        $this->diskName = config('personal-data-export.disk');

        $userDisk = Storage::fake('user-disk');
        $userDisk->put('thumbnail.png', 'my content');
    }

    protected function setUpDatabase(Application $app)
    {
        Schema::create('users', function (Blueprint $table) {
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
        return [PersonalDataExportServiceProvider::class];
    }

    public function assertZipContains($zipFile, $expectedFileName, $expectedContents = null)
    {
        Assert::assertFileExists($zipFile);

        $zip = new ZipArchive();

        $zip->open($zipFile);

        $temporaryDirectory = (new TemporaryDirectory())->create();

        $zipDirectoryName = 'extracted-files';

        $zip->extractTo($temporaryDirectory->path($zipDirectoryName));

        $expectedZipFilePath = $temporaryDirectory->path($zipDirectoryName.'/'.$expectedFileName);

        Assert::assertFileExists($expectedZipFilePath);

        if (is_null($expectedContents)) {
            return;
        }

        $actualContents = file_get_contents($expectedZipFilePath);

        Assert::assertEquals($expectedContents, $actualContents);
    }

    protected function progressDays(int $amountOfDays)
    {
        $newNow = now()->addDays($amountOfDays);

        Carbon::setTestNow($newNow);
    }

    public function assertFileContents(string $path, string $expectedContents)
    {
        Assert::fileExists($path);

        $actualContents = file_get_contents($path);

        Assert::assertEquals($expectedContents, $actualContents);
    }

    public function getStubPath(string $file): string
    {
        return __DIR__."/stubs/{$file}";
    }
}
