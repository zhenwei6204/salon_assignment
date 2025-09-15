<?php

namespace App\Payments;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Arr;

class CreditCardPaymentStrategy implements PaymentStrategyInterface
{
    public function processPayment(float $amount, array $paymentData = []): array
    {
        Log::info('Processing credit card payment', [
            'amount' => $amount,
           'payment_data' => Arr::except($paymentData, ['card_number', 'cvv'])
        ]);

   

        // Simulate credit card processing
        $success = $this->simulateCreditCardProcessing($amount, $paymentData);

        if ($success) {
            return [
                'success' => true,
                'message' => 'Credit card payment processed successfully.',
                'payment_method' => 'credit_card',
                'amount' => $amount,
                'payment_status' => 'completed',
                'payment_date' => now()->toDateTimeString(),
                'last_four' => isset($paymentData['card_number']) ? 'XXXX-' . substr($paymentData['card_number'], -4) : null
            ];
        } else {
            return [
                'success' => false,
                'message' => 'Credit card payment failed. Please check your card details.',
                'payment_method' => 'credit_card',
                'amount' => $amount,
                'payment_status' => 'failed'
            ];
        }
    }

    public function getPaymentMethodName(): string
    {
        return 'Credit/Debit Card';
    }

    public function getIcon(): string
    {
        return '💳';
    }

    public function getDescription(): string
    {
        return 'Secure credit/debit card payment';
    }

   

    public function validatePaymentData(array $paymentData): array
    {
        $errors = [];

        // Validate card number
        if (!isset($paymentData['card_number']) || empty($paymentData['card_number'])) {
            $errors[] = 'Card number is required';
        } elseif (!$this->isValidCardNumber($paymentData['card_number'])) {
            $errors[] = 'Invalid card number format';
        }

        // Validate expiry date
        if (!isset($paymentData['expiry_date']) || empty($paymentData['expiry_date'])) {
            $errors[] = 'Expiry date is required';
        } elseif (!$this->isValidExpiryDate($paymentData['expiry_date'])) {
            $errors[] = 'Invalid or expired card';
        }

        // Validate CVV
        if (!isset($paymentData['cvv']) || empty($paymentData['cvv'])) {
            $errors[] = 'CVV is required';
        } elseif (!$this->isValidCVV($paymentData['cvv'])) {
            $errors[] = 'Invalid CVV format';
        }

        // Validate cardholder name
        if (!isset($paymentData['cardholder_name']) || empty($paymentData['cardholder_name'])) {
            $errors[] = 'Cardholder name is required';
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }

    private function simulateCreditCardProcessing(float $amount, array $paymentData): bool
    {
        // Simulate processing time
        usleep(1000000); // 1 second

        // Simulate 90% success rate
        return mt_rand(1, 100) <= 90;
    }

   
}