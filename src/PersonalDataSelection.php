<?php

namespace Spatie\PersonalDataExport;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\Storage;
use Spatie\PersonalDataExport\Exceptions\CouldNotAddToPersonalDataSelection;
use Spatie\TemporaryDirectory\TemporaryDirectory;

class PersonalDataSelection
{
    protected array $files = [];

    public ExportsPersonalData | Model $user;

    public function __construct(
        protected TemporaryDirectory $temporaryDirectory
    ) {
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

    public function add(string $nameInDownload, array | string $content): PersonalDataSelection
    {
        if (!is_string($content)) {
            $content = json_encode($content, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        }

        $path = $this->temporaryDirectory->path($nameInDownload);

        $this->ensureDoesNotOverwriteExistingFile($path);

        $this->files[] = $path;

        file_put_contents($path, $content);

        return $this;
    }

    public function addFile(string $pathToFile, string $diskName = null, string $folder = null)
    {
        return is_null($diskName)
            ? $this->copyLocalFile($pathToFile, $folder)
            : $this->copyFileFromDisk($pathToFile, $diskName, $folder);
    }

    protected function copyLocalFile(string $pathToFile, string $folder = null)
    {
        if (is_string($folder) && !empty($folder)) {
            $fileName = $folder . '/' . pathinfo($pathToFile, PATHINFO_BASENAME);
        } else {
            $fileName = pathinfo($pathToFile, PATHINFO_BASENAME);
        }

        $fileName = pathinfo($pathToFile, PATHINFO_BASENAME);

        $destination = $this->temporaryDirectory->path($fileName);

        $this->ensureDoesNotOverwriteExistingFile($destination);

        (new Filesystem())->copy($pathToFile, $destination);

        $this->files[] = $destination;

        return $this;
    }

    protected function copyFileFromDisk(string $pathOnDisk, string $diskName, string $folder = null)
    {
        $stream = Storage::disk($diskName)->readStream($pathOnDisk);

        if (is_string($folder) && !empty($folder)) {
            $pathOnDirectory = $folder . '/' . $pathOnDisk;
        } else {
            $pathOnDirectory = $pathOnDisk;
        }

        $pathInTemporaryDirectory = $this->temporaryDirectory->path($pathOnDirectory);

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
