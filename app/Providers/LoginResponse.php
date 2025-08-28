<?php

namespace App\Providers;


use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;

class LoginResponse implements LoginResponseContract
{
    public function toResponse($request)
    {
        // Redirect users to home page after login
        return redirect()->intended('/');
    }
}
