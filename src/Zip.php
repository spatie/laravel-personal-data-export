<?php

namespace Spatie\PersonalDataExport;

use Illuminate\Support\Str;
use Spatie\TemporaryDirectory\TemporaryDirectory;
use ZipArchive;

class Zip
{
    protected ZipArchive $zipFile;

    protected int $fileCount = 0;

    protected string $pathToZip;

    public static function createForPersonalData(
        PersonalDataSelection $personalDataSelection,
        TemporaryDirectory $temporaryDirectory
    ): self {
        $zipFilenameParts = [
            $personalDataSelection->user->getKey(),
            now()->timestamp,
            Str::random(64),
        ];

        $zipFilename = implode('_', $zipFilenameParts).'.zip';

        $pathToZip = $temporaryDirectory->path($zipFilename);

        return (new static($pathToZip))
            ->add($personalDataSelection->files(), $temporaryDirectory->path())
            ->close();
    }

    public function __construct(string $pathToZip)
    {
        $this->zipFile = new ZipArchive();

        $this->pathToZip = $pathToZip;

        $this->open();
    }

    public function path(): string
    {
        return $this->pathToZip;
    }

    public function size(): int
    {
        if ($this->fileCount === 0) {
            return 0;
        }

        return filesize($this->pathToZip);
    }

    public function open(): self
    {
        $this->zipFile->open($this->pathToZip, ZipArchive::CREATE);

        return $this;
    }

    public function add(string|array $files, string $rootPath): self
    {
        foreach ($files as $file) {
            if (file_exists($file)) {
                $nameInZip = Str::after($file, $rootPath.'/');

                $this->zipFile->addFile($file, ltrim($nameInZip, DIRECTORY_SEPARATOR));
            }
            $this->fileCount++;
        }

        return $this;
    }

    public function close(): self
    {
        $this->zipFile->close();

        return $this;
    }
}
