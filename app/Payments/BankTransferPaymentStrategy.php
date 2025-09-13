<?php

namespace App\Payments;

use Illuminate\Support\Facades\Log;

class BankTransferPaymentStrategy implements PaymentStrategyInterface
{
    public function processPayment(float $amount, array $paymentData = []): array
    {
        Log::info('Processing bank transfer payment', [
            'amount' => $amount,
            'account_holder' => $paymentData['account_holder_name'] ?? 'N/A'
        ]);

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

        // Bank transfers typically need manual verification
        return [
            'success' => true,
            'message' => 'Bank transfer initiated. You will receive transfer details via email. Please complete the transfer within 24 hours.',
            'payment_method' => 'bank_transfer',
            'amount' => $amount,
            'payment_status' => 'completed', // Bank transfers start as pending
            'payment_date' => now()->toDateTimeString(),
            'reference_number' => 'BT_' . time() . '_' . rand(1000, 9999)
        ];
    }

    public function getPaymentMethodName(): string
    {
        return 'Bank Transfer';
    }

    public function getIcon(): string
    {
        return '🏦';
    }

    public function getDescription(): string
    {
        return 'Direct bank transfer payment';
    }

    public function getFormFields(): array
    {
        return [
            [
                'name' => 'account_holder_name',
                'type' => 'text',
                'label' => 'Account Holder Name',
                'placeholder' => 'Full name as shown on bank account',
                'required' => true,
                'validation' => 'required|string|max:100',
                'class' => 'form-control'
            ],
            [
                'name' => 'bank_name',
                'type' => 'text',
                'label' => 'Bank Name',
                'placeholder' => 'Your bank name',
                'required' => true,
                'validation' => 'required|string|max:100',
                'class' => 'form-control',
                'wrapper_class' => 'col-md-6'
            ],
            [
                'name' => 'account_number',
                'type' => 'text',
                'label' => 'Account Number',
                'placeholder' => 'Your account number',
                'required' => true,
                'validation' => 'required|string',
                'class' => 'form-control',
                'wrapper_class' => 'col-md-6'
            ],
            [
                'name' => 'routing_number',
                'type' => 'text',
                'label' => 'Routing Number (Optional)',
                'placeholder' => 'Bank routing number',
                'required' => false,
                'validation' => 'nullable|string',
                'class' => 'form-control'
            ]
        ];
    }

    public function getClientValidationRules(): array
    {
        return [
            'account_holder_name' => [
                'required' => true,
                'minLength' => 2
            ],
            'bank_name' => [
                'required' => true,
                'minLength' => 2
            ],
            'account_number' => [
                'required' => true,
                'minLength' => 5,
                'numeric' => true
            ]
        ];
    }

    public function validatePaymentData(array $paymentData): array
    {
        $errors = [];

        if (!isset($paymentData['account_holder_name']) || empty($paymentData['account_holder_name'])) {
            $errors[] = 'Account holder name is required';
        }

        if (!isset($paymentData['bank_name']) || empty($paymentData['bank_name'])) {
            $errors[] = 'Bank name is required';
        }

        if (!isset($paymentData['account_number']) || empty($paymentData['account_number'])) {
            $errors[] = 'Account number is required';
        } elseif (!preg_match('/^\d+$/', $paymentData['account_number'])) {
            $errors[] = 'Account number must contain only numbers';
        }

        // Optional routing number validation
        if (isset($paymentData['routing_number']) && !empty($paymentData['routing_number'])) {
            if (!preg_match('/^\d{9}$/', $paymentData['routing_number'])) {
                $errors[] = 'Routing number must be exactly 9 digits';
            }
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }
}