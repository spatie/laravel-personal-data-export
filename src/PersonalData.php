<?php

namespace Spatie\PersonalDataDownload;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Database\Eloquent\Model;
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

    public function addFile(string $pathToFile)
    {
        $fileName = pathinfo($pathToFile, PATHINFO_BASENAME);

        $destination = $this->temporaryDirectory->path($fileName);

        (new Filesystem())->copy($pathToFile, $destination);

        $this->files[] = $destination;

        return $this;
    }

    public function files(): array
    {
        return $this->files;
    }
}
