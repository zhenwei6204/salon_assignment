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
     * Get form fields configuration for this payment method
     * Returns array of field configurations
     */
    public function getFormFields(): array;

    /**
     * Get payment method icon/emoji
     */
    public function getIcon(): string;

    /**
     * Get payment method description
     */
    public function getDescription(): string;

    /**
     * Get client-side validation rules (optional)
     */
    public function getClientValidationRules(): array;
}