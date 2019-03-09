<?php

namespace Spatie\PersonalDataDownload\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Spatie\PersonalDataDownload\Events\PersonalDataHasBeenDownloaded;

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
