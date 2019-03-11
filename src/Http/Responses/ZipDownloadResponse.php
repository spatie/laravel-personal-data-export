<?php

namespace Spatie\PersonalDataExport\Http\Responses;

use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ZipDownloadResponse extends StreamedResponse
{
    public function __construct(string $filename)
    {
        $disk = Storage::disk(config('personal-data-export.disk'));

        if (! $disk->exists($filename)) {
            abort(404);
        }

        $downloadFilename = auth()->user()
            ? auth()->user()->personalDataExportName()
            : $filename;

        $downloadHeaders = [
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Content-Type' => 'application/zip',
            'Content-Length' => $disk->size($filename),
            'Content-Disposition' => 'attachment; filename="' . $downloadFilename . '"',
            'Pragma' => 'public',
        ];

        parent::__construct(function () use ($filename, $disk) {
            $stream = $disk->readStream($filename);

            fpassthru($stream);

            if (is_resource($stream)) {
                fclose($stream);
            }
        }, Response::HTTP_OK, $downloadHeaders);
    }
}
