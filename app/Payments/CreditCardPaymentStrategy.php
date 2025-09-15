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

    public function getFormFields(): array
    {
        return [
            [
                'name' => 'cardholder_name',
                'type' => 'text',
                'label' => 'Cardholder Name',
                'placeholder' => 'Name as shown on card',
                'required' => true,
                'validation' => 'required|string|max:100',
                'class' => 'form-control'
            ],
            [
                'name' => 'card_number',
                'type' => 'text',
                'label' => 'Card Number',
                'placeholder' => '1234 5678 9012 3456',
                'required' => true,
                'validation' => 'required|string',
                'class' => 'form-control card-number',
                'data_attributes' => [
                    'data-mask' => 'credit-card'
                ]
            ],
            [
                'name' => 'expiry_date',
                'type' => 'text',
                'label' => 'Expiry Date',
                'placeholder' => 'MM/YY',
                'required' => true,
                'validation' => 'required|string',
                'class' => 'form-control expiry-date',
                'data_attributes' => [
                    'data-mask' => 'expiry'
                ],
                'wrapper_class' => 'col-md-6'
            ],
            [
                'name' => 'cvv',
                'type' => 'text',
                'label' => 'CVV',
                'placeholder' => '123',
                'required' => true,
                'validation' => 'required|string|min:3|max:4',
                'class' => 'form-control cvv',
                'data_attributes' => [
                    'data-mask' => 'numeric',
                    'maxlength' => '4'
                ],
                'wrapper_class' => 'col-md-6'
            ]
        ];
    }

    public function getClientValidationRules(): array
    {
        return [
            'cardholder_name' => [
                'required' => true,
                'minLength' => 2
            ],
            'card_number' => [
                'required' => true,
                'creditCard' => true
            ],
            'expiry_date' => [
                'required' => true,
                'expiryDate' => true
            ],
            'cvv' => [
                'required' => true,
                'numeric' => true,
                'minLength' => 3,
                'maxLength' => 4
            ]
        ];
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

    private function isValidCardNumber(string $cardNumber): bool
    {
        // Remove spaces and dashes
        $cardNumber = preg_replace('/[\s\-]/', '', $cardNumber);
        
        // Check if it's all digits and reasonable length
        if (!preg_match('/^\d{13,19}$/', $cardNumber)) {
            return false;
        }

        // Luhn algorithm validation
        return $this->luhnCheck($cardNumber);
    }

    private function luhnCheck(string $cardNumber): bool
    {
        $sum = 0;
        $numDigits = strlen($cardNumber);
        $parity = $numDigits % 2;

        for ($i = 0; $i < $numDigits; $i++) {
            $digit = (int)$cardNumber[$i];
            if ($i % 2 == $parity) {
                $digit *= 2;
                if ($digit > 9) {
                    $digit -= 9;
                }
            }
            $sum += $digit;
        }

        return $sum % 10 == 0;
    }

    private function isValidExpiryDate(string $expiryDate): bool
    {
        // Expected format: MM/YY or MM/YYYY
        if (!preg_match('/^(\d{2})\/(\d{2}|\d{4})$/', $expiryDate, $matches)) {
            return false;
        }

        $month = (int)$matches[1];
        $year = (int)$matches[2];

        // Convert 2-digit year to 4-digit
        if ($year < 100) {
            $year += 2000;
        }

        // Check if month is valid
        if ($month < 1 || $month > 12) {
            return false;
        }

        // Check if card is not expired
        $expiryTimestamp = mktime(0, 0, 0, $month + 1, 1, $year); // First day of next month
        return $expiryTimestamp > time();
    }

    private function isValidCVV(string $cvv): bool
    {
        // CVV should be 3 or 4 digits
        return preg_match('/^\d{3,4}$/', $cvv);
    }
}