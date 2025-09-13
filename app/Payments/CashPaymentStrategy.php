<?php

namespace App\Payments;

use Illuminate\Support\Facades\Log;

/**
 * Cash Payment Strategy
 */
class CashPaymentStrategy implements PaymentStrategyInterface
{
    public function processPayment(float $amount, array $paymentData = []): array
    {
        Log::info('Processing cash payment', ['amount' => $amount]);

        return [
            'success' => true,
            'message' => 'Cash payment scheduled for appointment.',
            'payment_method' => 'cash',
            'amount' => $amount,
            'payment_status' => 'pending',
            'payment_date' => now()->toDateTimeString()
        ];
    }

    public function getPaymentMethodName(): string
    {
        return 'Cash Payment';
    }

    public function getIcon(): string
    {
        return '💵';
    }

    public function getDescription(): string
    {
        return 'Pay with cash when you arrive at the salon';
    }

    public function getFormFields(): array
    {
        return [
            [
                'type' => 'info',
                'content' => 'You can pay with cash when you arrive at the salon. Please bring the exact amount or have change ready.',
                'class' => 'alert alert-info'
            ]
        ];
    }

    public function getClientValidationRules(): array
    {
        return []; // No validation needed for cash
    }

    public function validatePaymentData(array $paymentData): array
    {
        return ['valid' => true, 'errors' => []]; // Always valid for cash
    }
}