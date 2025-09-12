<?php

namespace App\Actions\Fortify;

use App\Models\User;
use App\Models\Stylist;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Laravel\Fortify\Contracts\CreatesNewUsers;
use Laravel\Jetstream\Jetstream;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules;

    /**
     * Validate and create a newly registered user.
     *
     * @param  array<string, string>  $input
     */
    public function create(array $input): User
    {
        Validator::make($input, [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255'],
            'password' => $this->passwordRules(),
            'terms' => Jetstream::hasTermsAndPrivacyPolicyFeature() ? ['accepted', 'required'] : '',
        ])->after(function ($validator) use ($input) {
            // Check email in users table
            if (User::where('email', $input['email'])->exists()) {
                $validator->errors()->add('email', 'This email is already registered as a user.');
            }

            // Check email in stylists table
            if (Stylist::where('email', $input['email'])->exists()) {
                $validator->errors()->add('email', 'This email is already registered as a stylist.');
            }
        })->validate();

        return User::create([
            'name' => $input['name'],
            'email' => $input['email'],
            'password' => Hash::make($input['password']),
            'role' => 'user',
        ]);
    }
}
