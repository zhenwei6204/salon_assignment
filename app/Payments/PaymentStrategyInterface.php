<?php

namespace App\Payments;

interface PaymentStrategyInterface
{
    /**
     * Process the payment
     */
    public function processPayment(float $amount, array $paymentData = []): array;

    /**
     * Get payment method name
     */
    public function getPaymentMethodName(): string;

    /**
     * Validate payment data
     */
    public function validatePaymentData(array $paymentData): array;

   

    /**
     * Get payment method icon/emoji
     */
    public function getIcon(): string;

    /**
     * Get payment method description
     */
    public function getDescription(): string;

 
}