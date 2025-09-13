<?php

namespace App\Payments;
use Illuminate\Support\Arr; 
use Illuminate\Support\Facades\Log;

class BankTransferPaymentStrategy implements PaymentStrategyInterface
{
    public function processPayment(float $amount, array $paymentData = []): array
    {
        Log::info('Processing bank transfer payment', [
            'amount' => $amount,
           'payment_data' => Arr::except($paymentData, ['account_number'])
        ]);

        // Validate payment data first
        $validation = $this->validatePaymentData($paymentData);
        if (!$validation['valid']) {
            return [
                'success' => false,
                'message' => 'Validation failed: ' . implode(', ', $validation['errors']),
                'payment_method' => 'bank_transfer',
                'amount' => $amount,
                'payment_status' => 'failed',
                'errors' => $validation['errors']
            ];
        }

        // Simulate bank transfer processing
        // In real implementation, you would integrate with banking APIs
        $success = $this->simulateBankTransferProcessing($amount, $paymentData);

        if ($success) {
            return [
                'success' => true,
                'message' => 'Bank transfer initiated successfully. Processing may take 1-3 business days.',
                'payment_method' => 'bank_transfer',
                'amount' => $amount,
                'payment_status' => 'pending', // Bank transfers are usually pending
                'payment_date' => now()->toDateTimeString(),
                'bank_name' => $paymentData['bank_name'] ?? null,
                'account_holder' => $paymentData['account_holder_name'] ?? null
            ];
        } else {
            return [
                'success' => false,
                'transaction_id' => null,
                'message' => 'Bank transfer failed. Please verify your bank details.',
                'payment_method' => 'bank_transfer',
                'amount' => $amount,
                'payment_status' => 'failed'
            ];
        }
    }

    public function getPaymentMethodName(): string
    {
        return 'Bank Transfer';
    }

    public function validatePaymentData(array $paymentData): array
    {
        $errors = [];

        // Validate account holder name
        if (!isset($paymentData['account_holder_name']) || empty($paymentData['account_holder_name'])) {
            $errors[] = 'Account holder name is required';
        }

        // Validate bank name
        if (!isset($paymentData['bank_name']) || empty($paymentData['bank_name'])) {
            $errors[] = 'Bank name is required';
        }

        // Validate account number
        if (!isset($paymentData['account_number']) || empty($paymentData['account_number'])) {
            $errors[] = 'Account number is required';
        } elseif (!$this->isValidAccountNumber($paymentData['account_number'])) {
            $errors[] = 'Invalid account number format';
        }

        // Validate routing number (if provided)
        if (isset($paymentData['routing_number']) && !empty($paymentData['routing_number'])) {
            if (!$this->isValidRoutingNumber($paymentData['routing_number'])) {
                $errors[] = 'Invalid routing number format';
            }
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }

    private function simulateBankTransferProcessing(float $amount, array $paymentData): bool
    {
        // Simulate processing time
        usleep(3000000); // 3 seconds (bank transfers take longer to process)

        // Simulate 85% success rate (bank transfers can fail more often)
        return mt_rand(1, 100) <= 85;
    }

    private function isValidAccountNumber(string $accountNumber): bool
    {
        // Remove spaces and special characters
        $accountNumber = preg_replace('/[\s\-]/', '', $accountNumber);
        
        // Check if it's all digits and reasonable length (typically 8-17 digits)
        return preg_match('/^\d{8,17}$/', $accountNumber);
    }

    private function isValidRoutingNumber(string $routingNumber): bool
    {
        // Remove spaces and special characters
        $routingNumber = preg_replace('/[\s\-]/', '', $routingNumber);
        
        // Check if it's exactly 9 digits (US routing number format)
        if (!preg_match('/^\d{9}$/', $routingNumber)) {
            return false;
        }

        // Simple checksum validation (ABA routing number checksum)
        return $this->validateRoutingChecksum($routingNumber);
    }

    private function validateRoutingChecksum(string $routingNumber): bool
    {
        $weights = [3, 7, 1, 3, 7, 1, 3, 7, 1];
        $sum = 0;

        for ($i = 0; $i < 9; $i++) {
            $sum += (int)$routingNumber[$i] * $weights[$i];
        }

        return $sum % 10 === 0;
    }
}