<?php

namespace Spatie\PersonalDataExport\Http\Controllers;

use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PersonalDataExportController
{
    public function __invoke(string $zipFilename): StreamedResponse
    {
        $this->ensureAuthorizedToDownload($zipFilename);

        return $this->responseStream($zipFilename);
    }

    protected function ensureAuthorizedToDownload(string $zipFilename)
    {
        if (! config('personal-data-export.authentication_required')) {
            return;
        }

        if (! auth()->user()) {
            abort(Response::HTTP_FORBIDDEN);
        }

        [$owningUserId] = explode('-', $zipFilename);

        if ($owningUserId != auth()->user()->id) {
            abort(Response::HTTP_UNAUTHORIZED);
        }
    }

    protected function responseStream(string $filename): StreamedResponse
    {
        $disk = Storage::disk(config('personal-data-export.disk'));

        if (! $disk->exists($filename)) {
            abort(404);
        }

        $downloadFilename = auth()->user()->personalDataExportName();

        $downloadHeaders = [
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Content-Type' => 'application/zip',
            'Content-Length' => $disk->size($filename),
            'Content-Disposition' => 'attachment; filename="'.$downloadFilename.'"',
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
