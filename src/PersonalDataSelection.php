<?php

namespace Spatie\PersonalDataExport;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\Storage;
use Spatie\TemporaryDirectory\TemporaryDirectory;
use Spatie\PersonalDataExport\Exceptions\CouldNotAddToPersonalDataSelection;

class PersonalDataSelection
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

    public function files(): array
    {
        return $this->files;
    }

    public function forUser(ExportsPersonalData $user)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * @param string $nameInDownload
     * @param array|string $content
     *
     * @return \Spatie\PersonalDataExport\PersonalDataSelection
     */
    public function add(string $nameInDownload, $content)
    {
        if (! is_string($content)) {
            $content = json_encode($content);
        }

        $path = $this->temporaryDirectory->path($nameInDownload);

        $this->ensureDoesNotOverwriteExistingFile($path);

        $this->files[] = $path;

        file_put_contents($path, $content);

        return $this;
    }

    public function addFile(string $pathToFile, string $diskName = null)
    {
        return is_null($diskName)
            ? $this->copyLocalFile($pathToFile)
            : $this->copyFileFromDisk($pathToFile, $diskName);
    }

    protected function copyLocalFile(string $pathToFile)
    {
        $fileName = pathinfo($pathToFile, PATHINFO_BASENAME);

        $destination = $this->temporaryDirectory->path($fileName);

        $this->ensureDoesNotOverwriteExistingFile($destination);

        (new Filesystem())->copy($pathToFile, $destination);

        $this->files[] = $destination;

        return $this;
    }

    protected function copyFileFromDisk(string $pathOnDisk, string $diskName)
    {
        $stream = Storage::disk($diskName)->readStream($pathOnDisk);

        $pathInTemporaryDirectory = $this->temporaryDirectory->path($pathOnDisk);

        $this->ensureDoesNotOverwriteExistingFile($pathInTemporaryDirectory);

        file_put_contents($pathInTemporaryDirectory, stream_get_contents($stream), FILE_APPEND);

        $this->files[] = $pathInTemporaryDirectory;

        return $this;
    }

    protected function ensureDoesNotOverwriteExistingFile(string $path)
    {
        if (file_exists($path)) {
            throw CouldNotAddToPersonalDataSelection::fileAlreadyAddedToPersonalDataSelection($path);
        }
    }
}
