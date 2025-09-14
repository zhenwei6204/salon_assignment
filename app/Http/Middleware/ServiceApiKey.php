<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class ServiceApiKey
{
    public function handle(Request $request, Closure $next)
    {
        // In local dev, accept our default 'dev-key' to avoid .env changes
        if (app()->isLocal()) {
            if ($request->header('X-Service-Key') === 'dev-key' || $request->header('X-Service-Key') === null) {
                return $next($request);
            }
        }

        // For non-local, require the header to match some secret (optional)
        if ($request->header('X-Service-Key') !== 'dev-key') {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        return $next($request);
    }
}
