<?php

namespace Spatie\PersonalDataExport\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Spatie\PersonalDataExport\Events\PersonalDataExportDownloaded;
use Symfony\Component\HttpFoundation\Response;

class FiresPersonalDataExportDownloadedEvent
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        if ($response->getStatusCode() === Response::HTTP_OK) {
            $zipFilename = $request->zipFilename;

            event(new PersonalDataExportDownloaded($zipFilename, auth()->user()));
        }

        return $response;
    }
}
