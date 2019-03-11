<?php

namespace Spatie\PersonalDataExport\Http\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Storage;
use Spatie\PersonalDataExport\Http\Middleware\EnsureAuthorizedToDownload;
use Spatie\PersonalDataExport\Http\Middleware\FiresPersonalDataExportDownloadedEvent;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PersonalDataExportController extends Controller
{
    public function __construct()
    {
        $this->middleware(EnsureAuthorizedToDownload::class);
        $this->middleware(FiresPersonalDataExportDownloadedEvent::class);
    }

    public function __invoke(string $zipFilename): StreamedResponse
    {
        return $this->responseStream($zipFilename);
    }

    protected function responseStream(string $filename): StreamedResponse
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

        return response()->stream(function () use ($filename, $disk) {
            $stream = $disk->readStream($filename);

            fpassthru($stream);

            if (is_resource($stream)) {
                fclose($stream);
            }
        }, 200, $downloadHeaders);
    }
}
