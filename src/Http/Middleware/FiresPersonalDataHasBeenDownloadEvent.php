<?php

namespace Spatie\PersonalDataDownload\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Spatie\PersonalDataDownload\Events\PersonalDataHasBeenDownloaded;
use Symfony\Component\HttpFoundation\Response;

class FiresPersonalDataHasBeenDownloadEvent
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        if ($response->getStatusCode() === Response::HTTP_OK) {
            $zipFilename = $request->zipFilename;

            event(new PersonalDataHasBeenDownloaded($zipFilename, auth()->user()));
        }

        return $response;
    }
}