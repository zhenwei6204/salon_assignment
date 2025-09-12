<?php

namespace App\Providers;


use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;
use Illuminate\Support\Facades\Auth;


class LoginResponse implements LoginResponseContract
{
    public function toResponse($request)
    {
        $user = Auth::user();

        switch ($user->role) {
            case 'stylist':
                return redirect()->route('stylist.dashboard');
            case 'admin':
                return redirect('/admin');
            default:
                return redirect()->route('dashboard');
        }
    }
}
