<?php

namespace App\Payments;

use Illuminate\Support\Facades\Log;

class PayPalPaymentStrategy implements PaymentStrategyInterface
{
    public function processPayment(float $amount, array $paymentData = []): array
    {
        Log::info('Processing PayPal payment', [
            'amount' => $amount,
            'payment_data' => $paymentData
        ]);

        // Validate payment data first
        $validation = $this->validatePaymentData($paymentData);
        if (!$validation['valid']) {
            return [
                'success' => false,
                'transaction_id' => null,
                'message' => 'Validation failed: ' . implode(', ', $validation['errors']),
                'payment_method' => 'paypal',
                'amount' => $amount,
                'payment_status' => 'failed',
                'errors' => $validation['errors']
            ];
        }

        // Simulate PayPal processing
        // In real implementation, you would use PayPal SDK
        $success = $this->simulatePayPalProcessing($amount, $paymentData);

        if ($success) {
            return [
                'success' => true,
                'message' => 'PayPal payment processed successfully.',
                'payment_method' => 'paypal',
                'amount' => $amount,
                'payment_status' => 'completed',
                'payment_date' => now()->toDateTimeString(),
                'payer_email' => $paymentData['paypal_email'] ?? null
            ];
        } else {
            return [
                'success' => false,
                'transaction_id' => null,
                'message' => 'PayPal payment failed. Please try again.',
                'payment_method' => 'paypal',
                'amount' => $amount,
                'payment_status' => 'failed'
            ];
        }
    }

    public function getPaymentMethodName(): string
    {
        return 'PayPal';
    }

    public function validatePaymentData(array $paymentData): array
    {
        $errors = [];

        // Validate PayPal email
        if (!isset($paymentData['paypal_email']) || empty($paymentData['paypal_email'])) {
            $errors[] = 'PayPal email is required';
        } elseif (!filter_var($paymentData['paypal_email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Invalid PayPal email format';
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }

    private function simulatePayPalProcessing(float $amount, array $paymentData): bool
    {
        // Simulate processing time
        usleep(2000000); // 2 seconds (PayPal typically takes longer)

        // Simulate 95% success rate
        return mt_rand(1, 100) <= 95;
    }
}