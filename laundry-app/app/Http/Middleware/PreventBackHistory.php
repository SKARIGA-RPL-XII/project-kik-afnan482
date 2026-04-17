<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PreventBackHistory
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Skip StreamedResponse (seperti export CSV/Excel)
        if ($response instanceof StreamedResponse) {
            return $response;
        }

        return $response
            ->header('Cache-Control', 'no-cache, no-store, must-revalidate, max-age=0')
            ->header('Pragma', 'no-cache')
            ->header('Expires', 'Fri, 01 Jan 1990 00:00:00 GMT');
    }
}