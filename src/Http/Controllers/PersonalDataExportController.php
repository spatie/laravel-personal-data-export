<?php

namespace Spatie\PersonalDataExport\Http\Controllers;

use Illuminate\Routing\Controller;
use Spatie\PersonalDataExport\Http\Middleware\EnsureAuthorizedToDownload;
use Spatie\PersonalDataExport\Http\Middleware\FiresPersonalDataExportDownloadedEvent;
use Spatie\PersonalDataExport\Http\Responses\ZipDownloadResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PersonalDataExportController extends Controller
{
    public function __construct()
    {
        $this->middleware(EnsureAuthorizedToDownload::class);
        $this->middleware(FiresPersonalDataExportDownloadedEvent::class);
    }

    public function export(string $zipFilename): StreamedResponse
    {
        return new ZipDownloadResponse($zipFilename);
    }
}
