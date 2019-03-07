<?php

namespace Spatie\PersonalDataDownload;

use ZipArchive;
use Illuminate\Support\Str;
use Spatie\TemporaryDirectory\TemporaryDirectory;

class Zip
{
    /** @var \ZipArchive */
    protected $zipFile;

    /** @var int */
    protected $fileCount = 0;

    /** @var string */
    protected $pathToZip;

    public static function createForPersonalData(
        PersonalData $personalData,
        TemporaryDirectory $temporaryDirectory): self
    {
        $zipFilenameParts = [
            $personalData->user->id,
            now()->timestamp,
            Str::random(64),
        ];

        $zipFilename = implode('-', $zipFilenameParts).'.zip';

        $pathToZip = $temporaryDirectory->path($zipFilename);

        return (new static($pathToZip))
            ->add($personalData->files(), $temporaryDirectory->path())
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

    /**
     * @param string|array $files
     * @param string $rootPath
     *
     * @return \Spatie\PersonalDataDownload\Zip
     */
    public function add($files, $rootPath): self
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
