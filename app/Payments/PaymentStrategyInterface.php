<?php

namespace App\Payments;

interface PaymentStrategyInterface
{
    /**
     * Process payment for the given amount
     *
     * @param float $amount
     * @param array $paymentData Additional payment data (card details, customer info, etc.)
     * @return array Payment result with status, transaction_id, message, etc.
     */
    public function processPayment(float $amount, array $paymentData = []): array;

    /**
     * Get the payment method name
     *
     * @return string
     */
    public function getPaymentMethodName(): string;

    /**
     * Validate payment data before processing
     *
     * @param array $paymentData
     * @return array Validation result
     */
    public function validatePaymentData(array $paymentData): array;
}