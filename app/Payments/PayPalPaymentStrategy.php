<?php

namespace App\Payments;

use Illuminate\Support\Facades\Log;

class PayPalPaymentStrategy implements PaymentStrategyInterface
{
    public function processPayment(float $amount, array $paymentData = []): array
    {
        Log::info('Processing PayPal payment', ['amount' => $amount]);

        $validation = $this->validatePaymentData($paymentData);
        if (!$validation['valid']) {
            return [
                'success' => false,
                'message' => 'Validation failed: ' . implode(', ', $validation['errors']),
                'payment_method' => 'paypal',
                'amount' => $amount,
                'payment_status' => 'failed',
                'errors' => $validation['errors']
            ];
        }

        // Simulate PayPal processing (95% success rate)
        $success = mt_rand(1, 100) <= 95;

        if ($success) {
            return [
                'success' => true,
                'message' => 'PayPal payment processed successfully.',
                'payment_method' => 'paypal',
                'amount' => $amount,
                'payment_status' => 'completed',
                'payment_date' => now()->toDateTimeString()
            ];
        } else {
            return [
                'success' => false,
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

    public function getIcon(): string
    {
        return '🌐';
    }

    public function getDescription(): string
    {
        return 'Pay with your PayPal account';
    }

    public function getFormFields(): array
    {
        return [
            [
                'name' => 'paypal_email',
                'type' => 'email',
                'label' => 'PayPal Email',
                'placeholder' => 'your.email@example.com',
                'required' => true,
                'validation' => 'required|email',
                'class' => 'form-control'
            ]
        ];
    }

    public function getClientValidationRules(): array
    {
        return [
            'paypal_email' => [
                'required' => true,
                'email' => true
            ]
        ];
    }

    public function validatePaymentData(array $paymentData): array
    {
        $errors = [];

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
}