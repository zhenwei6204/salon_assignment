<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RedirectAfterLogout
{
    public function handle(Request $request, Closure $next)
    {
        
        $response = $next($request);
        
        // Check if this is a POST request to logout and user is no longer authenticated
        if ($request->method() === 'POST' && 
            $request->is('admin/logout') && 
            !Auth::check()) {
            \Log::info('Logout detected - redirecting to home page');
            return redirect('/');
        }
        
        return $response;
    }
}
