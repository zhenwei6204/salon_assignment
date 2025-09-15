<?php

namespace App\Security;


use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Crypt;
use Carbon\Carbon;

class PaymentSecurityManager
{
    public function validatePaymentSession(array $bookingDetails, $user): bool
    {
        if (empty($bookingDetails) || !isset($bookingDetails['booking_id'])) {
            return false;
        }

        // Check if booking belongs to authenticated user
        if (!isset($bookingDetails['customer_email']) || 
            $bookingDetails['customer_email'] !== $user->email) {
            return false;
        }

        // Check session age (max 1 hour for payment sessions)
        if (isset($bookingDetails['created_at'])) {
            $createdAt = Carbon::parse($bookingDetails['created_at']);
            if ($createdAt->diffInHours(now()) > 1) {
                return false;
            }
        }

        return true;
    }

    public function generateSecurityHash(int $bookingId): string
    {
        $data = [
            'booking_id' => $bookingId,
            'user_id' => auth()->id(),
            'timestamp' => now()->timestamp,
        ];

        return hash('sha256', json_encode($data) . config('app.key'));
    }

    public function verifySecurityHash(string $hash, int $bookingId): bool
    {
        $expectedHash = $this->generateSecurityHash($bookingId);
        return hash_equals($expectedHash, $hash);
    }

    public function encryptSensitiveData(string $data): string
    {
        return Crypt::encryptString($data);
    }
}