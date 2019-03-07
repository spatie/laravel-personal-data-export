<?php

namespace Spatie\PersonalDataDownload;

use Spatie\TemporaryDirectory\TemporaryDirectory;
use ZipArchive;
use Illuminate\Support\Str;
use Spatie\Backup\Helpers\Format;

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
        $zipFileName = $personalData->user->id . '-' . Str::random(64) . '.zip';

        $pathToZip = $temporaryDirectory->path($zipFileName);

        return (new static($pathToZip))
            ->add($personalData->files())
            ->close();
    }

    protected static function determineNameOfFileInZip(string $pathToFile, string $pathToZip)
    {
        $zipDirectory = pathinfo($pathToZip, PATHINFO_DIRNAME);

        $fileDirectory = pathinfo($pathToFile, PATHINFO_DIRNAME);

        if (Str::startsWith($fileDirectory, $zipDirectory)) {
            return str_replace($zipDirectory, '', $pathToFile);
        }

        return $pathToFile;
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

    public function humanReadableSize(): string
    {
        return Format::humanReadableSize($this->size());
    }

    public function open(): self
    {
        $this->zipFile->open($this->pathToZip, ZipArchive::CREATE);

        return $this;
    }

    public function close(): self
    {
        $this->zipFile->close();

        return $this;
    }

    /**
     * @param string|array $files
     * @param string $nameInZip
     *
     * @return \Spatie\PersonalDataDownload\Zip
     */
    public function add($files, string $nameInZip = null): self
    {
        if (is_array($files)) {
            $nameInZip = null;
        }

        if (is_string($files)) {
            $files = [$files];
        }

        foreach ($files as $file) {
            if (file_exists($file)) {
                $this->zipFile->addFile($file, ltrim($nameInZip, DIRECTORY_SEPARATOR));
            }
            $this->fileCount++;
        }

        return $this;
    }
}
