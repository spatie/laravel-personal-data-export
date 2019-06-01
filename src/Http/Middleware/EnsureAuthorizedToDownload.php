<?php

namespace Spatie\PersonalDataExport\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class EnsureAuthorizedToDownload
{
    public function handle(Request $request, Closure $next)
    {
        if (! config('personal-data-export.authentication_required')) {
            return $next($request);
        }

        if (! auth()->user()) {
            abort(Response::HTTP_FORBIDDEN);
        }

        $zipFilename = $request->route('zipFilename');

        [$owningUserId] = explode('_', $zipFilename);

        if ($owningUserId != auth()->user()->id) {
            abort(Response::HTTP_UNAUTHORIZED);
        }

        return $next($request);
    }
}
