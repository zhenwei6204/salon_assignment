<?php

namespace App\Providers;

use Illuminate\Http\RedirectResponse;

class LogoutResponse
{
    public function __construct()
    {
        
    }

    public function toResponse($request): RedirectResponse
    {
        
        
        // Redirect to home page after logout
        return redirect('/');
    }
}
