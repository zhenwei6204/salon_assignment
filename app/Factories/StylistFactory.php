<?php

namespace App\Factories;

use App\Models\User;
use App\Models\Stylist;
use Illuminate\Support\Facades\Hash;

class StylistFactory
{
    /**
     * Create a new stylist with a linked user account.
     *
     * @param  array  $data
     * @return Stylist
     * 
     */
    public static function create(array $data): Stylist
    {
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password'] ?? 'password123'),
            'role' => 'stylist',
        ]);

        // Determine title automatically based on experience
        $experience = $data['experience_years'] ?? 0;
        $title = match (true) {
            $experience >= 10 => 'Senior Stylist',
            $experience >= 5  => 'Intermediate Stylist',
            default => 'Junior Stylist',
        };

        return Stylist::create([
            'user_id' => $user->id,
            'name' => $data['name'],
            'title' => $title,
            'specializations' => $data['specializations'] ?? null,
            'experience_years' =>$experience,
            'phone' => $data['phone'] ?? null,
            'email' => $data['email'],
            'bio' => $data['bio'] ?? null,
            'image_url' => $data['image_url'] ?? null,
        ]);
    }
}