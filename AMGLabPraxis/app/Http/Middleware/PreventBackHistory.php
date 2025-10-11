<?php
namespace App\Http\Middleware;

use Closure;

class PreventBackHistory
{
    // in App\Http\Middleware\PreventBackHistory (opzionale)
    public function handle($request, Closure $next)
    {
        $response = $next($request);
        return $response->header('Cache-Control','no-cache, no-store, must-revalidate')
                        ->header('Pragma','no-cache')
                        ->header('Expires','0');
    }
}
