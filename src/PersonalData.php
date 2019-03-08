<?php

namespace Spatie\PersonalDataDownload;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Spatie\TemporaryDirectory\TemporaryDirectory;

class PersonalData
{
    /** @var \Spatie\TemporaryDirectory\TemporaryDirectory */
    protected $temporaryDirectory;

    /** @var array */
    protected $files = [];

    /** @var \Illuminate\Database\Eloquent\Model */
    public $user;

    public function __construct(TemporaryDirectory $temporaryDirectory)
    {
        $this->temporaryDirectory = $temporaryDirectory;
    }

    public function forUser(Model $user)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * @param string $nameInDownload
     * @param array|string $content
     */
    public function addContent(string $nameInDownload, $content)
    {
        if (! is_string($content)) {
            $content = json_encode($content);
        }

        $path = $this->temporaryDirectory->path($nameInDownload);

        $this->files[] = $path;

        file_put_contents($path, $content);
    }

    public function addFile(string $pathToFile, string $diskName = null)
    {
        return is_null($diskName)
            ? $this->copyLocalFile($pathToFile)
            : $this->copyFileFromDisk($pathToFile, $diskName);
    }

    public function files(): array
    {
        return $this->files;
    }

    protected function copyLocalFile(string $pathToFile)
    {
        $fileName = pathinfo($pathToFile, PATHINFO_BASENAME);

        $destination = $this->temporaryDirectory->path($fileName);

        (new Filesystem())->copy($pathToFile, $destination);

        $this->files[] = $destination;

        return $this;
    }

    protected function copyFileFromDisk(string $pathOnDisk, string $diskName)
    {
        $stream = Storage::disk($diskName)->readStream($pathOnDisk);

        $pathInTemporaryDirectory = $this->temporaryDirectory->path($pathOnDisk);

        file_put_contents($pathInTemporaryDirectory, stream_get_contents($stream), FILE_APPEND);

        return $this;
    }
}
