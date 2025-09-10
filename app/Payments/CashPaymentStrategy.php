<?php

namespace App\Payments;

use Illuminate\Support\Facades\Log;

class CashPaymentStrategy implements PaymentStrategyInterface
{
    public function processPayment(float $amount, array $paymentData = []): array
    {
        Log::info('Processing cash payment', [
            'amount' => $amount,
            'payment_data' => $paymentData
        ]);

        // Cash payment doesn't require actual processing
        // Just log and return success
        return [
            'success' => true,
            'message' => 'Cash payment scheduled. Please pay at the salon.',
            'payment_method' => 'cash',
            'amount' => $amount,
            'payment_status' => 'pending',
            'payment_date' => now()->toDateTimeString()
        ];
    }

    public function getPaymentMethodName(): string
    {
        return 'Cash Payment at Salon';
    }

    public function validatePaymentData(array $paymentData): array
    {
        // Cash payments don't require additional validation
        return [
            'valid' => true,
            'errors' => []
        ];
    }
}