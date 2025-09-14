<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class ServiceApiKey
{
    private const KEY = 'my-service-key-123'; // share privately

    public function handle(Request $request, Closure $next)
    {
        if ($request->header('X-Service-Key') !== self::KEY) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }
        return $next($request);
    }
}
